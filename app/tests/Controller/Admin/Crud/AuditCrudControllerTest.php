<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\Audit;
use App\Entity\File; // Required for creating File entities
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;

class AuditCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\AuditCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestFileEntity(string $filename): File
    {
        $entityManager = $this->getEntityManager();
        $file = new File();
        $file->setFilename($filename . '_' . uniqid() . '.txt'); // Ensure unique filename
        $entityManager->persist($file);
        $entityManager->flush();
        return $file;
    }

    private function createTestAuditEntity(string $nameSuffix = 'test'): Audit
    {
        $entityManager = $this->getEntityManager();

        $fileComposerJson = $this->createTestFileEntity('composer.json');
        $fileComposerLock = $this->createTestFileEntity('composer.lock');

        $audit = new Audit();
        $audit->setName('Crud Audit ' . $nameSuffix . ' ' . uniqid());
        $audit->setFileComposerJson($fileComposerJson);
        $audit->setFileComposerLock($fileComposerLock);
        // description defaults to ''

        $entityManager->persist($audit);
        $entityManager->flush();
        return $audit;
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
        // The 'new' page for Audit likely requires file uploads, which is complex for a simple page load test.
        // It might also be disabled like Analysis new page.
        // For now, let's see if it loads or gives a specific error (e.g., 404 or 500 if it expects parameters).
        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
        ]);
        // $this->assertPageIsSuccessful($client, $url);
        // Instead, check for a specific status or behavior if 'new' is restricted.
        // For now, trying to load it and will adjust based on outcome.
        // $client->request('GET', $url);

        // If new Audits are created via a different mechanism (e.g. API or specific form),
        // this page might be a 404 or redirect. Let's check for non-500 first.
        // $this->assertNotEquals(500, $client->getResponse()->getStatusCode(), "New Audit page should not be a server error.");
        // If it's specifically disabled like Analysis new, it would be a 404.
        // If it's a form that loads, it would be 200.
        // Assuming it passed because it was a 200.
        $this->assertPageIsSuccessful($client, $url);
    }

    public function testEditPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        $testAudit = $this->createTestAuditEntity(uniqid());

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $testAudit->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up
        $entityManager = $this->getEntityManager();
        $auditInDb = $entityManager->find(Audit::class, $testAudit->getId());
        if ($auditInDb) {
            $fileJson = $auditInDb->getFileComposerJson();
            $fileLock = $auditInDb->getFileComposerLock();
            $entityManager->remove($auditInDb);
            if ($fileJson) $entityManager->remove($fileJson);
            if ($fileLock) $entityManager->remove($fileLock);
            $entityManager->flush();
        }
    }
}
