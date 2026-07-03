<?php

namespace App\Enum;

enum Role: string
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_VIEWER = 'ROLE_VIEWER';

    public function getLabel(): string
    {
        return match ($this) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_VIEWER => 'Visualisation uniquement',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ROLE_ADMIN => 'danger',
            self::ROLE_VIEWER => 'primary'
        };
    }
}
