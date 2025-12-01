<?php

namespace App\Controller\Admin\Crud;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserCrudController extends AbstractGuardianCrudController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $resetPassword = Action::new('resetPassword', 'Réinitialiser le mot de passe')
            ->linkToCrudAction('resetPassword')
            ->setIcon('fa fa-key')
            ->setCssClass('btn btn-warning');

        return parent::configureActions($actions)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $resetPassword)
            ->add(Crud::PAGE_DETAIL, $resetPassword)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn('col-md-6 col-xxl-5');

        yield IdField::new('id')->hideOnForm();

        yield EmailField::new('email');

        yield ArrayField::new('roles')->hideOnForm();
    }

    /**
     * @param User $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userService->createNewUser($entityInstance);
    }

    /**
     * Reset user password and send account creation email.
     */
    public function resetPassword(AdminContext $context): RedirectResponse
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();

        try {
            $this->userService->resetUserPassword($user);
            $this->addFlash('success', sprintf('Le mot de passe de l\'utilisateur "%s" a été réinitialisé. Un email a été envoyé.', $user->getEmail()));
        } catch (\Exception $e) {
            $this->addFlash('error', sprintf('Erreur lors de la réinitialisation du mot de passe : %s', $e->getMessage()));
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($user->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
}
