<?php

namespace App\Validator;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Service\Api\Message\MessageApiService;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsWebhookValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly MessageApiService $discordApiService
    ) {}

    /**
     * @param IsWebhookValid $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (null === $value || '' === $value || !is_string($value)) {
            return;
        }

        // Ensure credentials are valid
        /** @var Form $form */
        $form = $this->context->getRoot();
        $channel = $form->getData();
        if (!$channel instanceof NotificationChannel) {
            $this->context
                ->buildViolation($constraint->creationError)
                ->addViolation()
            ;

            return;
        }

        if (NotificationType::Discord !== $channel->getType()
            && NotificationType::Mattermost !== $channel->getType()
        ) {
            return;
        }

        $client = $this->discordApiService->getClientByChannel($channel);
        if (!$client->checkCredentials()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
