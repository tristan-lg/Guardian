<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[Attribute]
class MatchPasswordPolicy extends Assert\Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(allowNull: false, message: 'Merci de renseigner un mot de passe'),
            new Assert\Length(min: 12, max: 255, minMessage: 'Votre mot de passe doit contenir {{ limit }} caractères minimum'),
            new PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK),
            new NotCompromisedPassword(),
            new Assert\Regex(pattern: '/[A-Z]+/', message: 'Votre mot de passe doit contenir au moins une majuscule'),
            new Assert\Regex(pattern: '/[0-9]+/', message: 'Votre mot de passe doit contenir au moins un chiffre'),
            new Assert\Regex(pattern: '/[@&$?#!]+/', message: 'Votre mot de passe doit contenir au moins un caractère spécial (@&$?#!)'),
        ];
    }
}
