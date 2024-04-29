<?php

namespace App\Entity\DTO;

readonly class PlatformDTO
{
    public function __construct(
        public ?string $php,
        public ?EndOfLifeCycleDto $phpInfos,
        public ?string $symfony,
        public ?EndOfLifeCycleDto $symfonyInfos
    ) {}

    public function toArray(): array
    {
        return [
            'php' => $this->php,
            'phpInfos' => $this->phpInfos,
            'symfony' => $this->symfony,
            'symfonyInfos' => $this->symfonyInfos,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['php'] ?? null,
            array_key_exists('phpInfos', $data) ? EndOfLifeCycleDto::fromArray($data['phpInfos']) : null,
            $data['symfony'] ?? null,
            array_key_exists('symfonyInfos', $data) ? EndOfLifeCycleDto::fromArray($data['symfonyInfos']) : null,
        );
    }
}
