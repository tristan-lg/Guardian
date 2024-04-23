<?php

namespace App\Entity;

use App\Entity\Interface\NameableEntityInterface;
use App\Enum\NotificationType;
use App\Repository\NotificationChannelRepository;
use App\Validator\IsWebhookValid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: NotificationChannelRepository::class)]
class NotificationChannel implements NameableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private NotificationType $type;

    #[IsWebhookValid]
    #[ORM\Column(length: 1024)]
    private string $value;

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function setType(NotificationType|string $type): static
    {
        $this->type = is_string($type) ? NotificationType::from($type) : $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public static function getSingular(): string
    {
        return "Canal de notification";
    }

    public static function getPlural(): string
    {
        return "Canaux de notifications";
    }
}
