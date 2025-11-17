<?php

namespace App\Form\Subject;

use App\Entity\Subject;
use App\Entity\SubjectStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class SubjectEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => '科目名',
                'attr' => ['maxlength' => 20],
            ])
            ->add('status', EntityType::class, [
                'class' => SubjectStatus::class,
                'choice_label' => 'status',    // 例: 1=学習中, 2=学習完了
                'label' => 'ステータス',
            ])
            ->add('save', SubmitType::class, [
                'label' => '保存',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subject::class,
            'method' => 'PUT'
        ]);
    }
}
