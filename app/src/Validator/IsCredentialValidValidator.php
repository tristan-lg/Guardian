<?php

namespace App\Validator;

use App\Entity\Credential;
use App\Service\GitlabApiService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsCredentialValidValidator extends ConstraintValidator
{

    public function __construct(
        private readonly GitlabApiService $gitlabApiService
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var IsCredentialValid $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        //Ensure credentials are valid
        $credentials = $this->context->getRoot()->getData();
        if (!$credentials instanceof Credential) {
            $this->context->buildViolation($constraint->credentialCreationError)
                ->addViolation()
            ;
            return;
        }

        $client = $this->gitlabApiService->getClient($credentials);
        if (!$client->check()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ host }}', $credentials->getDomain())
                ->addViolation()
            ;
        }
    }
}
