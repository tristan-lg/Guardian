<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Analysis;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

class AnalysisCrudController extends AbstractGuardianCrudController
{
    public static function getEntityFqcn(): string
    {
        return Analysis::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Résultats de l\'analyse', 'fas fa-flask'),
            IdField::new('id')->hideOnForm(),
            AssociationField::new('project', 'Projet')->hideOnForm(),
            DateTimeField::new('runAt', 'Date de début')->hideOnForm(),
            DateTimeField::new('endAt', 'Date de fin')->hideOnForm(),
            TextField::new('grade', 'Grade')->hideOnForm(),
            IntegerField::new('packagesCount', 'Dépendances')->hideOnForm(),

            FormField::addTab('Liste des dépendances', 'fas fa-cube'),
            // TODO - Afficher la liste des packages (dans un FormField::addTab) avec les vulnérabilités pour chaque package
            // TODO - Afficher la liste des packages (dans un FormField::addTab) avec la liste des erreurs de version pour chaque package

            //            TextField::new('name', 'Nom du projet'),
            //            AssociationField::new('credential', 'Identifiant')
            //                ->setTemplatePath('@Admin/field/association_readonly.html.twig')
            //                ->hideOnForm(),
            //
            //            FormField::addTab('Fichiers', 'fas fa-file'),
            //            ArrayField::new('files', false)
            //                ->setTemplatePath('@Admin/field/files.html.twig')
            //                ->hideOnForm(),
            //
            //            FormField::addTab('Analyses', 'fas fa-flask'),
            //            ArrayField::new('analyses', false)
            //                ->setTemplatePath('@Admin/field/analyses.html.twig')
            //                ->onlyOnDetail(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(
                Crud::PAGE_DETAIL,
                fn (Analysis $analysis) => sprintf('Analyse de %s du %s',
                    $analysis->getProject()->getName(),
                    $analysis->getRunAt()->format('d/m/Y à H:i:s')
                )
            )
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)

            // TODO - Pourquoi la supression marche pas ?
            ->add(Crud::PAGE_EDIT, Action::DELETE)
        ;
    }

    public function new(AdminContext $context): Response
    {
        throw $this->createNotFoundException();
    }
}
