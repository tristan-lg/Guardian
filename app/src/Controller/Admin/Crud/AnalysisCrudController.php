<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
use App\Entity\Analysis;
use App\Entity\Project;
use App\Exception\ProjectFileNotFoundException;
use App\Form\ProjectType;
use App\Service\ProjectAnalysisService;
use App\Service\ProjectScanService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class AnalysisCrudController extends AbstractGuardianCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectScanService $projectScanService,
        private readonly ProjectAnalysisService $projectAnalysisService
    ) {}

    public static function getEntityFqcn(): string
    {
        return Analysis::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            //TODO - Faire une fonction pour calculer le grade d'une analyse
            //TODO - Afficher la liste des packages (dans un FormField::addTab) avec les vulnérabilités pour chaque package
            //TODO - Afficher la liste des packages (dans un FormField::addTab) avec la liste des erreurs de version pour chaque package


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
            ->setEntityLabelInSingular('Analyse')
            ->setEntityLabelInPlural('Analyses')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
        ;
    }

    public function new(AdminContext $context): Response
    {
        throw $this->createNotFoundException();
    }
}
