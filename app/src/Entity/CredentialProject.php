<?php

namespace App\Entity;

use App\Repository\CredentialProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;

#[ORM\Entity(repositoryClass: CredentialProjectRepository::class)]
class CredentialProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private string $id;

    #[ORM\ManyToOne(inversedBy: 'apiProjects')]
    #[ORM\JoinColumn(nullable: false)]
    private Credential $credential;

    #[ORM\Column]
    private int $gitlabId;

    #[ORM\Column(length: 255)]
    private string $name;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCredential(): Credential
    {
        return $this->credential;
    }

    public function setCredential(Credential $credential): static
    {
        $this->credential = $credential;

        return $this;
    }

    public function getGitlabId(): int
    {
        return $this->gitlabId;
    }

    public function setGitlabId(int $gitlabId): static
    {
        $this->gitlabId = $gitlabId;

        return $this;
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
}
