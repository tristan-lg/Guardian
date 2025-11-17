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

    public function toString(): string
    {
        return $this->name;
    }

    public function getEmoji(): string
    {
        return match ($this) {
            self::A => '🟢',
            self::B => '🟡',
            self::C => '🟠',
            self::D => '🔴',
            self::E => '🔴',
        };
    }

    public function getPriority(): Priority
    {
        return match ($this) {
            self::A, self::B => Priority::Standard,
            self::C => Priority::Important,
            self::D, self::E => Priority::Urgent
        };
    }
}
