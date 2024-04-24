<?php

namespace App\Command;

use App\Entity\Project;
use App\Service\ProjectAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:project:purge',
    description: 'Removes the outdated analyses for all projects',
)]
class PurgeProjectCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProjectAnalysisService $projectAnalysisService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Removes the outdated analyses for all projects');
        $this->addOption('all', 'a', null, 'Purge all analyses');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $purgeAll = $input->getOption('all');

        $io = new SymfonyStyle($input, $output);

        if ($purgeAll) {
            $io->title('Purge all analyses');
        } else {
            $io->title('Purge outdated analyses');
        }

        // Set the project
        $projectList = $this->em->getRepository(Project::class)->findAll();

        foreach ($projectList as $project) {
            $io->writeln("Purge project : {$project->getName()}");
            $this->projectAnalysisService->clearOutdatedAnalysis(
                $project,
                $purgeAll ? 0 : ProjectAnalysisService::ANALYSYS_TO_KEEP
            );
        }

        $io->success('Purge done !');

        return Command::SUCCESS;
    }
}
