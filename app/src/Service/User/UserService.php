<?php

namespace App\Service\User;

use App\Entity\User;
use App\Mail\User\AccountCreatedEmail;
use App\Service\Mail\MailService;
use App\Service\Security\Login\FirstLoginService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailService $mailService,
        private readonly FirstLoginService $firstLoginService,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Create a new user account, persist it to the database, and send a first login email.
     *
     * @throws Exception
     */
    public function createNewUser(User $user): void
    {
        $user->setPassword(''); // Ensure password is empty for first login
        $this->em->persist($user);
        $this->em->flush();

        try {
            $firstLoginLink = $this->firstLoginService->createFirstLoginLink($user);
        } catch (Exception $e) {
            $this->logger->error('Error creating first login link', ['user' => $user->getEmail(), 'exception' => $e]);

            throw $e;
        }

        $this->mailService->send($user, AccountCreatedEmail::class, [
            AccountCreatedEmail::LOGIN_URL_DETAIL => $firstLoginLink,
        ]);

        $this->logger->notice('User account email sent', ['user' => $user->getEmail()]);
    }

    /**
     * Reset user password and send a new first login email.
     *
     * @throws Exception
     */
    public function resetUserPassword(User $user): void
    {
        $user->setPassword(''); // Reset password to empty string
        $this->em->flush();

        try {
            $firstLoginLink = $this->firstLoginService->createFirstLoginLink($user);
        } catch (Exception $e) {
            $this->logger->error('Error creating first login link for password reset', ['user' => $user->getEmail(), 'exception' => $e]);

            throw $e;
        }

        $this->mailService->send($user, AccountCreatedEmail::class, [
            AccountCreatedEmail::LOGIN_URL_DETAIL => $firstLoginLink,
        ]);

        $this->logger->notice('Password reset email sent', ['user' => $user->getEmail()]);
    }
}
