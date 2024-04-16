<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = new User();

        /** @var string $email */
        $email = $io->ask('Please enter the user email : ');
        $user->setEmail($email);

        /** @var string $plainPassword */
        $plainPassword = $io->askHidden('Please enter the user password : ');
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setRoles([Role::ROLE_USER->value]);

        // Persist
        $this->em->persist($user);
        $this->em->flush();

        $io->success('User created successfully!');

        return Command::SUCCESS;
    }
}
