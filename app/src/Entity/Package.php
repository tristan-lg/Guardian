<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $requiredVersion = null;

    #[ORM\Column(length: 16)]
    private string $installedVersion;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $availablePatch = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cve = null;

    #[ORM\ManyToOne(inversedBy: 'packages')]
    #[ORM\JoinColumn(nullable: false)]
    private Analysis $analysis;

    #[ORM\Column]
    private bool $subDependency;

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

    public function getRequiredVersion(): ?string
    {
        return $this->requiredVersion;
    }

    public function setRequiredVersion(?string $requiredVersion): static
    {
        $this->requiredVersion = $requiredVersion;

        return $this;
    }

    public function getInstalledVersion(): string
    {
        return $this->installedVersion;
    }

    public function setInstalledVersion(string $installedVersion): static
    {
        $this->installedVersion = $installedVersion;

        return $this;
    }

    public function getAvailablePatch(): ?string
    {
        return $this->availablePatch;
    }

    public function setAvailablePatch(?string $availablePatch): static
    {
        $this->availablePatch = $availablePatch;

        return $this;
    }

    public function getCve(): ?string
    {
        return $this->cve;
    }

    public function setCve(?string $cve): static
    {
        $this->cve = $cve;

        return $this;
    }

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function setAnalysis(Analysis $analysis): static
    {
        $this->analysis = $analysis;

        return $this;
    }

    public function isSubDependency(): bool
    {
        return $this->subDependency;
    }

    public function setSubDependency(bool $subDependency): static
    {
        $this->subDependency = $subDependency;

        return $this;
    }
}
