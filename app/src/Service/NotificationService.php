<?php

namespace App\Service;

use App\Component\Discord\Embed;
use App\Component\Discord\EmbedAuthor;
use App\Component\Discord\EmbedColor;
use App\Component\Discord\EmbedField;
use App\Entity\Analysis;
use App\Entity\NotificationChannel;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DiscordApiService $discordApiService,
    ) {}

    public function sendAnalysisDoneNotification(Analysis $analysis): void
    {
        // Build discord notification
        $embed = Embed::create()
            ->setAuthor(
                EmbedAuthor::create()
                    ->setName(sprintf('%s [%s] %s',
                        $analysis->getGradeEnum()->getEmoji(),
                        ucfirst($analysis->getProject()->getNamespace() ?? ''),
                        $analysis->getProject()->getName()
                    ))
                    ->setUrl($analysis->getProject()->getGitUrl())
            )
            ->setColor(EmbedColor::DANGER)
            ->setTitle(sprintf('Vulnérabilités découvertes'))
            ->setTimestamp($analysis->getRunAt())

            ->addField(EmbedField::create()
                ->setName('Grade')
                ->setValue(sprintf('%s', $analysis->getGrade()))
                ->setInline()
            )
            ->addField(EmbedField::create()
                ->setName('Vulnérabilités')
                ->setValue(sprintf('%d', $analysis->getCveCount()))
                ->setInline()
            )
        ;

        // Add fields for each CVE (prevent add too many fields)
        $advisories = $analysis->getAdvisoriesOrdered();
        $remainingFieldsCount = Embed::MAX_FIELDS - count($embed->getFields());
        $advisoriesToShow = count($advisories) > $remainingFieldsCount ? ($remainingFieldsCount - 1) : count($advisories);
        foreach (array_slice($advisories, 0, $advisoriesToShow) as $advisory) {
            $embed->addField(EmbedField::create()
                ->setName(sprintf('%s [%s] %s',
                    $advisory->getSeverityEnum()->emoji(),
                    $advisory->getSeverityEnum()->label(),
                    $advisory->getPackage()->getName()
                ))
                ->setValue(sprintf('[%s](%s)' . PHP_EOL . 'depuis le %s',
                    $advisory->getTitle(),
                    $advisory->getLink(),
                    $advisory->getReportedAt()->format('d/m/Y')
                ))
            );
        }

        // If there is more advisories that available fields, add a field to indicate it
        if (count($advisories) !== $advisoriesToShow) {
            $embed->addField(EmbedField::create()
                ->setName('...')
                ->setValue(sprintf('+ %d vulnérabilités supplémentaires', count($advisories) - 24))
            );
        }

        $this->sendDiscordNotification($embed);

        // Build email notification
        // TODO - Send email notification
    }

    /**
     * @param Embed|Embed[] $embeds
     */
    private function sendDiscordNotification(array|Embed $embeds): void
    {
        $embeds = is_array($embeds) ? $embeds : [$embeds];

        $channels = $this->em->getRepository(NotificationChannel::class)->findBy(['type' => NotificationType::DISCORD]);
        foreach ($channels as $discordChannel) {
            $client = $this->discordApiService->getClient($discordChannel->getValue());
            $client->sendMessage($embeds);
        }
    }
}
