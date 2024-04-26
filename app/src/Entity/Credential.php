<?php

namespace App\Entity;

use App\Entity\Interface\NameableEntityInterface;
use App\Repository\CredentialRepository;
use App\Validator\IsCredentialValid;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: CredentialRepository::class)]
class Credential implements NameableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\Column(length: 255)]
    private ?string $domain = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[IsCredentialValid]
    private ?string $accessToken = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'credential')]
    private Collection $projects;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $expireAt = null;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function __toString(): string
    {
        return ($this->getName() ?? '') . ' (' . ($this->getDomain() ?? '') . ')';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
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

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setCredential($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getCredential() === $this) {
                $project->setCredential(null);
            }
        }

        return $this;
    }

    public static function getSingular(): string
    {
        return 'Identifiant';
    }

    public static function getPlural(): string
    {
        return 'Identifiants';
    }

    public function getExpireAt(): ?DateTimeImmutable
    {
        return $this->expireAt;
    }

    public function setExpireAt(?DateTimeImmutable $expireAt): static
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expireAt && $this->expireAt < new DateTimeImmutable();
    }

    public function isExpiredIn(int $days): bool
    {
        return $this->expireAt && $this->expireAt < (new DateTimeImmutable())->modify('+' . $days . ' days');
    }
}
