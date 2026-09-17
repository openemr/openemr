<?php

/**
 * Validates an external ID token (signature, issuer, audience, nonce, expiry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use DateInterval;
use DateTimeImmutable;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;

final readonly class OidcIdTokenValidator
{
    public function __construct(
        private ClientInterface $httpClient,
        private ClockInterface $clock,
    ) {
    }

    public function validate(
        string $idToken,
        OidcRpSettings $settings,
        OidcProviderMetadata $metadata,
        string $nonce,
    ): OidcIdTokenClaims {
        if ($idToken === '' || $nonce === '') {
            throw new OidcRpException('ID token or nonce is missing');
        }

        try {
            $token = (new Parser(new JoseEncoder()))->parse($idToken);
        } catch (\Throwable $exception) {
            throw new OidcRpException('ID token is malformed', 0, $exception);
        }

        if (!$token instanceof UnencryptedToken) {
            throw new OidcRpException('ID token must be a signed JWT');
        }

        $algorithm = $token->headers()->get('alg');
        if (!is_string($algorithm) || !in_array($algorithm, OidcJwkSigner::SUPPORTED_ALGORITHMS, true)) {
            throw new OidcRpException('ID token signing algorithm is not allowed');
        }

        // The JsonWebKeySet constructor fetches the JWKS over HTTP and parses it,
        // so a provider outage or a malformed document throws from there rather
        // than from verify(). Those are not OidcRpException -- an unreachable
        // provider surfaces as JWKValidatorException and an invalid JSON body as
        // TypeError -- and the callback only renders its clean login-failure page
        // for OidcRpException. Keep the whole retrieve-and-verify step inside one
        // boundary so a transient IdP failure cannot turn into a 500.
        try {
            $jwks = new JsonWebKeySet($this->httpClient, $metadata->jwksUri);
            $signer = new OidcJwkSigner($algorithm);
            $headers = [
                'alg' => $algorithm,
                'kid' => $token->headers()->get('kid'),
            ];
            $signer->modifyHeader($headers);
            $verified = $signer->verify($token->signature()->hash(), $token->payload(), $jwks);
        } catch (\Throwable $exception) {
            throw new OidcRpException('ID token signature could not be verified', 0, $exception);
        }
        if ($verified !== true) {
            throw new OidcRpException('ID token signature is invalid');
        }

        $claims = $token->claims();
        $this->assertTimeClaims($token);
        $issuer = $claims->get('iss');
        if (!is_string($issuer) || $issuer !== $settings->issuer) {
            throw new OidcRpException('ID token issuer is invalid');
        }

        $audience = $claims->get('aud');
        $audiences = is_array($audience) ? $audience : [$audience];
        $audienceMatched = false;
        foreach ($audiences as $entry) {
            if (is_string($entry) && $entry === $settings->clientId) {
                $audienceMatched = true;
                break;
            }
        }
        if (!$audienceMatched) {
            throw new OidcRpException('ID token audience is invalid');
        }

        $azp = $claims->get('azp');
        // OpenID Connect Core 3.1.3.7: a token naming more than one audience has
        // to say which client it was issued for. Without azp a token minted for
        // another client that happens to list this client_id in aud would pass.
        if (count($audiences) > 1 && ($azp === null || $azp === '')) {
            throw new OidcRpException('ID token with multiple audiences is missing an authorized party');
        }
        if ($azp !== null && $azp !== '') {
            if (!is_string($azp) || $azp !== $settings->clientId) {
                throw new OidcRpException('ID token authorized party is invalid');
            }
        }

        $tokenNonce = $claims->get('nonce');
        if (!is_string($tokenNonce) || !hash_equals($nonce, $tokenNonce)) {
            throw new OidcRpException('ID token nonce is invalid');
        }

        $subject = $claims->get('sub');
        if (!is_string($subject) || $subject === '') {
            throw new OidcRpException('ID token subject is missing');
        }

        return new OidcIdTokenClaims(
            issuer: $issuer,
            subject: $subject,
            email: $this->stringClaim($claims->get('email')),
            emailVerified: self::isVerifiedEmailClaim($claims->get('email_verified')),
            preferredUsername: $this->stringClaim($claims->get('preferred_username')),
            givenName: $this->stringClaim($claims->get('given_name')),
            familyName: $this->stringClaim($claims->get('family_name')),
            name: $this->stringClaim($claims->get('name')),
            raw: $claims->all(),
        );
    }

    private function assertTimeClaims(UnencryptedToken $token): void
    {
        $now = $this->clock->now();
        $leeway = new DateInterval('PT60S');
        $exp = $token->claims()->get('exp');
        if (!$exp instanceof DateTimeImmutable) {
            throw new OidcRpException('ID token is missing an expiration');
        }
        if ($now > $exp->add($leeway)) {
            throw new OidcRpException('ID token has expired');
        }

        $iat = $token->claims()->get('iat');
        if (!$iat instanceof DateTimeImmutable) {
            throw new OidcRpException('ID token is missing an issued-at time');
        }
        if ($iat > $now->add($leeway)) {
            throw new OidcRpException('ID token issued-at time is in the future');
        }

        $nbf = $token->claims()->get('nbf');
        if ($nbf instanceof DateTimeImmutable && $now->add($leeway) < $nbf) {
            throw new OidcRpException('ID token is not yet valid');
        }
    }

    private function stringClaim(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private static function isVerifiedEmailClaim(mixed $value): bool
    {
        return $value === true || $value === 'true';
    }
}
