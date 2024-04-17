<?php

namespace App\Command;

use App\Entity\Project;
use App\Service\GitlabApiService;
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

        $client = $this->gitlabApiService->getClient($project->getCredential());
        $composerLock = $client->searchFileOnProject($project, 'composer.lock');
        if (!$composerLock) {
            $io->error('Composer file not found');

            return Command::FAILURE;
        }

        // TODO - perform scan ?

        return Command::SUCCESS;
    }
}
