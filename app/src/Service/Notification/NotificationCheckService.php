<?php

namespace App\Service\Notification;

use App\Component\Discord\Embed;
use App\Component\Discord\EmbedColor;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Service\Api\Message\DiscordApiService;
use App\Service\Api\Message\MattermostApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationCheckService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DiscordApiService $discordApiService,
        private readonly MattermostApiService $mattermostApiService,
        private readonly NotificationService $notificationService,
        private readonly ValidatorInterface $validator
    ) {}

    public function isNotificationChannelValid(NotificationChannel $channel): bool
    {
        return match ($channel->getType()) {
            NotificationType::Discord => $this->checkDiscordWebbhook($channel),
            NotificationType::Email => $this->checkEmailChannel($channel), // Email channels are always considered valid
            NotificationType::Mattermost => $this->checkMattermostWebhook($channel), //Impossible to check Mattermost webhook validity
            default => false,
        };
    }

    /**
     * Test the notification channel.
     * Updates the working status according to the test result.
     *
     * @param bool $sendTestNotification    If true, a real notification is sent
     *
     * @return bool True if the notification was sent successfully
     */
    public function performNotificationChannelTest(NotificationChannel $channel, bool $sendTestNotification = false): bool
    {
        $status = $this->isNotificationChannelValid($channel);

        // Update channel status
        $channel->setWorking($status);
        $this->em->flush();

        if (!$status) {
            return false;
        }

        // Second step - Send a test notification
        if ($sendTestNotification) {
            return match ($channel->getType()) {
                NotificationType::Discord => $this->notificationService->sendDiscordNotificationToChannel($channel, Embed::create()
                    ->setTitle('Test de notification')
                    ->setDescription('La configuration de notification est correcte')
                    ->setColor(EmbedColor::SUCCESS)
                ),
                NotificationType::Mattermost => $this->notificationService->sendDiscordNotificationToChannel($channel, Embed::create()
                    ->setTitle('Test de notification')
                    ->setDescription('La configuration de notification est correcte')
                    ->setColor(EmbedColor::SUCCESS)
                ),
                default => false,
            };
        }

        return true;
    }

    private function checkDiscordWebbhook(NotificationChannel $channel): bool
    {
        return $this->discordApiService->getClient($channel->getValue())->checkCredentials();
    }

    private function checkMattermostWebhook(NotificationChannel $channel): bool
    {
        return $this->mattermostApiService->getClient($channel->getValue())->checkCredentials();
    }

    private function checkEmailChannel(NotificationChannel $channel): bool
    {
        $errors = $this->validator->validate($channel->getValue(), [
            new NotBlank(),
            new Email(mode: Email::VALIDATION_MODE_HTML5),
            new Length(max: 180),
        ]);

        return count($errors) === 0;
    }
}
