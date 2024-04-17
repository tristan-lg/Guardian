<?php

namespace App\Validator;

use App\Entity\Credential;
use App\Service\GitlabApiService;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsCredentialValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GitlabApiService $gitlabApiService
    ) {}

    /**
     * @param IsCredentialValid $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (null === $value || '' === $value || !is_string($value)) {
            return;
        }

        // Ensure credentials are valid
        /** @var Form $form */
        $form = $this->context->getRoot();
        $credentials = $form->getData();
        if (!$credentials instanceof Credential) {
            $this->context
                ->buildViolation($constraint->credentialCreationError)
                ->addViolation()
            ;

            return;
        }

        $client = $this->gitlabApiService->getClient($credentials);
        if (!$client->checkCredentials()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ host }}', $credentials->getDomain() ?? '')
                ->addViolation()
            ;
        }
    }
}
