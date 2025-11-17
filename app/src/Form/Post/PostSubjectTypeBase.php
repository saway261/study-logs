<?php

namespace App\Form\Post;

use App\Entity\PostSubject;
use App\Repository\SubjectRepository;
use App\Repository\SubjectStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class PostSubjectTypeBase extends AbstractType
{
    public function __construct(
        protected readonly SubjectRepository $subjectRepository,
        protected readonly SubjectStatusRepository $statusRepository,
        protected readonly EntityManagerInterface $em
    ) {}

    protected function addCommonFields(FormBuilderInterface $builder): void
    {
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
    }

    protected function minuteChoices(): array
    {
        $choices = [];
        for ($m = 15; $m <= 1440; $m += 15) {
            $choices[$this->formatMinutes($m)] = $m; // 「1時間15分」=> 75
        }
        return $choices;
    }

    protected function formatMinutes(int $m): string
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
