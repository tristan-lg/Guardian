<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Field\MessageField;
use App\Entity\Credential;
use App\Security\Voter\CredentialVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CredentialCrudController extends AbstractGuardianCrudController
{
    public static function getEntityFqcn(): string
    {
        return Credential::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Libellé de l\'identifiant'),
            TextField::new('domain', 'Domaine (exemple : gitlab.com)'),
            TextField::new('accessToken', 'Access Token'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::DELETE, CredentialVoter::DELETE)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->overrideTemplate('crud/new', '@Admin/crud/credential/new.html.twig');
    }


}
