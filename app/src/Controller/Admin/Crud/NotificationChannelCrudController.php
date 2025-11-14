<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\DashboardController;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Form\NotificationChannelType;
use App\Service\Notification\NotificationCheckService;
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
        private readonly NotificationCheckService $notificationService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $em
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
                    'Discord' => NotificationType::Discord,
                    'Mattermost' => NotificationType::Mattermost,
                    'Email' => NotificationType::Email,
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

        if ($this->notificationService->performNotificationChannelTest($channel, sendTestNotification: true)) {
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

    public function new(AdminContext $context): Response
    {
        $request = $context->getRequest();

        $notificationChannel = new NotificationChannel();
        $form = $this->createForm(NotificationChannelType::class, $notificationChannel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->notificationService->isNotificationChannelValid($notificationChannel)) {
                $this->em->persist($notificationChannel);
                $this->em->flush();

                return $this->redirect(
                    $this->adminUrlGenerator
                        ->setDashboard(DashboardController::class)
                        ->setController(NotificationChannelCrudController::class)
                        ->setAction(Action::INDEX)
                        ->generateUrl()
                );
            }

            $this->addFlash('danger', 'Impossible de se connecter au canal de communication, vérifiez les informations fournies.');
        }

        return $this->render('@Admin/crud/notification_channel/new.html.twig', [
            'notificationChannel' => $notificationChannel,
            'form' => $form,
        ]);
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
