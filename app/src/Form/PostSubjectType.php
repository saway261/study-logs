<?php

namespace App\Form;

use App\Entity\PostSubject;
use App\Entity\Subject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PostSubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 科目: セレクト（Subjectエンティティ）
        $builder->add('subject', EntityType::class, [
            'class'        => Subject::class,
            'label' => false,
            'choice_label' => 'name',
            'placeholder'  => '科目を選択',
            'required'     => true,
            // 必要なら有効科目のみ表示するため query_builder を渡す
            // 'query_builder' => fn (SubjectRepository $r) => $r->createActiveQB(),
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
            'data_class' => PostSubject::class,
        ]);
    }
}
