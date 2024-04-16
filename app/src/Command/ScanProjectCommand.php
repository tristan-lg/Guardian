<?php

namespace App\Command;

use App\Entity\Credential;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:project:scan',
    description: 'Create a new user',
)]
class ScanProjectCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $client,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //Set the project
        $projectList = $this->em->getRepository(Project::class)->findAll();
//        $question = new ChoiceQuestion(
//            'Please select the project you want to scan',
//            array_map(fn (Project $project) => $project->getName(), $projectList),
//        );
//
//        $projectKey = $io->askQuestion($question);
//        $project = $projectList[array_search($projectKey, array_map(fn (Project $project) => $project->getName(), $projectList))];

        //TODO - For now, we force the project
        $project = $projectList[0];
        //Find project
        if ($project === null) {
            $io->error('Project not found');
            return Command::FAILURE;
        }

        $io->section('Scan the project : ' . $project->getName());

        //TODO - COnnect to git
        $composerLock = $this->searchComposerLock($project);
        if (!$composerLock) {
            $io->error('Composer file not found');
            return Command::FAILURE;
        }

        $composerLockContent = $this->downloadFile($project, $composerLock['path']);
        dd($composerLockContent);

        return Command::SUCCESS;
    }

    private function downloadFile(Project $project, string $path): string
    {
        $response = $this->client->request(
            'GET',
            'https://' . $project->getCredential()->getDomain() . '/api/v4/projects/' . $project->getProjectId(). '/repository/files/' . urlencode($path) . '/raw',
            [
                'headers' => $this->getHeaders($project->getCredential()),
                'query' => [
                    'ref' => 'master',
                ],
            ]
        );

        return $response->getContent();
    }

    private function searchComposerLock(Project $project, ?string $path = null): ?array
    {
        $trees = $this->requestTree($project, $path);
        $composerFile = array_filter($trees, fn ($tree) => $tree['name'] === 'composer.json');

        if ($composerFile) {
            return array_values($composerFile)[0];
        }

        foreach (array_filter($trees, fn($tree) => $tree['type'] === 'tree') as $tree) {
            if ($composerFile = $this->searchComposerLock($project, $tree['path'])) {
                return $composerFile;
            }
        }

        return null;
    }

    private function requestTree(Project $project, ?string $path = null): array
    {
        $response = $this->client->request(
            'GET',
            'https://' . $project->getCredential()->getDomain() . '/api/v4/projects/' . $project->getProjectId(). '/repository/tree',
            [
                'headers' => $this->getHeaders( $project->getCredential()),
                'query' => [
                    'path' => $path,
                    'ref' => 'master',
                    'per_page' => 100,
                ],
            ]
        );

        return json_decode($response->getContent(), true);
    }

    private function getHeaders(Credential $credential): array
    {
        return [
            'Authorization' => 'Bearer ' . $credential->getAccessToken(),
        ];
    }
}
