<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '⭐ 1 — Mauvais'          => 1,
                    '⭐⭐ 2 — Passable'        => 2,
                    '⭐⭐⭐ 3 — Correct'       => 3,
                    '⭐⭐⭐⭐ 4 — Bien'        => 4,
                    '⭐⭐⭐⭐⭐ 5 — Excellent' => 5,
                ],
                'label' => 'Note',
                'placeholder' => 'Choisissez une note',
                'attr' => [
                    'class' => 'w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500',
                ],
                'label_attr' => ['class' => 'block text-gray-300 mb-2 font-semibold'],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Partagez votre expérience avec ce produit...',
                    'class' => 'w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none',
                ],
                'label_attr' => ['class' => 'block text-gray-300 mb-2 font-semibold'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
