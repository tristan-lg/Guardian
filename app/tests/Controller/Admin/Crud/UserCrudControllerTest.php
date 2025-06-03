<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\User;
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\UserCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            // This should ideally not be called before createClient in tests,
            // but helper methods might use it. Kernel is booted by createClient.
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestUserEntity(string $emailSuffix = 'test'): User
    {
        $entityManager = $this->getEntityManager();
        $user = new User();
        $user->setEmail('crud_user_' . $emailSuffix . '@example.com');

        // Get password hasher from container
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']); // Default role for a new test user

        $entityManager->persist($user);
        $entityManager->flush();
        return $user;
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

        $testUser = $this->createTestUserEntity(uniqid());

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $testUser->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up
        $entityManager = $this->getEntityManager();
        $userInDb = $entityManager->find(User::class, $testUser->getId());
        if ($userInDb) {
            $entityManager->remove($userInDb);
            $entityManager->flush();
        }
    }
}
