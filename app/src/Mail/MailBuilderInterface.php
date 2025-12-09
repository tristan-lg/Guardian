<?php

namespace App\Mail;

use App\Exception\Mail\InvalidEmailBuilderException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AutoconfigureTag('app.mail.builders')]
interface MailBuilderInterface
{
    /**
     * Returns the mail subject.
     */
    public function getSubject(array $options): string|TranslatableMessage;

    /**
     * Get HTML associated to email.
     *
     * @throws InvalidEmailBuilderException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function getHtml(Environment $twig, array $options): string;

    /**
     * Configure list of parameters that can be passed to the email.
     */
    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * Get the list of files to attach to the email.
     *
     * @return DataPart[]
     */
    public function getFiles(array $options): array;
}
