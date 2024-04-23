<?php

namespace App\Validator;

use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Service\DiscordApiService;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsWebhookValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly DiscordApiService $discordApiService
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

        if ($channel->getType() !== NotificationType::DISCORD) {
            return;
        }

        $client = $this->discordApiService->getClient($value);
        if (!$client->checkCredentials()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
