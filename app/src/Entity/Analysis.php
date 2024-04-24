<?php

namespace App\Entity;

use App\Entity\Interface\NameableEntityInterface;
use App\Enum\Grade;
use App\Repository\AnalysisRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: AnalysisRepository::class)]
class Analysis implements NameableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\ManyToOne(inversedBy: 'analyses')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\Column]
    private DateTimeImmutable $runAt;

    #[ORM\Column(length: 8)]
    private string $grade = '?';

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(targetEntity: Package::class, mappedBy: 'analysis', cascade: ['persist', 'remove'])]
    private Collection $packages;

    #[ORM\Column]
    private DateTimeImmutable $endAt;

    #[ORM\Column]
    private int $cveCount;

    /**
     * @var Collection<int, Advisory>
     */
    #[ORM\OneToMany(targetEntity: Advisory::class, mappedBy: 'analysis', cascade: ['persist', 'remove'])]
    private Collection $advisories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $advisoryHash = null;

    public function __construct()
    {
        $this->runAt = new DateTimeImmutable();
        $this->packages = new ArrayCollection();
        $this->advisories = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getRunAt(): DateTimeImmutable
    {
        return $this->runAt;
    }

    public function setRunAt(DateTimeImmutable $runAt): static
    {
        $this->runAt = $runAt;

        return $this;
    }

    public function getGrade(): string
    {
        return $this->grade;
    }

    public function getGradeEnum(): Grade
    {
        return Grade::fromString($this->grade);
    }

    public function setGrade(string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setAnalysis($this);
        }

        return $this;
    }

    public function getEndAt(): DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getDurationInSeconds(): int
    {
        return $this->endAt->getTimestamp() - $this->runAt->getTimestamp();
    }

    public function getPackagesCount(): int
    {
        return $this->packages->count();
    }

    public static function getSingular(): string
    {
        return 'Analyse';
    }

    public static function getPlural(): string
    {
        return 'Analyses';
    }

    public function getCveCount(): int
    {
        return $this->cveCount;
    }

    public function setCveCount(int $cveCount): static
    {
        $this->cveCount = $cveCount;

        return $this;
    }

    /**
     * @return Collection<int, Advisory>
     */
    public function getAdvisories(): Collection
    {
        return $this->advisories;
    }

    public function addAdvisory(Advisory $advisory): static
    {
        if (!$this->advisories->contains($advisory)) {
            $this->advisories->add($advisory);
            $advisory->setAnalysis($this);
        }

        return $this;
    }

    public function removeAdvisory(Advisory $advisory): static
    {
        $this->advisories->removeElement($advisory);

        return $this;
    }

    public function getAdvisoriesOrdered(): array
    {
        return $this->advisories->matching(
            Criteria::create()
                ->orderBy(['severity' => Order::Ascending])
        )->toArray();
    }

    public function getAdvisoryHash(): ?string
    {
        return $this->advisoryHash;
    }

    public function setAdvisoryHash(?string $advisoryHash): static
    {
        $this->advisoryHash = $advisoryHash;

        return $this;
    }
}
