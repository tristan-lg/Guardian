<?php

namespace App\Entity;

use App\Entity\Interface\NameableEntityInterface;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements NameableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Credential $credential = null;

    #[ORM\Column]
    private ?int $projectId = null;

    #[ORM\Column]
    private array $files = [];

    /**
     * @var Collection<int, Analysis>
     */
    #[ORM\OneToMany(targetEntity: Analysis::class, mappedBy: 'project')]
    private Collection $analyses;

    public function __construct()
    {
        $this->analyses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? $this->getId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCredential(): ?Credential
    {
        return $this->credential;
    }

    public function setCredential(?Credential $credential): static
    {
        $this->credential = $credential;

        return $this;
    }

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function setProjectId(int $projectId): static
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }

    public function getRef(): string
    {
        // TODO - Later enable customization per project

        return 'master';
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
            $analysis->setProject($this);
        }

        return $this;
    }

    public function removeAnalysis(Analysis $analysis): static
    {
        $this->analyses->removeElement($analysis);

        return $this;
    }

    public static function getSingular(): string
    {
        return 'Projet';
    }

    public static function getPlural(): string
    {
        return 'Projets';
    }
}
