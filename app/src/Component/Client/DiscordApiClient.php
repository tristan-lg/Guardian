<?php

namespace App\Component\Client;

use App\Component\Discord\Embed;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class DiscordApiClient
{
    protected function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $webhook,
        private readonly LoggerInterface $logger,
    ) {}

    public function checkCredentials(): bool
    {
        try {
            return Response::HTTP_OK === $this->get('')->getStatusCode();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @see https://birdie0.github.io/discord-webhooks-guide/structure/embeds.html
     * @see https://discord.com/developers/docs/resources/channel#embed-object
     *
     * @param Embed[] $embeds
     */
    public function sendMessage(array $embeds): void
    {
        $embedsArray = array_map(fn (Embed $embed) => $embed->toArray(), $embeds);
        if (count($embedsArray) > 10) {
            $this->logger->error('DiscordApiClient::sendMessage: too many embeds : ' . count($embedsArray) . ' / 10');
            return;
        }

        foreach ($embedsArray as $key => $embed) {
            if (isset($embed['fields']) && count($embed['fields']) > 25) {
                $this->logger->error('DiscordApiClient::sendMessage: too many fields in embed ' . $key . ' : ' . count($embed['fields']) . ' / 25');
                return;
            }
        }

        $this->post('', [
            'embeds' => $embedsArray,
        ]);
    }

    public static function createClient(
        HttpClientInterface $client,
        LoggerInterface $logger,
        string $webhook,
    ): DiscordApiClient {
        return new self($client, $webhook, $logger);
    }

    private function get(string $endpoint, array $options = []): ResponseInterface
    {
        return $this->client->request('GET', $this->webhook . $endpoint, [
            'query' => $options,
        ]);
    }

    private function post(string $endpoint, array $jsonBody = []): ResponseInterface
    {
        return $this->client->request('POST', $this->webhook . $endpoint, [
            'json' => $jsonBody,
        ]);
    }
}
