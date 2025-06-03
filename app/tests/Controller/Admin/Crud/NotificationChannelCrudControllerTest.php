<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType; // Required for setting the type
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;

class NotificationChannelCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\NotificationChannelCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestNotificationChannelEntity(string $nameSuffix = 'test'): NotificationChannel
    {
        $entityManager = $this->getEntityManager();
        $notificationChannel = new NotificationChannel();
        $notificationChannel->setName('Crud NC ' . $nameSuffix . ' ' . uniqid());

        // Assuming NotificationType::DISCORD exists and is a valid case.
        // If the enum is not found or this case does not exist, PHP will error.
        // Need to ensure App\Enum\NotificationType is correctly autoloaded and has this case.
        $notificationChannel->setType(NotificationType::DISCORD); // Corrected case name
        $notificationChannel->setValue('https://discord.example.com/webhook/' . uniqid());
        // $notificationChannel->setWorking(true); // Defaults to true in entity

        $entityManager->persist($notificationChannel);
        $entityManager->flush();
        return $notificationChannel;
    }

    public function testListPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );
        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
        ]);
        $this->assertPageIsSuccessful($client, $url);
    }

    public function testNewPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );
        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
        ]);
        $this->assertPageIsSuccessful($client, $url);
    }

    public function testEditPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        $testNotificationChannel = $this->createTestNotificationChannelEntity(uniqid());

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $testNotificationChannel->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up
        $entityManager = $this->getEntityManager();
        $ncInDb = $entityManager->find(NotificationChannel::class, $testNotificationChannel->getId());
        if ($ncInDb) {
            $entityManager->remove($ncInDb);
            $entityManager->flush();
        }
    }
}
