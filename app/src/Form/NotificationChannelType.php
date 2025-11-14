<?php

namespace App\Form;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NotificationChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('name', TextType::class, [
                'label' => 'Libellé du canal',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => NotificationType::cases(),
                'choice_label' => fn (NotificationType $type) => $type->name,
                'empty_data' => null,
            ])
        ;

        $builder->addDependent('value', 'type', function (DependentField $field, ?NotificationType $notificationType): void {
            if (null === $notificationType) {
                return;
            }

            $field->add(TextType::class, [
                'label' => $notificationType->getTokenLabel(),
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => $notificationType === NotificationType::Email ? 255 : 1024]),
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotificationChannel::class,
        ]);
    }
}
