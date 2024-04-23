<?php

namespace App\Enum;

enum NotificationType: string
{
    case DISCORD = 'DISCORD';
    case EMAIL = 'EMAIL';
}
