<?php

namespace App\Entity\DTO;

readonly class EndOfLifeCycleDto
{
    public function __construct(
        public ?string $releaseDate,
        public ?string $eol,
        public ?string $latest,
        public ?string $latestReleaseDate,
        public ?bool $lts,
        public ?string $support
    ) {}

    public function toArray(): array
    {
        return [
            'releaseDate' => $this->releaseDate,
            'eol' => $this->eol,
            'latest' => $this->latest,
            'latestReleaseDate' => $this->latestReleaseDate,
            'lts' => $this->lts,
            'support' => $this->support,
        ];
    }

    public function isExpired(): bool
    {
        return null !== $this->eol && strtotime($this->eol) < time();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['releaseDate'] ?? null,
            $data['eol'] ?? null,
            $data['latest'] ?? null,
            $data['latestReleaseDate'] ?? null,
            $data['lts'] ?? null,
            $data['support'] ?? null
        );
    }
}
