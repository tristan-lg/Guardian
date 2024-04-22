<?php

namespace App\Event;

use App\Entity\Analysis;
use Symfony\Contracts\EventDispatcher\Event;

final class AnalysisDoneEvent extends Event
{
    /**
     * This event is dispatched each time an analysis is done.
     */
    public function __construct(
        private readonly Analysis $analysis,
        private readonly ?string $previousGrade
    ) {}

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function getPreviousGrade(): ?string
    {
        return $this->previousGrade;
    }

    public function getNewGrade(): string
    {
        return $this->getAnalysis()->getGrade();
    }

    public function hasGradeChanged(): bool
    {
        return $this->getPreviousGrade() !== $this->getNewGrade();
    }
}
