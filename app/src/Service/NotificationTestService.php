<?php

namespace App\Service;

use App\Component\Discord\Embed;
use App\Component\Discord\EmbedColor;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;

class NotificationTestService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DiscordApiService $discordApiService,
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Test the notification channel.
     * Updates the working status according to the test result.
     *
     * @param bool $sendTestNotification If true, a real notification is sent
     *
     * @return bool True if the notification was sent successfully
     */
    public function performNotificationChannelTest(NotificationChannel $channel, bool $sendTestNotification = false): bool
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
                NotificationType::DISCORD => $this->notificationService->sendDiscordNotificationToChannel($channel, Embed::create()
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
        $client = $this->discordApiService->getClient($channel->getValue());

        if (!$client->checkCredentials()) {
            $channel->setWorking(false);
            $this->em->flush();

            return false;
        }

        return true;
    }
}
