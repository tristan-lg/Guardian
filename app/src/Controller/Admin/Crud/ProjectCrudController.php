<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Service\ProjectScanService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ProjectCrudController extends AbstractCrudController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectScanService $projectScanService
    ) {
    }

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
                ->hideOnForm()
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Projet')
            ->setEntityLabelInPlural('Projets');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, Action::new('scan', 'Scanner le projet')
                ->linkToCrudAction('scan')
            )
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

            //Scan project for the first time
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
            'form' => $form
        ]);
    }

    public function scan(AdminContext $context): Response
    {
        $project = $context->getEntity()->getInstance();
        if (!$project) {
            throw new Exception('Project not found');
        }

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

    public function viewFile(AdminContext $context): Response
    {
        $project = $context->getEntity()->getInstance();
        if (!$project instanceof Project) {
            throw new Exception('Project not found');
        }

        /** @var string|null $fileKey */
        $fileKey = $context->getRequest()->get('file');
        if (!$fileKey) {
            throw new Exception('File not found');
        }

        $filePath = $project->getFiles()[$fileKey];
        if (!$filePath) {
            throw new Exception('File not found');
        }

        $fileContent = $this->projectScanService->getFileJsonContent($project, $filePath);

        return $this->render('@Admin/crud/project/view_file.html.twig', [
            'project' => $project,
            'fileKey' => $fileKey,
            'fileContent' => $fileContent
        ]);
    }




}
