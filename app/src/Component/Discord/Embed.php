<?php

namespace App\Component\Discord;

class Embed
{
    private string $title;
    private string $description;
    private ?int $color = null;
    private ?EmbedAuthor $author = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?int
    {
        return $this->color;
    }

    public function setColor(?int $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getAuthor(): ?EmbedAuthor
    {
        return $this->author;
    }

    public function setAuthor(?EmbedAuthor $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color,
            'author' => $this->author?->toArray() ?? null,
        ]);
    }

    public static function create(): self
    {
        return new self();
    }
}
