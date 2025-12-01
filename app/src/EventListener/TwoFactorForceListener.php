<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class TwoFactorForceListener
{
    public function __construct(
        #[Autowire('%app.force_2fa%')] private bool $force2fa,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[AsEventListener(event: RequestEvent::class, priority: -255)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->force2fa) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->isTotpAuthenticationEnabled()) {
            return;
        }

        // Disable on 2FA enable route
        if ('app_2fa_enable' === $event->getRequest()->attributes->get('_route')) {
            return;
        }

        // Redirect to 2FA setup page
        $event->setResponse(
            new RedirectResponse(
                $this->urlGenerator->generate('app_2fa_enable')
            )
        );
    }
}
