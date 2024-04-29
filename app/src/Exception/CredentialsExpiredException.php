<?php

namespace App\Exception;

use Exception;
use Throwable;

class CredentialsExpiredException extends Exception
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Les identifiants sont expirés ou invalides', 400, $previous);
    }
}
