<?php

namespace App\Component\Git;

use DateTime;
use DateTimeInterface;

readonly class TokenData
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $revoked,
        public array $scopes,
        public bool $active,
        public ?DateTimeInterface $expiresAt,
    ) {}

    public static function fromArray(array $jsonData): self
    {
        return new self(
            $jsonData['id'],
            $jsonData['name'],
            $jsonData['revoked'],
            $jsonData['scopes'],
            $jsonData['active'],
            new DateTime($jsonData['expires_at'] ?? null),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return (!$this->expiresAt && $this->expiresAt < new DateTime()) || !$this->active || $this->revoked;
    }
}
