<?php

namespace App\Enum;

enum Priority: string
{
    case Standard = 'standard';
    case Important = 'important';
    case Urgent = 'urgent';
}
