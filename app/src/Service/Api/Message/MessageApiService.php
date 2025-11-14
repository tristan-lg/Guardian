<?php

namespace App\Service\Api\Message;

use App\Component\Client\Message\DiscordApiClient;
use App\Component\Client\Message\MattermostApiClient;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use App\Exception\UnsupportedApiException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MessageApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {}

    public function getClientByChannel(NotificationChannel $channel): DiscordApiClient
    {
        return $this->getClient($channel->getValue(), $channel->getType());
    }

    public function getClient(string $webhook, NotificationType $type): DiscordApiClient
    {
        return match ($type) {
            NotificationType::Discord => DiscordApiClient::createClient($this->client, $this->logger, $webhook),
            NotificationType::Mattermost => MattermostApiClient::createClient($this->client, $this->logger, $webhook),

            // Future types can be added here
            default => throw new UnsupportedApiException($type)
        };
    }
}
