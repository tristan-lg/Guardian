<?php

namespace App\Controller\Admin\Crud;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class NotificationChannelCrudController extends AbstractGuardianCrudController
{
    public static function getEntityFqcn(): string
    {
        return NotificationChannel::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('type', 'Type de notification')
                ->setFormType(EnumType::class)
                ->setFormTypeOption('class', NotificationType::class),
            TextField::new('value', 'Valeur')
                ->setHelp('Valeur du canal de notification (email, webhook, etc.)'),
        ];
    }
}
