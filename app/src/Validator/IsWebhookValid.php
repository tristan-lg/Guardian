<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class IsWebhookValid extends Constraint
{
    public string $message = 'L\'URL webhook est invalide';
    public string $creationError = 'Le canal de communication n\'a pas pu être créé.';
}
