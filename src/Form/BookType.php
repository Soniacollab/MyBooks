<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\User;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ["placeholder" => "Saisissez le titre du livre"],
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'attr' => ["placeholder" => "Saisissez le nom de l'auteur"],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ["placeholder" => "Saisissez la description du livre"],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => array_combine(Book::GENRES, Book::GENRES),
                'placeholder' => 'Choisissez un genre',
            ])
            ->add('coverImage', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/png, image/jpeg',
                    'onchange' => 'previewImage(event)' // Pour déclencher la prévisualisation
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer mon livre',
                'attr' => [
                    'class' => 'btn w-100 btn-outline-dark'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
