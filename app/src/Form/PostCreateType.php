<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PostCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // --- 日付（YYYY-MM-DD / カレンダーピッカー / 既定値：今日） ---
        $builder->add('date', DateType::class, [
            // モデルは \DateTimeImmutable を使う（エンティティと合わせる）
            'input'          => 'datetime_immutable',
            // 画面は単一の <input type="date"> にする
            'widget'         => 'single_text',
            // 画面表示・送受信のタイムゾーン
            'model_timezone' => 'Asia/Tokyo',
            'view_timezone'  => 'Asia/Tokyo',
            // 画面のフォーマット（HTML5のdateは yyyy-MM-dd 固定だが、念のため指定）
            'format'         => 'yyyy-MM-dd',
            // HTML5のブラウザ標準ピッカーを使う
            'html5'          => true,
            'required'       => true,
        ]);

        // --- 科目記録（繰り返し可能な行） ---
        $builder->add('postSubjects', CollectionType::class, [
            'entry_type'    => PostSubjectType::class,
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

        // 既定値：日付が未設定なら「今日（JST）」を入れる
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Post|null $post */
            $post = $event->getData();
            if (!$post) {
                return;
            }
            if ($post->getDate() === null) {
                $todayJst = (new \DateTimeImmutable('today', new \DateTimeZone('Asia/Tokyo')));
                $post->setDate($todayJst);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // このフォームは Post エンティティにバインド
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
