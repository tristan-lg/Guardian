<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
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

class ProjectCrudController extends AbstractGuardianCrudController
{
    public const string ACTION_SCAN = 'scan';
    public const string ACTION_START_ANALYSIS = 'startAnalysis';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectScanService $projectScanService,
        private readonly ProjectAnalysisService $projectAnalysisService
    ) {}

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations générales', 'fas fa-info-circle'),
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du projet'),
            AssociationField::new('credential', 'Identifiant')
                ->setTemplatePath('@Admin/field/association_readonly.html.twig')
                ->hideOnForm(),

            FormField::addTab('Fichiers', 'fas fa-file'),
            ArrayField::new('files', false)
                ->setTemplatePath('@Admin/field/files.html.twig')
                ->hideOnForm(),

            FormField::addTab('Analyses', 'fas fa-flask'),
            ArrayField::new('analyses', false)
                ->setTemplatePath('@Admin/field/analyses.html.twig')
                ->onlyOnDetail(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Projet')
            ->setEntityLabelInPlural('Projets')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_DETAIL, Action::new('scan', 'Scanner le projet')
                ->linkToCrudAction('scan')
                ->setIcon('fa fa-wand-magic-sparkles')
                ->setCssClass('btn btn-warning')
            )
            ->add(Crud::PAGE_DETAIL, Action::new('startAnalysis', 'Lancer une analyse')
                ->linkToCrudAction('startAnalysis')
                ->setIcon('fa fa-flask')
                ->setCssClass('btn btn-primary')
            )
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, self::ACTION_START_ANALYSIS, self::ACTION_SCAN, Action::EDIT, Action::DELETE])
        ;
    }

    public function new(AdminContext $context): Response
    {
        $request = $context->getRequest();

        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($project);

            // Scan project for the first time
            $this->projectScanService->scanProject($project);
            $this->em->flush();

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setDashboard(DashboardController::class)
                    ->setController(ProjectCrudController::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        return $this->render('@Admin/crud/project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    public function scan(AdminContext $context): Response
    {
        $project = $this->getProject($context);
        $this->projectScanService->scanProject($project);
        $this->addFlash('success', 'Le projet a été scanné avec succès');

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(ProjectCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($project->getId())
                ->generateUrl()
        );
    }

    public function startAnalysis(AdminContext $context): Response
    {
        $project = $this->getProject($context);

        $this->projectAnalysisService->scheduleAnalysis($project);
        $this->addFlash('success', 'L\'analyse du projet a été programmée avec succès');

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(ProjectCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($project->getId())
                ->generateUrl()
        );
    }

    public function viewFile(AdminContext $context): Response
    {
        $project = $this->getProject($context);

        /** @var null|string $fileKey */
        $fileKey = $context->getRequest()->get('file');
        if (!$fileKey) {
            throw new ProjectFileNotFoundException($fileKey);
        }

        $fileContent = $this->projectScanService->getFileJsonContent($project, $fileKey);

        return $this->render('@Admin/crud/project/view_file.html.twig', [
            'project' => $project,
            'fileKey' => $fileKey,
            'fileContent' => $fileContent,
        ]);
    }

    private function getProject(AdminContext $context): Project
    {
        $project = $context->getEntity()->getInstance();
        if (!$project instanceof Project) {
            throw new Exception('Project not found');
        }

        return $project;
    }
}
