<?php

namespace App\DataFixtures;

use App\Entity\Analysis;
use App\Entity\Audit;
use App\Entity\Credential;
use App\Entity\DTO\EndOfLifeCycleDto;
use App\Entity\DTO\PlatformDTO;
use App\Entity\File;
use App\Entity\Project;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createAdmin($manager);
        $creds = $this->createFakeCredentials($manager);

        $projects = [
            'Project 1' => 'A',
            'Project 2' => 'B',
            'Project 3' => 'C',
            'Project 4' => 'D',
            'Project 5' => 'E',
        ];

        foreach ($projects as $projectName => $grade) {
            $project = $this->createFakeProject($manager, $creds);
            $project->setName($projectName);
            $this->createFakeAnalysis($manager, $project, $grade);
        }

        $manager->flush();
    }

    private function createAdmin(ObjectManager $manager): User
    {
        $admin = new User();
        $admin->setEmail('test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'test'));

        $manager->persist($admin);

        return $admin;
    }

    private function createFakeCredentials(ObjectManager $manager): Credential
    {
        $credentials = new Credential();
        $credentials->setDomain('example.com');
        $credentials->setName('Example credential');
        $credentials->setAccessToken('fake-access-token');
        $credentials->setExpireAt(new DateTimeImmutable('+2 years'));
        $manager->persist($credentials);

        return $credentials;
    }

    private function createFakeProject(ObjectManager $manager, Credential $credential): Project
    {
        $project = new Project();
        $project->setName('Example project');
        $project->setCredential($credential);
        $project->setProjectId(1);
        $project->setFiles([]);
        $project->setBranch('main');

        $manager->persist($project);

        return $project;
    }

    private function createFakeAnalysis(ObjectManager $manager, Project $project, string $grade): void
    {
        $audit = new Audit();
        $audit->setFileComposerJson($this->createFile($manager))
            ->setFileComposerLock($this->createFile($manager))
            ->setName('Fake audit')
            ->setDescription('Fake audit desc');

        $eolPhp = new EndOfLifeCycleDto(
            releaseDate: '2021-11-25',
            eol: '2024-11-25',
            latest: '8.3.12',
            latestReleaseDate: '2024-06-20',
            lts: true,
            support: 'Security fixes only',
        );
        $eolSf = new EndOfLifeCycleDto(
            releaseDate: '2021-11-25',
            eol: '2024-11-25',
            latest: '7.3.22',
            latestReleaseDate: '2024-06-20',
            lts: false,
            support: 'Security fixes only',
        );

        $analysis = new Analysis();
        $analysis->setProject($project)
            ->setRunAt(new DateTimeImmutable())
            ->setGrade($grade)
            ->setEndAt(new DateTimeImmutable('+2 seconds'))
            ->setCveCount(0)
            ->setAdvisoryHash('fake-hash')
            ->setPlatform(new PlatformDTO('8.3', $eolPhp, '6.4', $eolSf))
            ->setAudit($audit)
        ;

        $manager->persist($audit);
        $manager->persist($analysis);
    }

    private function createFile(ObjectManager $manager): File
    {
        $file = new File();
        $file->setFilename('Fake file');

        $manager->persist($file);

        return $file;
    }
}
