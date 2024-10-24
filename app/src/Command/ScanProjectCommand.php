<?php

namespace App\Command;

use App\Controller\Admin\Crud\AnalysisCrudController;
use App\Entity\Project;
use App\Service\AnalysisService;
use App\Service\ProjectScanService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:project:scan',
    description: 'Run a project scan',
)]
class ScanProjectCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AnalysisService $projectAnalysisService,
        private readonly ProjectScanService $projectScanService,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Set the project
        $projectList = $this->em->getRepository(Project::class)->findAll();

        if (empty($projectList)) {
            $io->error('No project found');

            return Command::FAILURE;
        }

        if (1 === count($projectList)) {
            $project = $projectList[0];
        } else {
            $question = new ChoiceQuestion(
                'Please select the project you want to scan',
                array_map(fn (Project $project) => $project->getName(), $projectList),
            );

            $projectKey = $io->askQuestion($question);
            $project = $projectList[array_search($projectKey, array_map(fn (Project $project) => $project->getName(), $projectList))] ?? null;
        }

        // Find project
        if (!$project || !$project->getCredential()) {
            $io->error('Project not found');

            return Command::FAILURE;
        }

        // Check if project files are scanned
        if (0 === count($project->getFiles())) {
            $io->section('Scan the project files : ' . $project->getName());

            try {
                $this->projectScanService->scanProject($project);
            } catch (Exception $e) {
                $io->error('Error while scanning project files : ' . $e->getMessage());

                return Command::FAILURE;
            }
        }

        $io->section('Run project analysis for : ' . $project->getName());
        $analysis = $this->projectAnalysisService->runAnalysis($project);
        if (!$analysis) {
            $io->error('Analysis failed : Credential expired or not found');

            return Command::FAILURE;
        }

        $urlToAnalysis = $this->adminUrlGenerator
            ->setController(AnalysisCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($analysis->getId())
            ->generateUrl()
        ;

        $io->success("Analysis done with grade [{$analysis->getGrade()}]");

        $io->block("See the result at : {$urlToAnalysis}");

        return Command::SUCCESS;
    }
}
