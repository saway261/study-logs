<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostSubject;
use App\Form\Post\PostCreateType;
use App\Form\Post\PostDeleteType;
use App\Form\Post\PostEditType;
use App\Repository\PostRepository;
use App\Repository\SubjectRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/posts', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('/{date}', name: 'show', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function show(string $date, PostRepository $postRepo): Response
    {
        $post = $this->findPostByDateOr404($date, $postRepo);

        return $this->render('post/detail.html.twig', [
            'post' => $post,
            'date' => $date,
        ]);
    }

    #[Route('/{date}/edit', name: 'edit', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function edit(string $date, PostRepository $postRepo, SubjectRepository $subjectRepo): Response
    {
        $post = $this->findPostByDateOr404($date, $postRepo);

        $editForm   = $this->createForm(PostEditType::class, $post);
        $deleteForm = $this->createForm(PostDeleteType::class);

        return $this->render('post/edit.html.twig', [
            'editForm'     => $editForm->createView(),
            'deleteForm'   => $deleteForm->createView(),
            'post'         => $post,
            'date'         => $date,
            'subjectNames' => $subjectRepo->findAllActiveNames(),
        ]);
    }

    #[Route('/{date}', name: 'update', methods: ['PUT'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function update(string $date, Request $req, EntityManagerInterface $em, PostRepository $postRepo): Response
    {
        $post = $this->findPostByDateOr404($date, $postRepo);

        $editForm = $this->createForm(PostEditType::class, $post);
        $editForm->handleRequest($req);

        if (!$editForm->isSubmitted() || !$editForm->isValid()) {
            $deleteForm = $this->createForm(PostDeleteType::class);

            return $this->render('post/edit.html.twig', [
                'editForm'   => $editForm,
                'deleteForm' => $deleteForm,
                'post'       => $post,
                'date'       => $date,
            ]);
        }

        try {
            $em->flush();
            $this->addFlash('success', '投稿を更新しました。');

            return $this->redirectToRoute('post_show', [
                'date' => $post->getDate()->format('Y-m-d'),
            ]);
        } catch (ConstraintViolationException $e) {
            $deleteForm = $this->createForm(PostDeleteType::class);

            $response = $this->render('post/edit.html.twig', [
                'editForm'   => $editForm,
                'deleteForm' => $deleteForm,
                'post'       => $post,
                'date'       => $date,
            ]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $response;
        }
    }

    #[Route('/{date}', name: 'delete', methods: ['DELETE'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function delete(string $date, Request $req, EntityManagerInterface $em, PostRepository $postRepo): Response
    {
        $post = $this->findPostByDateOr404($date, $postRepo);

        $deleteForm = $this->createForm(PostDeleteType::class);
        $deleteForm->handleRequest($req);

        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->addFlash('danger', '削除に失敗しました。');

            return $this->redirectToRoute('post_edit', [
                'date' => $post->getDate()->format('Y-m-d'),
            ]);
        }

        $post->setIsDeleted(true);
        $em->flush();

        $this->addFlash('success', '投稿を削除しました。');

        // TODO: 一覧画面ができたらそこへリダイレクト
        return $this->redirectToRoute('post_show', [
            'date' => $post->getDate()->format('Y-m-d'),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET'])]
    public function new(SubjectRepository $subjectRepo): Response
    {
        $post = new Post();
        $post->addPostSubject(new PostSubject());

        $form = $this->createForm(PostCreateType::class, $post);

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
            'subjectNames' => $subjectRepo->findAllActiveNames(), // datalist用
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SubjectRepository $subjectRepo
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('post/new.html.twig', [
                'form' => $form->createView(),
                'subjectNames' => $subjectRepo->findAllActiveNames(),
            ], new Response('', 422));
        }

        try {
            // POST_SUBMITイベントで既にSubjectがpersistされているので、そのままflush
            $em->persist($post);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            // Subjectの重複エラーの場合も処理
            // ただし、PostSubjectTypeのPOST_SUBMITで既存を検索しているので、通常は発生しない
            // Postの日付重複エラーの場合
            $this->addFlash('warning', 'この日付の投稿は既に存在します。既存の詳細に移動しました。');
            return $this->redirectToRoute('post_show', [
                'date' => $post->getDate()->format('Y-m-d'),
            ], 303);
        }

        return $this->redirectToRoute('post_show', [
            'date' => $post->getDate()->format('Y-m-d'),
        ], 303);
    }

    // ───────── ここから共通privateメソッド ─────────

    /**
     * 'YYYY-MM-DD' の文字列を DateTimeImmutable に変換し、
     * 不正な場合は 404 を投げる。
     */
    private function parseDateOr404(string $date): DateTimeImmutable
    {
        $d = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$d) {
            throw $this->createNotFoundException('日付の形式が不正です。');
        }

        return $d;
    }

    /**
     * 日付文字列から Post を1件取得し、
     * 見つからなければ 404 を投げる。
     */
    private function findPostByDateOr404(string $date, PostRepository $postRepo): Post
    {
        $d = $this->parseDateOr404($date);

        $post = $postRepo->findByDate($d);
        if (!$post) {
            throw $this->createNotFoundException('記事が見つかりません。');
        }

        return $post;
    }
}
