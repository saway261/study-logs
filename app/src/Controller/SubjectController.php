<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Form\SubjectCreateType;
use App\Entity\Subject;
use App\Repository\SubjectStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Form\SubjectEditType;
use App\Form\SubjectDeleteType;

#[Route('/subjects', name: 'subject_')]
final class SubjectController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SubjectRepository $repo): Response
    {
        $form = $this->createForm(SubjectCreateType::class, new Subject());

        return $this->render('subject/index.html.twig', [
            'studying' => $repo->findStudying(),
            'done'     => $repo->findDone(),
            'form'     => $form->createView(),
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $req,
        EntityManagerInterface $em,
        SubjectStatusRepository $statusRepo
    ): Response {
        $subject = new Subject();
        $form = $this->createForm(SubjectCreateType::class, $subject)->handleRequest($req);

        if (!$form->isSubmitted() || !$form->isValid()) {
            // 失敗時も index を再描画（一覧＋フォームエラーを出す）
            // 一覧再取得が必要
            /** @var SubjectRepository $repo */
            $repo = $em->getRepository(Subject::class);
            return $this->render('subject/index.html.twig', [
                'studying' => $repo->findStudying(),
                'done'     => $repo->findDone(),
                'form'     => $form->createView(),
            ]);
        }

        // “学習中” is_deleted=falseを強制
        $subject->setStatus($statusRepo->getStudying());
        $subject->setIsDeleted(false);

        try {
            $em->persist($subject);
            $em->flush();
            $this->addFlash('success', '科目を追加しました。');
        } catch (UniqueConstraintViolationException) {
            $this->addFlash('danger', '同名の学習中（未削除）科目が既に存在します。');
        }
        // PRGで一覧へ（ここで学習中リストに乗っている）
        return $this->redirectToRoute('subject_index');
    }

    #[Route('/{id}', name: 'edit', methods: ['GET'])]
    public function edit(Subject $subject): Response
    {
        $editForm = $this->createForm(SubjectEditType::class, $subject);
        $deleteForm = $this->createForm(SubjectDeleteType::class);
        return $this->render('subject/edit.html.twig', [
            'editForm' => $editForm,
            'deleteForm' => $deleteForm,
            'subject' => $subject,
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['POST'])]
    public function update(Subject $subject, Request $req, EntityManagerInterface $em): Response
    {
        $deleteForm = $this->createForm(SubjectDeleteType::class);
        $deleteForm->handleRequest($req);

        // 削除ボタンが押された場合
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {

            $subject->setIsDeleted(true);
            $em->flush();

            $this->addFlash('success', '科目を削除しました。');
            return $this->redirectToRoute('subject_index');
        }

        // 更新フォームの処理
        $editForm = $this->createForm(SubjectEditType::class, $subject);
        $editForm->handleRequest($req);

        if (!$editForm->isSubmitted() || !$editForm->isValid()) {
            return $this->render('subject/edit.html.twig', [
                'editForm' => $editForm,
                'deleteForm' => $deleteForm,
                'subject' => $subject,
            ]);
        }

        try {
            $em->flush();
            $this->addFlash('success', '科目を更新しました。');
            return $this->redirectToRoute('subject_index');
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('danger', '同名の学習中（未削除）科目が既に存在します。');
            return $this->render('subject/edit.html.twig', [
                'editForm' => $editForm,
                'deleteForm' => $deleteForm,
                'subject' => $subject,
            ]);
        }
    }
}
