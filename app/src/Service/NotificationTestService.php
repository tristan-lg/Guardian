<?php

namespace App\Service;

use App\Component\Discord\Embed;
use App\Component\Discord\EmbedColor;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationTestService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DiscordApiService $discordApiService,
        private readonly NotificationService $notificationService,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Test the notification channel.
     *
     * @param bool $sendTestNotification    If true, a real notification is sent
     * @param bool $updateStatus            If true, the channel status is updated in the database
     *
     * @return bool True if the notification was sent successfully
     */
    public function performNotificationChannelTest(
        NotificationChannel $channel,
        bool $sendTestNotification = false,
        bool $updateStatus = true
    ): bool
    {
        // First step - Check credentials for the channel
        $status = match ($channel->getType()) {
            NotificationType::DISCORD => $this->checkDiscordWebbhook($channel),
            default => false,
        };

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
                default => false,
            };
        }

        return true;
    }

    private function checkDiscordWebbhook(NotificationChannel $channel): bool
    {
        return $this->discordApiService->getClient($channel->getValue())->checkCredentials();
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
