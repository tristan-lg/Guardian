<?php

namespace App\Security\Voter;

use App\Entity\Credential;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

use function PHPUnit\Framework\matches;

class CredentialVoter extends Voter
{
    public const string DELETE = 'DELETE_CREDENTIAL';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE])
            && $subject instanceof Credential;
    }

    /**
     * @param  Credential  $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match($attribute) {
            self::DELETE => $subject->getProjects()->isEmpty(),
            default => false,
        };
    }
}
