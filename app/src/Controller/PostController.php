<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use App\Repository\SubjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Form\PostCreateType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Entity\PostSubject;
use App\Repository\SubjectStatusRepository;
use App\Entity\Subject;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

#[Route('/posts', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('/{date}', name: 'show', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function show(string $date, PostRepository $posts): Response
    {
        // 'YYYY-MM-DD' を DateTimeImmutable に
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$d) {
            throw $this->createNotFoundException('日付の形式が不正です。');
        }

        // 記事1件（論理削除は除外）
        $post = $posts->findByDate($d);
        if (!$post) {
            throw $this->createNotFoundException('記事が見つかりません。');
        }

        return $this->render('post/detail.html.twig', [
            'post'     => $post,
            'date'     => $date,
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
}
