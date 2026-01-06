<?php

namespace OpenEMR\Tools\OAuth2;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Ramsey\Uuid\Uuid;

class ClientCredentialsAssertionGenerator
{
    public static function generateAssertion(Key $privateKey, Key $publicKey, string $oauthTokenUrl, string $clientId): string
    {
        $configuration = Configuration::forAsymmetricSigner(
        // You may use RSA or ECDSA and all their variations (256, 384, and 512)
            new Sha384(),
            $privateKey,
            $publicKey
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $jti = Uuid::uuid4();

        $now   = new \DateTimeImmutable();
        $token = $configuration->builder()
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
            ->relatedTo($clientId)
            ->getToken($configuration->signer(), $configuration->signingKey());
        $assertion = $token->toString(); // The string representation of the object is a JWT string
        return $assertion;
    }
}
