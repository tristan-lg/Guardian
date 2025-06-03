<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\Project;
use App\Entity\Credential; // Added for creating a test credential
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;

class ProjectCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\ProjectCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestCredential(string $name = 'Test Credential for Project'): Credential
    {
        $entityManager = $this->getEntityManager();
        $credential = new Credential();
        $credential->setName($name . ' ' . uniqid());
        $credential->setDomain('https://gitlab.example.com'); // Changed from setGitlabBaseUrl
        $credential->setAccessToken('test-token-' . uniqid()); // Changed from setToken
        // Set other mandatory fields for Credential if any
        // e.g. $credential->setClient(...); if it's a relation
        // $credential->setDiscordChannel(...);
        // $credential->setExpiresAt(...)
        $entityManager->persist($credential);
        $entityManager->flush();
        return $credential;
    }

    private function createTestProject(string $name = 'Test Project for Edit'): Project
    {
        $entityManager = $this->getEntityManager();

        $credential = $this->createTestCredential();

        $project = new Project();
        $project->setName($name);
        $project->setGitUrl('https://example.com/project/' . uniqid() . '.git');
        $project->setProjectId(rand(10000, 999999));
        $project->setBranch('main');
        $project->setCredential($credential);
        // $project->setFiles([]); // Default value already set in entity constructor

        $entityManager->persist($project);
        $entityManager->flush();
        return $project;
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
        // 1. Create client first
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        // 2. Now get EntityManager (kernel is booted by createClient)
        //    and create the project.
        $project = $this->createTestProject('Project To Edit ' . uniqid());

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $project->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up the created project
        // EntityManager is already available via $this->getEntityManager()
        // or can be fetched again if preferred, kernel is already booted.
        $entityManager = $this->getEntityManager();
        // Re-fetch the project in the current EM context if it was detached or for safety
        $projectInDb = $entityManager->find(Project::class, $project->getId());
        if ($projectInDb) {
            $entityManager->remove($projectInDb);
            // Also remove the associated credential if it's not used by other tests/projects
            $credential = $projectInDb->getCredential();
            if ($credential) {
                // Check if this credential was specifically created for this project and is safe to remove
                // For simplicity, if its name contains "Test Credential for Project", remove it.
                // This is a heuristic and might need refinement for more complex scenarios.
                if (str_contains($credential->getName() ?? '', 'Test Credential for Project')) {
                    $entityManager->remove($credential);
                }
            }
            $entityManager->flush();
        }
    }
}
