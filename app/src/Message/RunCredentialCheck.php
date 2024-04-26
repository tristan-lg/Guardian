<?php

namespace App\Message;

/**
 * Run an analysis for the given project.
 */
class RunCredentialCheck
{
    public function __construct(
        private string $credentialId
    ) {}

    public function getCredentialId(): string
    {
        return $this->credentialId;
    }
}
