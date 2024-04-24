<?php

namespace App\Enum;

enum Severity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    public function emoji(): string
    {
        return match ($this) {
            self::LOW => '🔵',
            self::MEDIUM => '🟠',
            self::HIGH => '🔴',
            self::CRITICAL => '🚨',
            self::UNKNOWN => '❓',
        };
    }
}
