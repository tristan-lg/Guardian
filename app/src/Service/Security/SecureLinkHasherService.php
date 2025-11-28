<?php

namespace App\Service\Security;

use App\Component\SecureLink\SecureLinkEntityInterface;
use SensitiveParameter;
use Stringable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Signature\Exception\ExpiredSignatureException;
use Symfony\Component\Security\Core\Signature\Exception\InvalidSignatureException;

class SecureLinkHasherService
{
    public function __construct(
        #[SensitiveParameter] #[Autowire('%kernel.secret%')] private readonly string $secret
    ) {
        if (!$secret) {
            throw new InvalidArgumentException('A non-empty secret is required.');
        }
    }

    /**
     * Verifies the hash using the provided user identifier and expire time.
     *
     * This method must be called before the user object is loaded from a provider.
     *
     * @param string $userIdentifier The identifier
     * @param int    $expires        The expiry time as a unix timestamp
     * @param string $hash           The plaintext hash provided by the request
     *
     * @throws InvalidSignatureException If the signature does not match the provided parameters
     * @throws ExpiredSignatureException If the signature is no longer valid
     */
    public function acceptSignatureHash(string $userIdentifier, int $expires, string $hash): void
    {
        if ($expires < time()) {
            throw new ExpiredSignatureException('Signature has expired.');
        }
        $hmac = substr($hash, 0, 44);
        $payload = substr($hash, 44) . ':' . $expires . ':' . $userIdentifier;

        if (!hash_equals($hmac, $this->generateHash($payload))) {
            throw new InvalidSignatureException('Invalid or expired signature.');
        }
    }

    /**
     * Verifies the hash using the provided user and expire time.
     *
     * @param SecureLinkEntityInterface $entity  An entity that supports secure links
     * @param int                       $expires The expiry time as a unix timestamp
     * @param string                    $hash    The plaintext hash provided by the request
     *
     * @throws InvalidSignatureException If the signature does not match the provided parameters
     * @throws ExpiredSignatureException If the signature is no longer valid
     */
    public function verifySignatureHash(SecureLinkEntityInterface $entity, int $expires, string $hash): void
    {
        if ($expires < time()) {
            throw new ExpiredSignatureException('Signature has expired.');
        }

        if (!hash_equals($hash, $this->computeSignatureHash($entity, $expires))) {
            throw new InvalidSignatureException('Invalid or expired signature.');
        }
    }

    /**
     * Computes the secure hash for the provided supported entity and expire time.
     *
     * @param SecureLinkEntityInterface $entity  An entity that supports secure links
     * @param int                       $expires The expiry time as a unix timestamp
     */
    public function computeSignatureHash(SecureLinkEntityInterface $entity, int $expires): string
    {
        $identifier = $entity->getSecureLinkIdentifier();
        $fieldsHash = hash_init('sha256');

        foreach ($entity->getSecureLinkProperties() as $key => $value) {
            if (!is_scalar($value) && !$value instanceof Stringable) {
                throw new \InvalidArgumentException(sprintf('The property path "%s" on the object "%s" must return a value that can be cast to a string, but "%s" was returned.', $key, $entity::class, get_debug_type($value)));
            }
            hash_update($fieldsHash, ':' . base64_encode((string) $value));
        }

        $fieldsHash = strtr(base64_encode(hash_final($fieldsHash, true)), '+/=', '-_~');

        return $this->generateHash($fieldsHash . ':' . $expires . ':' . $identifier) . $fieldsHash;
    }

    private function generateHash(string $tokenValue): string
    {
        return strtr(base64_encode(hash_hmac('sha256', $tokenValue, $this->secret, true)), '+/=', '-_~');
    }
}
