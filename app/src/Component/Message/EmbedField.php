<?php

namespace App\Component\Message;

class EmbedField
{
    private string $name;
    private string $value;
    private bool $inline = false;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setInline(bool $inline = true): self
    {
        $this->inline = $inline;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'value' => $this->value,
            'inline' => $this->inline,
        ]);
    }

    public static function create(): self
    {
        return new self();
    }
}
