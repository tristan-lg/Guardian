<?php

namespace App\Twig\Components;

use App\Entity\NotificationChannel;
use App\Form\NotificationChannelType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class NotificationChannelForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public NotificationChannel $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NotificationChannelType::class, $this->initialFormData);
    }
}
