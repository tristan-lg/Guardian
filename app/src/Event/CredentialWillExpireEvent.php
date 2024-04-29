<?php

namespace App\Event;

use App\Entity\Credential;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

final class CredentialWillExpireEvent extends Event
{
    /**
     * This event is dispatched each time a credential expiration check is done.
     */
    public function __construct(
        private readonly Credential $credential
    ) {}

    public function getDaysBeforeExpiration(): int
    {
        if ($this->credential->isExpired()) {
            return 0;
        }

        if (!$this->credential->getExpireAt()) {
            return 365;
        }

        return $this->credential->getExpireAt()->diff(new DateTimeImmutable())->days ?: 0;
    }

    public function getCredential(): Credential
    {
        return $this->credential;
    }
}
