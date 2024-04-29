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
