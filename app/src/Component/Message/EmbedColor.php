<?php

namespace App\Component\Message;

enum EmbedColor: string
{
    case PRIMARY = '#0d6efd';
    case SECONDARY = '#6c757d';
    case SUCCESS = '#198754';
    case DANGER = '#dc3545';
    case WARNING = '#ffc107';
    case INFO = '#0dcaf0';

    public function getDecimal(): int
    {
        return (int) hexdec($this->value);
    }

    public function getHex(): string
    {
        return $this->value;
    }
}
