<?php

namespace App\Command;

use App\Entity\Package;
use App\Entity\Project;
use App\Service\GitlabApiService;
use App\Service\ProjectAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:project:scan',
    description: 'Run a project scan - For POC & dev purpose only',
)]
class ScanProjectCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GitlabApiService $gitlabApiService,
        private readonly ProjectAnalysisService $projectAnalysisService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Set the project
        $projectList = $this->em->getRepository(Project::class)->findAll();
        //        $question = new ChoiceQuestion(
        //            'Please select the project you want to scan',
        //            array_map(fn (Project $project) => $project->getName(), $projectList),
        //        );
        //
        //        $projectKey = $io->askQuestion($question);
        //        $project = $projectList[array_search($projectKey, array_map(fn (Project $project) => $project->getName(), $projectList))];

        // TODO - For now, we force the project
        $project = $projectList[0] ?? null;
        // Find project
        if (!$project || !$project->getCredential()) {
            $io->error('Project not found');

            return Command::FAILURE;
        }

        $io->section('Scan the project : ' . $project->getName());

//        $client = $this->gitlabApiService->getClient($project->getCredential());
//        $composerLock = $client->searchFileOnProject($project, 'composer.lock');
//        if (!$composerLock) {
//            $io->error('Composer file not found');
//
//            return Command::FAILURE;
//        }

//        $package = new Package();
//        $package->setName('adodb/adodb-php')
//            ->setInstalledVersion('5.0.0');
//
//        $package2 = new Package();
//        $package2->setName('composer/composer')
//            ->setInstalledVersion('2.7.0');
//
//        $package3 = new Package();
//        $package3->setName('guzzlehttp/guzzle')
//            ->setInstalledVersion('6.1.0');


//        $this->projectAnalysisService->injectPackageSecurityAdvisories([
//            $package,
//            $package2,
//            $package3,
//        ]);
//
//        dd($package, $package2, $package3);
        // TODO - perform scan ?

        return Command::SUCCESS;
    }
}
