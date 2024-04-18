<?php

namespace App\Message;

class RunAnalysis
{
    public function __construct(
        private string $projectId
    ) {}

    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
