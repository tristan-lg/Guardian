<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\IsTwoFactorCodeValid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EnableTwoFactorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('totpSecret', HiddenType::class) // Needed to ensure code do not change on form rerender
            ->add('code', TextType::class, [
                'label' => 'form.two_factor.code',
                'attr' => [
                    'autocomplete' => 'one-time-code',
                    'inputmode' => 'numeric',
                    'maxlength' => 6,
                    'pattern' => '[0-9]*',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 6, max: 6),
                    new Regex('/^\d{6}$/'),
                    new IsTwoFactorCodeValid(),
                ],
                'mapped' => false,
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
