<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Credential;
use App\Security\Voter\CredentialVoter;
use App\Service\CredentialsService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class CredentialCrudController extends AbstractGuardianCrudController
{
    public const string ACTION_CHECK_CREDENTIAL = 'checkCredential';

    public function __construct(
        private readonly CredentialsService $credentialsService,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

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
            CollectionField::new('apiProjects', 'Projets git')
                ->setTemplatePath('@Admin/field/credential_projects_count.html.twig'),
            DateField::new('expireAt', 'Date d\'expiration')
                ->setTemplatePath('@Admin/field/expiration.html.twig')->hideOnForm(),

            TextField::new('accessToken', 'Access Token')->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::DELETE, CredentialVoter::DELETE)
            ->add(Crud::PAGE_DETAIL, Action::new(self::ACTION_CHECK_CREDENTIAL, 'Vérifier les identifiants')
                ->linkToCrudAction('checkCredential')
                ->setIcon('fa fa-key')
                ->setCssClass('btn btn-warning')
            )
            ->add(Crud::PAGE_DETAIL, Action::new('scanProjects', 'Détecter les projets')
                ->linkToCrudAction('scanProjects')
                ->setIcon('fa fa-sync')
                ->setCssClass('btn btn-info')
            )
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, self::ACTION_CHECK_CREDENTIAL, Action::EDIT, Action::DELETE])
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->overrideTemplate('crud/new', '@Admin/crud/credential/new.html.twig')
        ;
    }

    /**
     * @param Credential $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setLastNotification(null);
        $this->credentialsService->scheduleCredentialsCheck($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @param Credential $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        try {
            $this->credentialsService->scanCredentialProjects($entityInstance);
        } catch (Exception) {
            $this->addFlash('warning', "La détection des projets associés a échoué, l'identifiant est invalide ou le serveur est introuvable.");
        }
    }

    public function checkCredential(AdminContext $context): Response
    {
        $credential = $this->getCredential($context);

        $this->credentialsService->scheduleCredentialsCheck($credential);
        $this->addFlash('success', 'L\'analyse des identifiants a été réalisée');

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(CredentialCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($credential->getId())
                ->generateUrl()
        );
    }

    public function scanProjects(AdminContext $context): Response
    {
        $credential = $this->getCredential($context);

        try {
            $this->credentialsService->scanCredentialProjects($credential);
            $this->addFlash('success', 'L\'analyse des projects a été réalisée');
        } catch (Exception) {
            $this->addFlash('warning', "La détection des projets associés a échoué, l'identifiant est invalide ou le serveur est introuvable.");
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(CredentialCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($credential->getId())
                ->generateUrl()
        );
    }

    private function getCredential(AdminContext $context): Credential
    {
        $credential = $context->getEntity()->getInstance();
        if (!$credential instanceof Credential) {
            throw new Exception('Credential not found');
        }

        return $credential;
    }
}
