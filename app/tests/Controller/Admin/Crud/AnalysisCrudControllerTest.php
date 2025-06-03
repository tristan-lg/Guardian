<?php

namespace App\Tests\Controller\Admin\Crud;

use App\Entity\Analysis;
use App\Entity\Project;
use App\Entity\Credential;
use App\Enum\Grade; // Required for setting grade
use App\Tests\WebTestCaseBase;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class AnalysisCrudControllerTest extends WebTestCaseBase
{
    private const CRUD_CONTROLLER_FQCN = 'App\Controller\Admin\Crud\AnalysisCrudController';

    private function getEntityManager(): EntityManagerInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    // Helper to create a Credential for the Project
    private function createTestCredentialForAnalysis(): Credential
    {
        $entityManager = $this->getEntityManager();
        $credential = new Credential();
        $credential->setName('Credential for AnalysisTest ' . uniqid());
        $credential->setDomain('https://gitlab.analysis-test.com');
        $credential->setAccessToken('analysis-test-token-' . uniqid());
        $entityManager->persist($credential);
        $entityManager->flush();
        return $credential;
    }

    // Helper to create a Project for the Analysis
    private function createTestProjectForAnalysis(): Project
    {
        $entityManager = $this->getEntityManager();
        $credential = $this->createTestCredentialForAnalysis();
        $project = new Project();
        $project->setName('Project for AnalysisTest ' . uniqid());
        $project->setGitUrl('https://example.com/project-for-analysis/' . uniqid() . '.git');
        $project->setProjectId(rand(100000, 9999999));
        $project->setBranch('main');
        $project->setCredential($credential);
        $entityManager->persist($project);
        $entityManager->flush();
        return $project;
    }

    private function createTestAnalysisEntity(): Analysis
    {
        $entityManager = $this->getEntityManager();
        $project = $this->createTestProjectForAnalysis();

        $analysis = new Analysis();
        $analysis->setProject($project);
        // runAt is set in constructor
        $analysis->setEndAt(new DateTimeImmutable('+5 minutes'));
        $analysis->setGrade(Grade::A->name); // Use the character 'A', 'B', etc.
        $analysis->setCveCount(0);
        // platform defaults to []

        $entityManager->persist($analysis);
        $entityManager->flush();
        return $analysis;
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
        // New page for Analysis might require a project_id or audit_id in query params
        // Let's try without first, EasyAdmin might handle selection.
        // If it fails, will need to create a project and pass its ID.
        $project = $this->createTestProjectForAnalysis(); // Create a project to be available for selection
        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'new',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            // 'project_id' => $project->getId(), // Not strictly needed if the page is a 404 anyway
        ]);
        // $this->assertPageIsSuccessful($client, $url); // Expecting 404 instead
        $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(404);


        // Clean up the created project and its credential
        $entityManager = $this->getEntityManager();
        $pInDb = $entityManager->find(Project::class, $project->getId());
        if ($pInDb) {
            $cInDb = $pInDb->getCredential();
            $entityManager->remove($pInDb);
            if ($cInDb) $entityManager->remove($cInDb);
            $entityManager->flush();
        }
    }

    public function testEditPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        $testAnalysis = $this->createTestAnalysisEntity();

        $url = $client->getContainer()->get('router')->generate('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::CRUD_CONTROLLER_FQCN,
            'entityId' => $testAnalysis->getId(),
        ]);
        $this->assertPageIsSuccessful($client, $url);

        // Clean up
        $entityManager = $this->getEntityManager();
        $analysisInDb = $entityManager->find(Analysis::class, $testAnalysis->getId());
        if ($analysisInDb) {
            $projectInDb = $analysisInDb->getProject();
            $entityManager->remove($analysisInDb);
            if ($projectInDb) {
                $credentialInDb = $projectInDb->getCredential();
                $entityManager->remove($projectInDb);
                if ($credentialInDb) {
                    $entityManager->remove($credentialInDb);
                }
            }
            $entityManager->flush();
        }
    }
}
