<?php

namespace App\Enum;

enum NotificationType: string
{
    case Discord = 'DISCORD';
    case Mattermost = 'MATTERMOST';
    case Email = 'EMAIL';

    public function getTokenLabel(): string
    {
        return match ($this) {
            self::Discord => 'URL de Webhook Discord',
            self::Mattermost => 'URL de Webhook Mattermost',
            self::Email => 'Adresse email',
        };
    }
}
