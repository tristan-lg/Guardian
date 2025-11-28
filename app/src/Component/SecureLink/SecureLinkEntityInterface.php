<?php

namespace App\Component\SecureLink;

interface SecureLinkEntityInterface
{
    public function getSecureLinkProperties(): array;

    public function getSecureLinkIdentifier(): string;
}
