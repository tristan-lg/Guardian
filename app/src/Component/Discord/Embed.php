<?php

namespace App\Component\Discord;

use DateTimeInterface;

class Embed
{
    public const int MAX_FIELDS = 25;

    private string $title;
    private ?string $description = null;
    private ?EmbedColor $color = null;
    private ?EmbedAuthor $author = null;
    private ?DateTimeInterface $timestamp = null;

    /**
     * @var EmbedField[]
     */
    private array $fields = [];

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setAuthor(?EmbedAuthor $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function setTimestamp(?DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function setColor(?EmbedColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function addField(EmbedField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color?->getDecimal() ?? null,
            'author' => $this->author?->toArray() ?? null,
            'timestamp' => $this->timestamp?->format('c') ?? null,
            'fields' => array_map(fn (EmbedField $field) => $field->toArray(), $this->fields),
        ]);
    }

    public static function create(): self
    {
        return new self();
    }
}
