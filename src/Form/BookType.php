<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=> 'Название книги',
                'required' => true
            ])
            ->add('author', TextType::class, [
                'label' => 'Автор книги',
                'required' => true
            ])
            ->add('cover', FileType::class, [
                'label' => 'Файл с облошкой книги',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'maxSizeMessage' => 'Размер файла не должен быть больше 10 Мб.',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Загрузите файл в формате jpg или png.',
                    ])
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Файл с книгой в формате PDF',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'maxSizeMessage' => 'Размер файла не должен быть больше 10 Мб.',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Загрузите файл в формате PDF.',
                    ])
                ]
            ])
            ->add('readDate', DateTimeType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y') - 100),
                'label' => 'Дата прочтения',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
