<?php

namespace App\Component\Client\Message;

use App\Component\Message\Embed;
use App\Enum\Priority;
use App\Mail\Notification\NotificationEmail;
use App\Service\Mail\MailService;

class EmailClient implements MessageClient
{
    protected function __construct(
        private readonly MailService $mailService,
        private readonly string $email
    ) {}

    public function checkCredentials(): bool
    {
        return true;
    }

    /**
     * @param Embed[] $embeds
     */
    public function sendMessage(array $embeds, Priority $priority): void
    {
        foreach ($embeds as $embed) {
            $this->mailService->send($this->email, NotificationEmail::class, [
                NotificationEmail::EMBED => $embed
            ]);
        }
    }

    public static function createClient(
        MailService $mailService,
        string $email,
    ): EmailClient {
        return new self($mailService, $email);
    }
}
