<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Saisissez votre prénom']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Saisissez votre nom']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Saisissez votre e-mail']
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr' => ['placeholder' => 'Saisissez votre mot de passe']
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => ['placeholder' => 'Confirmez votre mot de passe']
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider mon inscription',
                'attr' => ['class' => 'btn w-100 btn-outline-dark']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
