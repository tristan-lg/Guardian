<?php

namespace App\Form;

use App\Entity\Audit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du projet audité',
                'attr' => [
                    'class' => 'field-text',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description sur le projet d\'audit',
                'attr' => [
                    'class' => 'field-text',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('fileComposerJson', FileType::class, [
                'label' => 'Fichier composer.json',
                'mapped' => false,
                'attr' => [
                    'accept' => '.json',
                ],
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'application/json',
                            'application/x-json',
                            'text/json',
                        ],
                    ]
                    ),
                ],
            ])
            ->add('fileComposerLock', FileType::class, [
                'label' => 'Fichier composer.lock',
                'mapped' => false,
                'attr' => [
                    'accept' => '.lock',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'application/json',
                            'application/x-json',
                            'text/json',
                        ],
                    ]
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Audit::class,
        ]);
    }
}
