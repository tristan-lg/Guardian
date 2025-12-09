<?php

namespace App\Mail;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractMailBuilder implements MailBuilderInterface
{
    public function configureOptions(OptionsResolver $resolver): void {}

    public function getFiles(array $options): array
    {
        return [];
    }
}
