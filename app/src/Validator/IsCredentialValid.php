<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class IsCredentialValid extends Constraint
{
    public $credentialCreationError = 'The credential could not be created.';
    public $message = 'The access token "{{ value }}" for the host "{{ host }}" is not valid.';
}
