<?php

namespace App\Service\Security\Login;

use App\Entity\User;
use App\Service\Security\SecureLinkHasherService;
use DateMalformedStringException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Signature\Exception\ExpiredSignatureException;
use Symfony\Component\Security\Core\Signature\Exception\InvalidSignatureException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\LoginLink\Exception\ExpiredLoginLinkException;
use Symfony\Component\Security\Http\LoginLink\Exception\InvalidLoginLinkException;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

class FirstLoginService
{
    // It is secure because the link is only valid until user has a password
    public const int DEFAULT_LIFETIME = 3600 * 24 * 30; // 30 days

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('@security.user.provider.concrete.app_user_provider')] private readonly UserProviderInterface $userProvider,
        private readonly SecureLinkHasherService $signatureHasher,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function createFirstLoginLink(User $user, int $lifetime = self::DEFAULT_LIFETIME): LoginLinkDetails
    {
        $expires = time() + $lifetime;
        $expiresAt = new DateTimeImmutable('@' . $expires);

        $url = $this->urlGenerator->generate('app_first_login', [
            'user' => $user->getSecureLinkIdentifier(),
            'expires' => $expires,
            'hash' => $this->signatureHasher->computeSignatureHash($user, $expires),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return new LoginLinkDetails($url, $expiresAt);
    }

    /**
     * Consume the login link and return the user if login link is valid.
     *
     * @throws InvalidLoginLinkException
     * @throws ExpiredLoginLinkException
     */
    public function consumeFirstLoginLink(Request $request): User
    {
        /** @var string $userIdentifier */
        $userIdentifier = $request->get('user');

        if (!$hash = $request->get('hash')) {
            $this->logger->notice('[FirstLogin] Error with login link : Missing "hash" parameter.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException('Missing "hash" parameter.');
        }
        if (!$expires = $request->get('expires')) {
            $this->logger->notice('[FirstLogin] Error with login link : Missing "expires" parameter.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException('Missing "expires" parameter.');
        }

        if (!is_numeric($expires)) {
            $this->logger->notice('[FirstLogin] Error with login link : Invalid "expires" parameter.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException('Invalid "expires" parameter.');
        }
        $expires = (int) $expires;

        if (!is_string($hash)) {
            $this->logger->notice('[FirstLogin] Error with login link : Invalid "hash" parameter.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException('Invalid "hash" parameter.');
        }

        try {
            $this->signatureHasher->acceptSignatureHash($userIdentifier, $expires, $hash);

            $user = $this->userProvider->loadUserByIdentifier($userIdentifier);
            if (!$user instanceof User) {
                $this->logger->notice('[FirstLogin] Error trying to login : User not found.', ['user' => $userIdentifier]);

                throw new InvalidLoginLinkException('User not found.');
            }

            if ($user->getPassword()) {
                $this->logger->notice('[FirstLogin] Error trying to login : User already has a password.', ['user' => $userIdentifier]);

                throw new InvalidLoginLinkException('User already has a password.');
            }

            $this->signatureHasher->verifySignatureHash($user, $expires, $hash);
        } catch (UserNotFoundException $e) {
            $this->logger->notice('[FirstLogin] Error trying to login : User not found.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException('User not found.', 0, $e);
        } catch (ExpiredSignatureException $e) {
            $this->logger->notice('[FirstLogin] Error trying to login : Expired signature.', ['user' => $userIdentifier]);

            throw new ExpiredLoginLinkException(ucfirst(str_ireplace('signature', 'login link', $e->getMessage())), 0, $e);
        } catch (InvalidSignatureException $e) {
            $this->logger->notice('[FirstLogin] Error trying to login : Invalid signature.', ['user' => $userIdentifier]);

            throw new InvalidLoginLinkException(ucfirst(str_ireplace('signature', 'login link', $e->getMessage())), 0, $e);
        }

        return $user;
    }
}
