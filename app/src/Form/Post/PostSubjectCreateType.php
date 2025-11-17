<?php

namespace App\Form\Post;

use App\Entity\PostSubject;
use App\Entity\Subject;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class PostSubjectCreateType extends PostSubjectTypeBase
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 科目: テキスト入力（既存選択も直接入力も可能）
        $builder->add('subject', TextType::class, [
            'label' => false,
            'required' => true,
            'mapped' => false, // POST_SUBMITで処理するため一旦false
            'attr' => [
                'maxlength' => 20,
                'placeholder' => '科目名を入力または選択',
                'list' => 'subject-list', // datalist用のID
                'autocomplete' => 'off',
            ],
        ]);

        $this->addCommonFields($builder);

        // フォーム送信後に、入力された科目名からSubjectエンティティを解決
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var PostSubject $postSubject */
            $postSubject = $event->getData();
            $form = $event->getForm();

            $subjectName = trim($form->get('subject')->getData() ?? '');

            if (empty($subjectName)) {
                return;
            }

            // 既存のSubjectを名前で検索（未削除のみ）
            $existingSubject = $this->subjectRepository->findOneBy([
                'name' => $subjectName,
                'isDeleted' => false,
            ]);

            if ($existingSubject) {
                // 既存のSubjectが見つかった場合
                $postSubject->setSubject($existingSubject);
            } else {
                // 既存が見つからない場合：SubjectController::createのロジックを流用して新規作成
                $subject = new Subject();
                $subject->setName($subjectName);
                // SubjectController::createと同様に、"学習中" is_deleted=falseを強制
                $subject->setStatus($this->statusRepository->getStudying());
                $subject->setIsDeleted(false);

                // ここではpersistのみ（flushはコントローラーで）
                $this->em->persist($subject);

                $postSubject->setSubject($subject);
            }
        });
    }
}
