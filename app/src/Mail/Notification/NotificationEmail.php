<?php

namespace App\Mail\Notification;

use App\Component\Message\Embed;
use App\Mail\AbstractMailBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;

class NotificationEmail extends AbstractMailBuilder
{
    public const string EMBED = 'Embed';

    public function getSubject(array $options): string|TranslatableMessage
    {
        $embed = $this->getEmbed($options);

        return $embed->getAuthor()
            ? sprintf('%s : %s', $embed->getTitle(), $embed->getAuthor()->getName())
            : $embed->getTitle();
    }

    public function getHtml(Environment $twig, array $options): string
    {
        return $twig->render('@emails/notification/notification.email.html.twig', [
            'embed' => $this->getEmbed($options),
        ]);
    }

    public function getEmbed(array $options): Embed
    {
        return $options[self::EMBED];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(self::EMBED);
        $resolver->setAllowedTypes(self::EMBED, Embed::class);
    }
}
