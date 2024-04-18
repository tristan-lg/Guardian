<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Credential;
use App\Security\Voter\CredentialVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Identifiant')
            ->setEntityLabelInPlural('Identifiants')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::DELETE, CredentialVoter::DELETE);
    }
}
