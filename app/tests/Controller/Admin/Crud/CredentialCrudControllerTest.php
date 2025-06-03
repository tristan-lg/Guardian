<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\Credential;
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;

class CredentialCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\CredentialCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestCredentialEntity(string $nameSuffix = 'test'): Credential
    {
        $entityManager = $this->getEntityManager();
        $credential = new Credential();
        $credential->setName('Crud Credential ' . $nameSuffix . ' ' . uniqid());
        $credential->setDomain('https://gitlab.crud-test.com/' . uniqid());
        $credential->setAccessToken('crud-token-' . uniqid());
        // Set other mandatory fields if any, e.g. expireAt if not nullable
        // $credential->setExpireAt(new \DateTimeImmutable('+1 month'));

        $entityManager->persist($credential);
        $entityManager->flush();
        return $credential;
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

        $testCredential = $this->createTestCredentialEntity(uniqid());

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $testCredential->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up
        $entityManager = $this->getEntityManager();
        $credentialInDb = $entityManager->find(Credential::class, $testCredential->getId());
        if ($credentialInDb) {
            $entityManager->remove($credentialInDb);
            $entityManager->flush();
        }
    }
}
