<?php

namespace App\Entity;

use App\Entity\Interface\NameableEntityInterface;
use App\Enum\Severity;
use App\Repository\AdvisoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: AdvisoryRepository::class)]
class Advisory implements NameableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $advisoryId;

    #[ORM\ManyToOne(inversedBy: 'advisories')]
    #[ORM\JoinColumn(nullable: false)]
    private Package $package;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 1024)]
    private string $link;

    #[ORM\Column(length: 255)]
    private string $cve;

    #[ORM\Column(length: 255)]
    private string $affectedVersions;

    #[ORM\Column(length: 255)]
    private string $source;

    #[ORM\Column]
    private DateTimeImmutable $reportedAt;

    #[ORM\Column(length: 32)]
    private string $severity;

    public function getId(): string
    {
        return $this->id;
    }

    public function getAdvisoryId(): string
    {
        return $this->advisoryId;
    }

    public function setAdvisoryId(string $advisoryId): static
    {
        $this->advisoryId = $advisoryId;

        return $this;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): static
    {
        $this->package = $package;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getCve(): string
    {
        return $this->cve;
    }

    public function setCve(string $cve): static
    {
        $this->cve = $cve;

        return $this;
    }

    public function getAffectedVersions(): string
    {
        return $this->affectedVersions;
    }

    public function setAffectedVersions(string $affectedVersions): static
    {
        $this->affectedVersions = $affectedVersions;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getReportedAt(): DateTimeImmutable
    {
        return $this->reportedAt;
    }

    public function setReportedAt(DateTimeImmutable $reportedAt): static
    {
        $this->reportedAt = $reportedAt;

        return $this;
    }

    public function getSeverityEnum(): Severity
    {
        return Severity::from($this->getSeverity());
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;

        return $this;
    }

    public static function fromPackagistApi(array $advisory): Advisory
    {
        $severity = Severity::tryFrom($advisory['severity']) ?? Severity::UNKNOWN;

        return (new Advisory())
            ->setAdvisoryId($advisory['advisoryId'])
            ->setTitle($advisory['title'])
            ->setLink($advisory['link'])
            ->setCve($advisory['cve'] ?? '')
            ->setAffectedVersions($advisory['affectedVersions'])
            ->setSource($advisory['source'] ?? 'Unknown')
            ->setReportedAt(new DateTimeImmutable($advisory['reportedAt']))
            ->setSeverity($severity->value)
        ;
    }

    public static function getSingular(): string
    {
        return 'Vulnérabilité';
    }

    public static function getPlural(): string
    {
        return 'Vulnérabilités';
    }
}
