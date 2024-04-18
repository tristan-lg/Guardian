<?php

namespace App\Enum;

enum Severity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
    case UNKNOWN = 'unknown';
}
