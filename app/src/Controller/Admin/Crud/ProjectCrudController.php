<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Service\AnalysisService;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
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
        private readonly AnalysisService $projectAnalysisService
    ) {}

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations générales', 'fas fa-info-circle'),
            IdField::new('id')->onlyOnDetail(),
            TextField::new('credentialsStatus', '<i class="fa-brands fa-gitlab" style="color: #ff7800"></i> Gitlab')
                ->setTemplatePath('@Admin/field/credential_status.html.twig')
                ->hideOnForm(),
            TextField::new('namespace', 'Groupe')->setDisabled(),
            TextField::new('name', 'Nom du projet original')->setDisabled()->hideOnIndex(),
            TextField::new('alias', 'Nom du projet'),
            AssociationField::new('credential', 'Identifiant')
                ->setTemplatePath('@Admin/field/association_readonly.html.twig')
                ->onlyOnDetail(),
            UrlField::new('gitUrl', 'Repository')->setDisabled(),
            UrlField::new('avatarUrl', 'Avatar')->onlyOnDetail(),
            TextField::new('lastGrade', 'Grade')
                ->setTemplatePath('@Admin/field/grade_simple.html.twig')
                ->onlyOnIndex(),
            TextField::new('lastGrade', 'Grade')
                ->setTemplatePath('@Admin/field/grade_simple.html.twig')
                ->onlyOnDetail(),
            IntegerField::new('lastVulnerabilitiesCount', 'Vulnérabilités')
                ->setTemplatePath('@Admin/field/cve_count_alert.html.twig')
                ->hideOnForm(),

            FormField::addTab('Fichiers', 'fas fa-file')->onlyOnDetail(),
            ArrayField::new('files', false)
                ->setTemplatePath('@Admin/field/files.html.twig')
                ->onlyOnDetail(),

            FormField::addTab('Analyses', 'fas fa-flask')->onlyOnDetail(),
            ArrayField::new('analyses', false)
                ->setTemplatePath('@Admin/field/analyses.html.twig')
                ->onlyOnDetail(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_DETAIL, Action::new('scan', 'Scanner les fichiers')
                ->linkToCrudAction('scan')
                ->setIcon('fa fa-wand-magic-sparkles')
                ->setCssClass('btn btn-primary')
            )
            ->add(Crud::PAGE_DETAIL, Action::new('startAnalysis', 'Lancer une analyse')
                ->linkToCrudAction('startAnalysis')
                ->setIcon('fa fa-flask')
                ->setCssClass('btn btn-warning')
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
            try {
                $this->projectScanService->scanProject($project);
                $this->em->flush();
            } catch (Exception $e) {
                $this->addFlash('danger', 'Impossible de lier le projet : ' . $e->getMessage());
            }

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

        try {
            $this->projectScanService->scanProject($project);
            $this->addFlash('success', 'Le projet a été scanné avec succès');
        } catch (Exception $e) {
            $this->addFlash('danger', 'Erreur lors du scan du projet : ' . $e->getMessage());
        }

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

        try {
            $this->projectAnalysisService->scheduleAnalysis($project);
            $this->addFlash('success', 'L\'analyse du projet a été programmée avec succès');
        } catch (Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la programmation de l\'analyse : ' . $e->getMessage());
        }

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

        if (!$project->getCredential()?->isValid()) {
            $this->addFlash('danger', 'Les identifiants du projet sont invalides ou expirés');

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(ProjectCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($project->getId())
                    ->generateUrl()
            );
        }

        /** @var null|string $fileKey */
        $fileKey = $context->getRequest()->get('file');
        if (!$fileKey) {
            throw new Exception('The parameter \'file\' is not set.');
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
