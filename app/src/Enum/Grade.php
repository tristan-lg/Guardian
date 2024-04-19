<?php

namespace App\Enum;

use InvalidArgumentException;

enum Grade: int
{
    case A = 0;
    case B = 1;
    case C = 2;
    case D = 3;
    case E = 4;

    public static function fromString(string $grade): self
    {
        return match (strtoupper($grade)) {
            'A' => self::A,
            'B' => self::B,
            'C' => self::C,
            'D' => self::D,
            'E' => self::E,
            default => throw new InvalidArgumentException('Invalid grade'),
        };
    }
}
