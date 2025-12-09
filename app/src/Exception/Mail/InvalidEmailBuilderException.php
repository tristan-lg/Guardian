<?php

namespace App\Exception\Mail;

use Exception;

class InvalidEmailBuilderException extends Exception
{
    public function __construct(string $urlKey)
    {
        parent::__construct(
            sprintf('The email builder \'%s\' does not exists !', $urlKey),
            500
        );
    }
}
