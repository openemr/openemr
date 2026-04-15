<?php

/**
 * Validates OIDC ID tokens (JWTs).
 *
 * This is the central validation component for external OIDC authentication.
 * Both the web UI flow and the API flow use this class.
 *
 * Validation steps (in order):
 *  1. Parse JWT and extract header (kid, alg)
 *  2. Reject disallowed algorithms (e.g. "none")
 *  3. Resolve signing key from JWKS via JsonWebKeySet (cached + rotation-aware)
 *  4. Verify cryptographic signature
 *  5. Verify iss matches expected issuer
 *  6. Verify aud contains expected client ID
 *  7. Verify exp > current time (with clock skew tolerance)
 *  8. Verify iat is not unreasonably far in the past
 *  9. Map claims to NormalizedIdentity via ClaimMapperInterface
 * 10. Return ValidatedToken
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsSha256;
use Lcobucci\JWT\Signer\Rsa\Sha384 as RsSha384;
use Lcobucci\JWT\Signer\Rsa\Sha512 as RsSha512;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use OpenEMR\Common\Auth\Oidc\Identity\ClaimMapperInterface;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JWKValidatorException;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final readonly class OidcTokenValidator
{
    public function __construct(
        private ClientInterface $httpClient,
        private ClaimMapperInterface $claimMapper,
        private Clock $clock,
        private ?CacheInterface $cache = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Validate an OIDC ID token and return a ValidatedToken on success.
     *
     * @param string $idToken The raw JWT string.
     * @param string $jwksUri The provider's JWKS endpoint URI.
     * @param OidcValidationParameters $parameters Validation configuration.
     * @throws OidcTokenValidationException On any validation failure.
     * @throws \Exception
     */
    public function validate(
        string $idToken,
        string $jwksUri,
        OidcValidationParameters $parameters,
    ): ValidatedToken {
        if ($idToken === '') {
            throw new OidcTokenValidationException('ID token is empty');
        }

        // Step 1: Parse the JWT
        try {
            $token = (new Parser(new JoseEncoder()))->parse($idToken);
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            throw new OidcTokenValidationException('Failed to parse ID token', 0, $e);
        }

        if (!$token instanceof Plain) {
            throw new OidcTokenValidationException('ID token is not a signed JWT');
        }

        // Step 2: Extract and validate algorithm
        $alg = $token->headers()->get('alg', '');
        if (!is_string($alg) || !in_array($alg, $parameters->allowedAlgorithms, true)) {
            throw new OidcTokenValidationException(
                "Unsupported or disallowed algorithm: " . (is_string($alg) ? $alg : 'unknown'),
            );
        }

        $signer = $this->signerForAlgorithm($alg);

        // Step 3: Get signing key by kid
        $kid = $token->headers()->get('kid', '');
        if (!is_string($kid) || $kid === '') {
            throw new OidcTokenValidationException('ID token missing "kid" header');
        }

        try {
            $jwks = new JsonWebKeySet(
                $this->httpClient,
                $jwksUri,
                null,
                $this->logger,
                $this->cache,
            );
            $publicKey = $jwks->getSigningKeyAsPem($kid, $alg);
        } catch (JWKValidatorException $e) {
            throw new OidcTokenValidationException('Failed to retrieve signing key', 0, $e);
        }

        // Step 4-7: Verify signature, issuer, audience, time claims
        $constraints = [
            new SignedWith($signer, $publicKey),
            new IssuedBy($parameters->expectedIssuer),
            new PermittedFor($parameters->expectedAudience),
            // LooseValidAt tolerates missing "nbf" claim, which Firebase/GCIP
            // tokens do not include. It still validates "exp" and "iat".
            new LooseValidAt(
                $this->clock,
                new \DateInterval('PT' . $parameters->clockSkewSeconds . 'S'),
            ),
        ];

        try {
            (new Validator())->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            throw new OidcTokenValidationException('Token validation failed', 0, $e);
        }

        // Step 8: Verify iat is not unreasonably old
        $iat = $token->claims()->get('iat');
        if ($iat instanceof \DateTimeInterface) {
            $maxAge = $this->clock->now()->getTimestamp() - $parameters->maxTokenAgeSeconds;
            if ($iat->getTimestamp() < $maxAge) {
                throw new OidcTokenValidationException('Token iat claim is too far in the past');
            }
        }

        // Step 9: Map claims to NormalizedIdentity
        $claims = $this->extractClaims($token);

        if (!$this->claimMapper->supports($claims)) {
            throw new OidcTokenValidationException('Claim mapper does not support this token');
        }

        try {
            $identity = $this->claimMapper->map($claims);
        } catch (\RuntimeException $e) {
            throw new OidcTokenValidationException('Failed to map token claims to identity', 0, $e);
        }

        // Step 10: Build result
        $exp = $token->claims()->get('exp');
        $expiresAt = $exp instanceof \DateTimeImmutable
            ? $exp
            : new \DateTimeImmutable('@' . $this->clock->now()->getTimestamp());

        $jti = $token->claims()->get('jti');

        return new ValidatedToken(
            identity: $identity,
            claims: $claims,
            expiresAt: $expiresAt,
            jti: is_string($jti) ? $jti : null,
        );
    }

    private function signerForAlgorithm(string $alg): Signer
    {
        return match ($alg) {
            'RS256' => new RsSha256(),
            'RS384' => new RsSha384(),
            'RS512' => new RsSha512(),
            default => throw new OidcTokenValidationException("No signer for algorithm: {$alg}"),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function extractClaims(Plain $token): array
    {
        $claims = [];
        foreach ($token->claims()->all() as $name => $value) {
            $claims[$name] = $value instanceof \DateTimeInterface ? $value->getTimestamp() : $value;
        }

        return $claims;
    }
}
