<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class IsProjectUnique extends Constraint
{
    public string $useInProjectMessage = 'Cette contrainte ne peut être utilisée que dans un projet';
    public string $message = 'Ce projet existe déjà pour la branche {{ branch }}';
}
