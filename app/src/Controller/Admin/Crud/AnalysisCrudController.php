<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Analysis;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
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

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        )->andWhere('entity.project IS NOT NULL');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Résultats de l\'analyse', 'fas fa-flask'),
            IdField::new('id')->hideOnForm(),
            AssociationField::new('project', 'Projet')->hideOnForm(),
            DateTimeField::new('runAt', 'Date de début')->hideOnForm(),
            DateTimeField::new('endAt', 'Date de fin')->hideOnForm(),
            TextField::new('grade', 'Grade')
                ->setTemplatePath('@Admin/field/grade.html.twig')
                ->hideOnForm(),
            IntegerField::new('packagesCount', 'Dépendances')->hideOnForm(),
            IntegerField::new('cveCount', 'Vulnérabilités')
                ->setTemplatePath('@Admin/field/cve_count_alert.html.twig')
                ->hideOnForm(),
            TextField::new('advisoryHash', 'Hash des vulnérabilités')->onlyOnDetail(),

            FormField::addTab('Liste des dépendances', 'fas fa-cube'),
            ArrayField::new('packages', false)
                ->setTemplatePath('@Admin/field/dependencies.html.twig')
                ->onlyOnDetail(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(
                Crud::PAGE_DETAIL,
                fn (Analysis $analysis) => sprintf('Analyse de %s du %s',
                    $analysis->getAnalysableTargetName(),
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

            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
        ;
    }

    public function new(AdminContext $context): Response
    {
        throw $this->createNotFoundException();
    }
}
