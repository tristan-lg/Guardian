<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
use App\Entity\Audit;
use App\Form\AuditType;
use App\Service\AnalysisService;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AuditCrudController extends AbstractGuardianCrudController
{
    public const string ACTION_START_ANALYSIS = 'startAnalysis';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AnalysisService $analysisService,
        private readonly FileService $fileService,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Audit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations générales', 'fas fa-info-circle'),
            IdField::new('id')->onlyOnDetail(),
            TextField::new('name', 'Nom du projet d\'audit'),
            TextEditorField::new('description', 'Description'),

            TextField::new('lastAnalysis.grade', 'Grade')
                ->setTemplatePath('@Admin/field/grade.html.twig')
                ->hideOnForm(),
            IntegerField::new('lastAnalysis.cveCount', 'Vulnérabilités')
                ->setTemplatePath('@Admin/field/cve_count_alert.html.twig')
                ->hideOnForm(),
            IntegerField::new('lastAnalysis.packagesCount', 'Dépendances')->hideOnForm(),
            DateTimeField::new('lastAnalysis.endAt', 'Date d\'analyse')->hideOnForm(),

            FormField::addTab('Analyse', 'fas fa-flask')->onlyOnDetail(),
            ArrayField::new('lastAnalysis.packages', false)
                ->setTemplatePath('@Admin/field/dependencies.html.twig')
                ->onlyOnDetail(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(
                Crud::PAGE_DETAIL,
                fn (Audit $audit) => sprintf('Audit de %s du %s',
                    $audit->getName(),
                    $audit->getLastAnalysis()?->getEndAt()->format('d/m/Y à H:i:s') ?? 'N/D'
                )
            )
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_DETAIL, Action::new('startAnalysis', 'Lancer une analyse')
                ->linkToCrudAction('startAnalysis')
                ->setIcon('fa fa-flask')
                ->setCssClass('btn btn-warning')
            )
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, self::ACTION_START_ANALYSIS, Action::EDIT, Action::DELETE])
        ;
    }

    public function new(AdminContext $context): Response
    {
        $request = $context->getRequest();

        $audit = new Audit();
        $form = $this->createForm(AuditType::class, $audit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($audit);

            /** @var UploadedFile $composerJsonFile */
            $composerJsonFile = $form->get('fileComposerJson')->getData();
            $audit->setFileComposerJson(
                $this->fileService->uploadFile($composerJsonFile)
            );

            /** @var UploadedFile $composerLockFile */
            $composerLockFile = $form->get('fileComposerLock')->getData();
            $audit->setFileComposerLock(
                $this->fileService->uploadFile($composerLockFile)
            );

            $this->em->flush();

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setDashboard(DashboardController::class)
                    ->setController(AuditCrudController::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        return $this->render('@Admin/crud/audit/new.html.twig', [
            'audit' => $audit,
            'form' => $form,
        ]);
    }

    public function startAnalysis(AdminContext $context): Response
    {
        $project = $this->getAudit($context);

        try {
            $this->analysisService->runAnalysis($project);
            $this->addFlash('success', 'L\'analyse a été executée avec succès');
        } catch (Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la programmation de l\'analyse : ' . $e->getMessage());
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(AuditCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($project->getId())
                ->generateUrl()
        );
    }

    private function getAudit(AdminContext $context): Audit
    {
        $project = $context->getEntity()->getInstance();
        if (!$project instanceof Audit) {
            throw new Exception('Audit not found');
        }

        return $project;
    }
}
