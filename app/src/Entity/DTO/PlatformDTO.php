<?php

namespace App\Entity\DTO;

readonly class PlatformDTO
{
    public function __construct(
        public ?string $php,
        public ?EndOfLifeCycleDto $phpInfos,
        public ?string $symfony,
        public ?EndOfLifeCycleDto $symfonyInfos,
        public ?string $drupal,
        public ?EndOfLifeCycleDto $drupalInfos,
    ) {}

    public function isPhpExpired(): bool
    {
        return $this->phpInfos?->isExpired() ?? false;
    }

    public function isSymfonyExpired(): bool
    {
        return $this->symfonyInfos?->isExpired() ?? false;
    }

    public function isDrupalExpired(): bool
    {
        return $this->drupalInfos?->isExpired() ?? false;
    }

    public function toArray(): array
    {
        return [
            'php' => $this->php,
            'phpInfos' => $this->phpInfos?->toArray() ?? null,
            'symfony' => $this->symfony,
            'symfonyInfos' => $this->symfonyInfos?->toArray() ?? null,
            'drupal' => $this->drupal,
            'drupalInfos' => $this->drupalInfos?->toArray() ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['php'] ?? null,
            (array_key_exists('phpInfos', $data) && $data['phpInfos']) ? EndOfLifeCycleDto::fromArray($data['phpInfos']) : null,
            $data['symfony'] ?? null,
            (array_key_exists('symfonyInfos', $data) && $data['symfonyInfos']) ? EndOfLifeCycleDto::fromArray($data['symfonyInfos']) : null,
            $data['drupal'] ?? null,
            (array_key_exists('drupalInfos', $data) && $data['drupalInfos']) ? EndOfLifeCycleDto::fromArray($data['drupalInfos']) : null
        );
    }
}
