<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;

final class PostController extends AbstractController
{
    #[Route('/post/{date}', name: 'post_show', methods: ['GET'], requirements:['date' => '\d{4}-\d{2}-\d{2}'])]
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
}
