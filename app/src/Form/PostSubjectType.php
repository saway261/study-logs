<?php

namespace App\Form;

use App\Entity\PostSubject;
use App\Entity\Subject;
use App\Repository\SubjectRepository;
use App\Repository\SubjectStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PostSubjectType extends AbstractType
{
    public function __construct(
        private readonly SubjectRepository $subjectRepository,
        private readonly SubjectStatusRepository $statusRepository,
        private readonly EntityManagerInterface $em
    ) {}

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

        // 勉強時間（分）: 15分刻み 15..1440
        $builder->add('minutes', ChoiceType::class, [
            'choices'     => $this->minuteChoices(),
            'label' => false,
            'placeholder' => '時間（分）を選択',
            'required'    => true,
        ]);

        // 勉強内容: VARCHAR(100)
        $builder->add('summary', TextType::class, [
            'label' => false,
            'required' => false,
            'attr'     => [
                'maxlength'   => 100,
                'placeholder' => '例：配列とループの復習',
            ],
        ]);

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

    private function minuteChoices(): array
    {
        $choices = [];
        for ($m = 15; $m <= 1440; $m += 15) {
            $choices[$this->formatMinutes($m)] = $m; // 「1時間15分」=> 75
        }
        return $choices;
    }

    private function formatMinutes(int $m): string
    {
        $h = intdiv($m, 60);
        $r = $m % 60;
        if ($h > 0 && $r > 0) return sprintf('%d時間%d分', $h, $r);
        if ($h > 0)          return sprintf('%d時間', $h);
        return sprintf('%d分', $m);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSubject::class
        ]);
    }
}
