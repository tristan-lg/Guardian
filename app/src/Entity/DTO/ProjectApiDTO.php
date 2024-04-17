<?php

namespace App\Entity\DTO;

readonly class ProjectApiDTO
{
    public function __construct(
        public int $id,
        public string $name
    ) {}
}
