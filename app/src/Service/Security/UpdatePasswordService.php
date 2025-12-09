<?php

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdatePasswordService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
    ) {}

    public function setNewPassword(User $user, #[SensitiveParameter] string $newPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $this->entityManager->flush();

        $this->logger->notice('[ResetPasswordService] Password updated', ['user_target' => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ]]);
    }

    public function isPasswordValid(User $user, #[SensitiveParameter] string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }
}
