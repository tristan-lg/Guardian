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
        private readonly ?Analysis $previousAnalysis
    ) {}

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function getPreviousAnalysis(): ?Analysis
    {
        return $this->previousAnalysis;
    }

    public function getNewGrade(): string
    {
        return $this->getAnalysis()->getGrade();
    }

    public function hasGradeChange(): bool
    {
        return $this->getAnalysis()->getGrade() !== $this->getPreviousAnalysis()?->getGrade();
    }

    public function hasAdvisoriesHashChange(): bool
    {
        return $this->getAnalysis()->getAdvisoryHash() !== $this->getPreviousAnalysis()?->getAdvisoryHash();
    }
}
