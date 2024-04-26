<?php

namespace App\Message;

/**
 * Run an analysis for the given project.
 */
class ClearProjectAnalyses
{
    public function __construct(
        private string $projectId
    ) {}

    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
