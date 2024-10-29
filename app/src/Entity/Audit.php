<?php

namespace App\Entity;

use App\Entity\DTO\PlatformDTO;
use App\Repository\AuditRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: AuditRepository::class)]
class Audit
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private File $fileComposerJson;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private File $fileComposerLock;

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    /**
     * @var Collection<int, Analysis>
     */
    #[ORM\OneToMany(targetEntity: Analysis::class, mappedBy: 'audit', cascade: ['remove'])]
    private Collection $analyses;

    public function __construct()
    {
        $this->analyses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFileComposerJson(): File
    {
        return $this->fileComposerJson;
    }

    public function setFileComposerJson(File $fileComposerJson): static
    {
        $this->fileComposerJson = $fileComposerJson;

        return $this;
    }

    public function getFileComposerLock(): File
    {
        return $this->fileComposerLock;
    }

    public function setFileComposerLock(File $fileComposerLock): static
    {
        $this->fileComposerLock = $fileComposerLock;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Analysis>
     */
    public function getAnalyses(): Collection
    {
        return $this->analyses;
    }

    public function addAnalysis(Analysis $analysis): static
    {
        if (!$this->analyses->contains($analysis)) {
            $this->analyses->add($analysis);
            $analysis->setAudit($this);
        }

        return $this;
    }

    public function removeAnalysis(Analysis $analysis): static
    {
        if ($this->analyses->removeElement($analysis)) {
            // set the owning side to null (unless already changed)
            if ($analysis->getAudit() === $this) {
                $analysis->setAudit(null);
            }
        }

        return $this;
    }

    public function getLastGrade(): ?string
    {
        $lastAnalysis = $this->getLastAnalysis();
        if (!$lastAnalysis) {
            return null;
        }

        return $lastAnalysis->getGrade();
    }

    public function getLastVulnerabilitiesCount(): ?int
    {
        $lastAnalysis = $this->getLastAnalysis();
        if (!$lastAnalysis) {
            return null;
        }

        return $lastAnalysis->getCveCount();
    }

    public function getLastAnalysis(): ?Analysis
    {
        return $this->analyses->last() ?: null;
    }

    public function getPlatform(): ?PlatformDTO
    {
        return $this->getLastAnalysis()?->getPlatform();
    }
}
