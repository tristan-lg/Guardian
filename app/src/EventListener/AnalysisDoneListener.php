<?php

namespace App\EventListener;

use App\Enum\Grade;
use App\Event\AnalysisDoneEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class AnalysisDoneListener
{
    public function __construct(
        private NotificationService $notificationService,
    ) {
    }

    #[AsEventListener]
    public function onAnalysisDoneEvent(AnalysisDoneEvent $event): void
    {
        if (!$event->hasGradeChanged()) {
            return;
        }

        if (Grade::fromString($event->getNewGrade()) === Grade::A) {
            return;
        }

        //Send notification for each channel
        $this->notificationService->sendAnalysisDoneNotification(
            $event->getAnalysis()
        );
    }

}
