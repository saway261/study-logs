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
    public function new(SubjectRepository $subjects): Response
    {
        // 新規用の空エンティティ（Date は PostCreateType の PRE_SET_DATA で「今日」に入る）
        $post = new Post();

        // 1行目を最初から出しておきたい場合は↓（任意）
        $post->addPostSubject(new PostSubject());

        $form = $this->createForm(PostCreateType::class, $post);

        return $this->render('post/new.html.twig', [
            // Twig では form_start/form_widget/form_end を使うので createView() を渡す
            'form' => $form->createView()
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostCreateType::class, $post);
        $form->handleRequest($request); // ← ここで POST データを $post にバインド＆バリデーション

        if (!$form->isSubmitted() || !$form->isValid()) {
            // バリデーション NG。ステータスは 200 でも 422 でもお好みで
            return $this->render('post/new.html.twig', [
                'form' => $form->createView(),
            ], new Response('', 422));
        }

        try {
            // DBへ保存（Post ⇔ PostSubject は OneToMany/cascade:persist 前提）
            $em->persist($post);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            // UNIQUE(date, is_deleted) 等に抵触した場合のUX
            $this->addFlash('warning', 'この日付の投稿は既に存在します。既存の詳細に移動しました。');
            return $this->redirectToRoute('post_show', [
                'date' => $post->getDate()->format('Y-m-d'),
            ], 303);
        }

        // 正常完了：確認（＝公開）画面へ 303 See Other
        return $this->redirectToRoute('post_show', [
            'date' => $post->getDate()->format('Y-m-d'),
        ], 303);
    }
}
