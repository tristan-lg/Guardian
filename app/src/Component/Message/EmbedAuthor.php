<?php

namespace App\Component\Message;

class EmbedAuthor
{
    private string $name;
    private ?string $url = null;
    private ?string $iconUrl = null;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setIconUrl(?string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'url' => $this->url,
            'iconUrl' => $this->iconUrl,
        ]);
    }

    public static function create(): self
    {
        return new self();
    }
}
