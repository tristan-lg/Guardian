<?php

namespace App\Validator;

use App\Entity\User;
use App\Service\Security\TwoFactorService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class IsTwoFactorCodeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService
    ) {}

    /**
     * @param null|string $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsTwoFactorCodeValid) {
            throw new UnexpectedTypeException($constraint, IsTwoFactorCodeValid::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        /** @var FormInterface $root */
        $root = $this->context->getRoot();
        $user = $root->getData();
        if (!$user instanceof User) {
            throw new UnexpectedValueException($user, User::class);
        }

        if (!$this->twoFactorService->isTwoFactorCodeValid($user, $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
