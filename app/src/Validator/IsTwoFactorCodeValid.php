<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class IsTwoFactorCodeValid extends Constraint
{
    public string $message = 'Le code renseigné est invalide';
}
