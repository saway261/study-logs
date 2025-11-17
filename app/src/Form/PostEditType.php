<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class PostEditType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // --- 科目記録（繰り返し可能な行） ---
        $builder->add('postSubjects', CollectionType::class, [
            'entry_type'    => PostSubjectEditType::class,
            'entry_options' => ['label' => false],
            'allow_add'     => true,   // 「＋」で追加
            'allow_delete'  => true,   // 行削除も可能に
            'by_reference'  => false,  // addPostSubject() を呼ばせるため false
            'prototype'     => true,   // JSで行を複製するための雛形を生成
            'label'         => false,
        ]);

        // --- 日報（長文） ---
        $builder->add('content', TextareaType::class, [
            'required' => true,
            'attr'     => [
                'rows' => 20,
                'maxlength' => 1000,
                'placeholder' => '今日の学習メモや気づきなど',
            ],
        ]);
        $builder->add('save', SubmitType::class, [
            'label' => '保存',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // このフォームは Post エンティティにバインド
        $resolver->setDefaults([
            'data_class' => Post::class,
            'method' => 'PUT'
        ]);
    }
}
