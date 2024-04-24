<?php

namespace App\Component\Client;

use App\Component\Discord\Embed;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class DiscordApiClient
{
    protected function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $webhook
    ) {
    }

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
     * @param  Embed[]  $embeds
     */
    public function sendMessage(array $embeds): void
    {
        $this->post('', [
            'embeds' => array_map(fn(Embed $embed) => $embed->toArray(), $embeds),
        ]);
    }

    private function get(string $endpoint, array $options = []): ResponseInterface
    {
        return $this->client->request('GET', $this->webhook . $endpoint, [
            'query' => $options,
        ]);
    }

    private function post(string $endpoint, array $jsonBody = []): ResponseInterface
    {
        try {
            return $this->client->request('POST', $this->webhook . $endpoint, [
                'json' => $jsonBody,
            ]);
        }catch (ClientException $r) {
            dd($r);
        }

    }

    public static function createClient(
        HttpClientInterface $client,
        string $webhook,
    ): DiscordApiClient {
        return new self($client, $webhook);
    }
}
