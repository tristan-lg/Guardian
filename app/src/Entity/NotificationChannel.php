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

    #[ORM\Column(options: ['default' => true])]
    private bool $working = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

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
        return 'Canal de notification';
    }

    public static function getPlural(): string
    {
        return 'Canaux de notifications';
    }

    public function isWorking(): bool
    {
        return $this->working;
    }

    public function setWorking(bool $working): static
    {
        $this->working = $working;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
