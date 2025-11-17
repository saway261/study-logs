<?php

namespace App\Form\Post;

use App\Entity\PostSubject;
use App\Entity\Subject;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class PostSubjectEditType extends PostSubjectTypeBase
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subject', TextType::class, [
            'label' => false,
            'required' => true,
            'mapped' => false,
            'attr' => [
                'maxlength' => 20,
                'placeholder' => '科目名を入力または選択',
                'list' => 'subject-list',
                'autocomplete' => 'off',
            ],
        ]);

        $this->addCommonFields($builder);

        // ★ ここを PRE_SET_DATA → POST_SET_DATA に変更
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var PostSubject|null $postSubject */
            $postSubject = $event->getData();
            if (!$postSubject) {
                return;
            }

            $subject = $postSubject->getSubject();
            if ($subject) {
                $form = $event->getForm();
                // TextType( mapped=false ) に Subject名を手動でセット
                $form->get('subject')->setData($subject->getName());
            }
        });

        // POST_SUBMIT 側のロジックはそのままでOK
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var PostSubject $postSubject */
            $postSubject = $event->getData();
            $form = $event->getForm();

            $subjectName = trim($form->get('subject')->getData() ?? '');
            if ($subjectName === '') {
                return;
            }

            $existingSubject = $this->subjectRepository->findOneBy([
                'name'      => $subjectName,
                'isDeleted' => false,
            ]);

            if ($existingSubject) {
                $postSubject->setSubject($existingSubject);
            } else {
                $subject = new Subject();
                $subject->setName($subjectName);
                $subject->setStatus($this->statusRepository->getStudying());
                $subject->setIsDeleted(false);

                $this->em->persist($subject);
                $postSubject->setSubject($subject);
            }
        });
    }
}
