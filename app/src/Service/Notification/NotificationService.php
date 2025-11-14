<?php

namespace App\Service\Notification;

use App\Component\Message\Embed;
use App\Component\Message\EmbedAuthor;
use App\Component\Message\EmbedColor;
use App\Component\Message\EmbedField;
use App\Entity\Analysis;
use App\Entity\Credential;
use App\Entity\NotificationChannel;
use App\Enum\Priority;
use App\Service\Api\Message\MessageApiService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageApiService $messageApiService,
        private readonly LoggerInterface $logger
    ) {}

    public function sendAnalysisDoneNotification(Analysis $analysis): void
    {
        if (!$analysis->getProject()) {
            return;
        }

        // Build notification
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

        $this->sendGlobalNotification($embed, $analysis->getGradeEnum()->getPriority());
    }

    public function sendCredentialWillExpireNotification(Credential $credential): void
    {
        // Build discord notification
        if (!$credential->getExpireAt()) {
            return;
        }

        $embed = Embed::create()
            ->setColor(EmbedColor::WARNING)
            ->setTitle(sprintf('⚠️ L\'identifiant "%s" expire dans %d jours',
                ucfirst($credential->getName() ?? ''),
                $credential->getExpireAt()->diff(new DateTimeImmutable())->days
            ))
        ;

        $this->sendGlobalNotification($embed, Priority::Important);
    }

    public function sendCredentialJustExpiredNotification(Credential $credential): void
    {
        // Build discord notification
        $embed = Embed::create()
            ->setColor(EmbedColor::DANGER)
            ->setTitle(sprintf('🚨 L\'identifiant "%s" a expiré !',
                ucfirst($credential->getName() ?? ''),
            ))
        ;

        $this->sendGlobalNotification($embed, Priority::Important);
    }

    /**
     * Send a notification to the specified channel.
     *
     * @param Embed|Embed[] $embeds
     */
    public function sendNotificationToChannel(NotificationChannel $channel, array|Embed $embeds, Priority $priority = Priority::Standard): bool
    {
        $embeds = is_array($embeds) ? $embeds : [$embeds];

        $client = $this->messageApiService->getClientByChannel($channel);

        try {
            $client->sendMessage($embeds, $priority);
        } catch (Throwable $t) {
            // Log error
            $this->logger->critical('Error while sending notification', [
                'channelType' => $channel->getType()->value,
                'channelId' => $channel->getId(),
                'error' => $t->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param Embed|Embed[] $embeds
     */
    private function sendGlobalNotification(array|Embed $embeds, Priority $priority = Priority::Standard): void
    {
        $channels = $this->em->getRepository(NotificationChannel::class)->findBy(['working' => true]);
        foreach ($channels as $channel) {
            $this->logger->info('Sending notification to channel : ' . $channel->getName(), [
                'channelType' => $channel->getType()->value,
                'channelId' => $channel->getId(),
            ]);

            $this->sendNotificationToChannel($channel, $embeds, $priority);
        }
    }
}
