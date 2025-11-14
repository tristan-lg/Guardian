<?php

namespace App\Component\Client\Message;

use App\Component\Message\Embed;
use App\Component\Message\EmbedField;
use App\Enum\Priority;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MattermostApiClient implements MessageClient
{
    protected function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $webhook
    ) {}

    public function checkCredentials(): bool
    {
        //For now, we assume the webhook is always valid (no way to check it with Mattermost API)
        return true;
    }

    /**
     * @param Embed[] $embeds
     */
    public function sendMessage(array $embeds, Priority $priority): void
    {
        $this->post('', [
            'username' => 'Guardian',
            'priority' => [
                'priority' => 'urgent|important|standard',
                'request_ack' => false,
            ],
            'attachments' => array_map(fn (Embed $embed) => $this->embedToArray($embed), $embeds)
        ]);
    }

    private function embedToArray(Embed $embed): array
    {
        return array_filter([
            'title' => $embed->getTitle(),
            'color' => $embed->getColor()?->getHex(),
            'text' => $embed->getDescription(),

            'author_name' => $embed->getAuthor()?->getName(),
            'author_icon' => $embed->getAuthor()?->getIconUrl(),

            'fields' => array_map(fn (EmbedField $field) => [
                'title' => $field->getName(),
                'value' => $field->getValue(),
                'short' => $field->isInline(),
            ], $embed->getFields()),

            'footer' => $embed->getTimestamp()
                ? sprintf('Vérification effectuée le %s', $embed->getTimestamp()->format('d/m/Y à H:i'))
                : null,
        ]);
    }

    public static function createClient(
        HttpClientInterface $client,
        LoggerInterface $logger,
        string $webhook,
    ): MattermostApiClient {
        return new self($client, $webhook);
    }

    private function post(string $endpoint, array $jsonBody = []): ResponseInterface
    {
        return $this->client->request('POST', $this->webhook . $endpoint, [
            'json' => $jsonBody,
        ]);
    }
}
