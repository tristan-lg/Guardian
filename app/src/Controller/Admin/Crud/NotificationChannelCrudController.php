<?php

namespace App\Controller\Admin\Crud;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Service\NotificationTestService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;

class NotificationChannelCrudController extends AbstractGuardianCrudController
{
    private const string ACTION_TEST_CHANNEL = 'checkChannel';

    public function __construct(
        private readonly NotificationTestService $notificationService,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return NotificationChannel::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('type', 'Type de notification')
                ->setFormType(EnumType::class)
                ->setFormTypeOption('class', NotificationType::class)
                ->setChoices([
                    'Discord' => NotificationType::DISCORD,
                    // 'Email' => NotificationType::EMAIL,
                ]),
            TextField::new('name', 'Libellé')
                ->setHelp('Nom du canal de notification à titre informatif')
                ->setRequired(true),
            TextField::new('value', 'Valeur')
                ->setHelp('Valeur du canal de notification (email, webhook, etc.)'),
            TextField::new('workingStatus', 'Etat')
                ->setTemplatePath('@Admin/field/channel_working.html.twig')
                ->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_DETAIL, Action::new(self::ACTION_TEST_CHANNEL, 'Envoyer une notification de test')
                ->linkToCrudAction(self::ACTION_TEST_CHANNEL)
                ->setIcon('fa fa-bell')
                ->setCssClass('btn btn-warning')
            )
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, self::ACTION_TEST_CHANNEL, Action::EDIT, Action::DELETE])
        ;
    }

    public function checkChannel(AdminContext $context): Response
    {
        $channel = $this->getNotificationChannel($context);

        if ($this->notificationService->performNotificationChannelTest($channel, true)) {
            $this->addFlash('success', 'La notification de test a été envoyée avec succès');
        } else {
            $this->addFlash('danger', 'Impossible d\'envoyer la notification de test');
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(NotificationChannelCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($channel->getId())
                ->generateUrl()
        );
    }

    /**
     * @param NotificationChannel $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setWorking(true);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function getNotificationChannel(AdminContext $context): NotificationChannel
    {
        $channel = $context->getEntity()->getInstance();
        if (!$channel instanceof NotificationChannel) {
            throw new Exception('NotificationChannel not found');
        }

        return $channel;
    }
}
