<?php

namespace App\Mail\User;

use App\Mail\AbstractMailBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;

class AccountCreatedEmail extends AbstractMailBuilder
{
    public const string LOGIN_URL_DETAIL = 'Details';

    public function getSubject(array $options): string|TranslatableMessage
    {
        return 'Votre compte a été créé';
    }

    public function getHtml(Environment $twig, array $options): string
    {
        /** @var LoginLinkDetails $detail */
        $detail = $options[self::LOGIN_URL_DETAIL];

        return $twig->render('@emails/user/account_created.email.html.twig', [
            'loginUrl' => $detail->getUrl(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(self::LOGIN_URL_DETAIL);
        $resolver->setAllowedTypes(self::LOGIN_URL_DETAIL, LoginLinkDetails::class);
    }
}
