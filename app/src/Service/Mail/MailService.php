<?php

namespace App\Service\Mail;

use App\Component\Mail\MailableUser;
use App\Exception\Mail\InvalidEmailBuilderException;
use App\Mail\MailBuilderInterface;
use Closure;
use Error;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;

use function Symfony\Component\Translation\t;

class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        #[Autowire(value: '%mailer.contact%')] private readonly string $contact,
        #[AutowireIterator(tag: 'app.mail.builders')] private readonly iterable $mailBuilders,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Send email to user using the emailBuilder.
     *
     * @param array|MailableUser|string $to                  Email target. If array, all emails will be added to the email "to" field.
     * @param string                    $builderClass        the builder class to use to build the email (must implements MailBuilderInterface)
     * @param array                     $options             The builder options
     * @param null|Closure              $mailConfigurationFn A function to configure the email before sending it (e.g. to add CC, BCC, etc.). The function receives the Email object as parameter.
     *
     * @throws InvalidEmailBuilderException
     * @throws TransportExceptionInterface
     * @throws Throwable
     */
    public function send(
        array|MailableUser|string $to,
        string $builderClass,
        array $options = [],
        ?Closure $mailConfigurationFn = null,
    ): void {
        $resolver = new OptionsResolver();

        $builder = $this->getMailBuilder($builderClass);

        $builder->configureOptions($resolver);

        $options = $resolver->resolve($options);

        try {
            $subject = $builder->getSubject($options);
            if (is_string($subject)) {
                $subject = t($subject);
            }

            $email = (new Email())
                ->from($this->contact)
                ->subject($subject->trans($this->translator))
                ->html($builder->getHtml($this->twig, $options), 'text/html')
            ;

            // Handle multiple to
            if (is_array($to)) {
                foreach ($to as $emailTo) {
                    $email->addTo($emailTo instanceof MailableUser ? $emailTo->getEmail() : $emailTo);
                }
            } else {
                $email->to($to instanceof MailableUser ? $to->getEmail() : $to);
            }

            // Attach files to emails
            foreach ($builder->getFiles($options) as $file) {
                $email->addPart($file);
            }

            // @phpstan-ignore-next-line (Can throw template exception)
        } catch (Error|Throwable $e) {
            $this->logger->critical("Impossible d'envoyer l'email {$builderClass} : {$e->getMessage()}");

            throw $e;
        }

        // Apply mail configuration function if provided
        if ($mailConfigurationFn) {
            $mailConfigurationFn($email);
        }

        $this->mailer->send($email);
    }

    /**
     * @throws InvalidEmailBuilderException
     */
    private function getMailBuilder(string $mailBuilderClass): MailBuilderInterface
    {
        /** @var MailBuilderInterface $builder */
        foreach ($this->mailBuilders as $builder) {
            if (get_class($builder) === $mailBuilderClass) {
                return $builder;
            }
        }

        throw new InvalidEmailBuilderException($mailBuilderClass);
    }
}
