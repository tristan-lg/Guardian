<?php

namespace App\Event;

use App\Entity\Credential;
use Symfony\Contracts\EventDispatcher\Event;

final class CredentialJustExpiredEvent extends Event
{
    /**
     * This event is dispatched each time a credential has expire after a credentials analysis.
     */
    public function __construct(
        private readonly Credential $credential
    ) {}

    public function getCredential(): Credential
    {
        return $this->credential;
    }
}
