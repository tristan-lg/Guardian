<?php

namespace App\Entity;

use App\Component\Mail\MailableUser;
use App\Component\SecureLink\SecureLinkEntityInterface;
use App\Entity\Interface\NameableEntityInterface;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, PasswordUpgraderInterface, NameableEntityInterface, TwoFactorInterface, MailableUser, SecureLinkEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [Role::ROLE_USER->value];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $totpSecret = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        if (empty($this->roles)) {
            return [Role::ROLE_USER->value];
        }

        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        // @phpstan-ignore-next-line
        return $this->getEmail();
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $this->setPassword($newHashedPassword);
    }

    public static function getSingular(): string
    {
        return 'Utilisateur';
    }

    public static function getPlural(): string
    {
        return 'Utilisateurs';
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return null !== $this->totpSecret;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if (!$this->totpSecret) {
            return null;
        }

        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): static
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function getSecureLinkProperties(): array
    {
        return ['id' => $this->getId(), 'email' => $this->getEmail()];
    }

    public function getSecureLinkIdentifier(): string
    {
        return $this->getUserIdentifier();
    }
}
