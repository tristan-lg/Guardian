<?php

namespace App\Service;

use App\Component\Discord\Embed;
use App\Component\Discord\EmbedAuthor;
use App\Entity\Analysis;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DiscordApiService $discordApiService,
    ) {
    }

    public function sendAnalysisDoneNotification(Analysis $analysis): void
    {
        //Build discord notification
        $embed = Embed::create()
            ->setTitle('Analyse du projet ' . $analysis->getProject()->getName())
            ->setDescription('TODO')
            ->setAuthor(
                EmbedAuthor::create()
                ->setName('Auteur')
                ->setUrl($analysis->getProject()->getGitUrl())
            )
            ;

        $this->sendDiscordNotification($embed);

        //Build email notification
        // TODO - Send email notification
    }

    /**
     * @param  Embed|Embed[]  $embeds
     */
    private function sendDiscordNotification(Embed|array $embeds): void
    {
        $embeds = is_array($embeds) ? $embeds : [$embeds];

        $channels = $this->em->getRepository(NotificationChannel::class)->findBy(['type' => NotificationType::DISCORD]);
        foreach ($channels as $discordChannel) {
            $client = $this->discordApiService->getClient($discordChannel->getValue());
            $client->sendMessage($embeds);
        }
    }

}
