<?php

namespace App\Form;

use App\Entity\GameTypes;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('email', EmailType::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Modérateur' => 'ROLE_MODERATOR',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Roles',
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'label' => 'Password (leave blank to keep current)',
                'mapped' => false,
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Email Verified',
            ])
            ->add('game_types', EntityType::class, [
                'class' => GameTypes::class,
                'choice_label' => 'type',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Preferred Game Types',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
