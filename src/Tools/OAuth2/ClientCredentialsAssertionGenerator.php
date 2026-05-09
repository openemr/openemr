<?php

namespace OpenEMR\Tools\OAuth2;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Ramsey\Uuid\Uuid;

class ClientCredentialsAssertionGenerator
{
    /**
     * @param non-empty-string $oauthTokenUrl
     * @param non-empty-string $clientId
     * @param non-empty-string|null $kid Optional JWK `kid` to embed in
     *   the JWT header. SMART Backend Services / RFC 7515 §4.1.4 require
     *   `kid` so the OAuth server can resolve the right JWK from the
     *   client's registered set; OpenEMR's
     *   `JWTClientAuthenticationService` rejects assertions without it.
     *   Left optional so isolated tests that exercise just the
     *   generator (and don't round-trip through validation) can keep
     *   working without supplying a fixture kid.
     * @return non-empty-string
     */
    public static function generateAssertion(
        Key $privateKey,
        Key $publicKey,
        string $oauthTokenUrl,
        string $clientId,
        ?string $kid = null,
    ): string {
        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            $privateKey,
            $publicKey
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $jti = Uuid::uuid4()->toString();

        $now   = new \DateTimeImmutable();
        $builder = $configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($clientId)
            // Configures the audience (aud claim)
            ->permittedFor($oauthTokenUrl)
            // Configures the id (jti claim)
            ->identifiedBy($jti)
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify('+60 seconds'))
            ->relatedTo($clientId);

        if ($kid !== null) {
            $builder = $builder->withHeader('kid', $kid);
        }

        $token = $builder->getToken($configuration->signer(), $configuration->signingKey());
        return $token->toString(); // The string representation of the object is a JWT string
    }
}
