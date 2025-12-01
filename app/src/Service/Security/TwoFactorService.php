<?php

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

class TwoFactorService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly LoggerInterface $logger
    ) {}

    public function clearUserTwoFactor(User $user): void
    {
        $user->setTotpSecret(null);
        $this->em->flush();

        $this->logger->notice('[TwoFactorService] Cleared user two factor secret', ['user_target' => [
            'id' => $user->getId(),
        ]]);
    }

    public function setUserTwoFactorSecret(User $user, string $secret): void
    {
        $user->setTotpSecret($secret);
        $this->em->flush();

        $this->logger->notice('[TwoFactorService] Set user two factor secret', ['user_target' => [
            'id' => $user->getId(),
        ]]);
    }

    public function generateTwoFactorSecret(): string
    {
        return $this->totpAuthenticator->generateSecret();
    }

    public function getQrCodeContent(User $user): string
    {
        return $this->totpAuthenticator->getQRContent($user);
    }

    public function isTwoFactorCodeValid(User $user, string $code): bool
    {
        return $this->totpAuthenticator->checkCode($user, $code);
    }
}
