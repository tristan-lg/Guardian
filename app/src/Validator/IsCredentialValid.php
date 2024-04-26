<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class IsCredentialValid extends Constraint
{
    public string $credentialCreationError = 'L\'identifiant de connexion n\'a pas pu être créé.';
    public string $message = 'L\'identifiant de connexion "{{ value }}" est invalide pour "{{ host }}".';
    public string $messageMissingScope = 'L\'identifiant de connexion "{{ value }}" ne possède pas le scope "{{ scope }}".';
    public string $messageExpired = 'L\'identifiant de connexion "{{ value }}" a expiré.';
}
