<?php

namespace App\Exception;

use App\Enum\NotificationType;
use Exception;

class UnsupportedApiException extends Exception
{
    public function __construct(NotificationType $type)
    {
        parent::__construct('No API is supported for this notification channel type : ' . $type->value, 400);
    }
}
