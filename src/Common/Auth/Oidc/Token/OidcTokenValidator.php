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
 *  9. Reject if jti is in the revocation list (immediate-lockout)
 * 10. Map claims to NormalizedIdentity via ClaimMapperInterface
 * 11. Return ValidatedToken
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

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
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
use OpenEMR\Common\Auth\Oidc\Identity\ClaimMapperInterface;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JWKValidatorException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

readonly class OidcTokenValidator
{
    public function __construct(
        private ClientInterface $httpClient,
        private ClaimMapperInterface $claimMapper,
        private ClockInterface $clock,
        private JWTRepository $jwtRepository,
        private TokenRevocationCheckerInterface $revocationChecker,
        private ?CacheInterface $cache = null,
        private ?LoggerInterface $logger = null,
        private ?OidcUrlValidator $urlValidator = null,
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
                urlValidator: $this->urlValidator,
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

        // LooseValidAt validates `exp` *if present* but doesn't require it.
        // We require it explicitly: a token without `exp` would store
        // `jti_exp = NULL` in jwt_grant_history, and the replay-lookup
        // filter `jti_exp > ?` excludes NULL rows from the result set
        // (SQL: NULL > x is NULL, not true) — letting the same token be
        // replayed indefinitely. Reject up front so the storage layer
        // never sees a NULL exp from this path.
        $exp = $token->claims()->get('exp');
        if (!$exp instanceof \DateTimeImmutable) {
            throw new OidcTokenValidationException('Token missing required exp claim');
        }

        // Step 8: Verify iat is not unreasonably old
        $iat = $token->claims()->get('iat');
        if ($iat instanceof \DateTimeInterface) {
            $maxAge = $this->clock->now()->getTimestamp() - $parameters->maxTokenAgeSeconds;
            if ($iat->getTimestamp() < $maxAge) {
                throw new OidcTokenValidationException('Token iat claim is too far in the past');
            }
        }

        // Step 9: Reject revoked jtis. Tokens without a jti claim cannot be
        // revoked through this mechanism — operators wanting immediate lockout
        // for those should rely on session invalidation instead.
        $jti = $token->claims()->get('jti');
        if (is_string($jti) && $jti !== '' && $this->revocationChecker->isRevoked($jti)) {
            throw new OidcTokenValidationException('Token has been revoked');
        }

        // JTI replay protection.
        //
        // Every ID-token validation records a replay key so a second presentation
        // of the same token is rejected. Providers that omit the "jti" claim
        // (notably Firebase/GCIP in some configurations) fall back to a synthetic
        // key derived from (iss, sub, iat), which is unique per token issuance.
        //
        // The repository's "jti_exp > ?" filter is passed the current clock time
        // (not the token's own exp), so the lookup returns "has this jti been
        // seen recently, and is the stored record still within its validity
        // window". Records past their stored jti_exp naturally age out.
        $replayKey = $this->computeReplayKey($token);
        $nowTimestamp = $this->clock->now()->getTimestamp();
        if ($this->jwtRepository->getJwtGrantHistoryForJTI($replayKey, $nowTimestamp) !== []) {
            throw new OidcTokenValidationException('ID token has already been used (replay detected)');
        }
        $issuerClaim = $token->claims()->get('iss');
        $issuerForRecord = is_string($issuerClaim) ? $issuerClaim : $parameters->expectedIssuer;
        // exp is now guaranteed DateTimeImmutable by the explicit guard above.
        $this->jwtRepository->saveJwtHistory($replayKey, $issuerForRecord, $exp->getTimestamp());

        // Step 10: Map claims to NormalizedIdentity
        $claims = $this->extractClaims($token);

        if (!$this->claimMapper->supports($claims)) {
            throw new OidcTokenValidationException('Claim mapper does not support this token');
        }

        try {
            $identity = $this->claimMapper->map($claims);
        } catch (\RuntimeException $e) {
            throw new OidcTokenValidationException('Failed to map token claims to identity', 0, $e);
        }

        // Step 11: Build result. exp is guaranteed DateTimeImmutable by the
        // require-exp guard right after the LooseValidAt assertions above.
        return new ValidatedToken(
            identity: $identity,
            claims: $claims,
            expiresAt: $exp,
            jti: is_string($jti) ? $jti : null,
        );
    }

    /**
     * Compute the replay-protection key for a token.
     *
     * Prefers the token's "jti" claim when present (the OIDC standard). Falls
     * back to a synthetic SHA-256 digest of (iss, sub, iat) when the provider
     * omits "jti" — this is stable per token issuance so a second presentation
     * of the same token still collides with the stored record.
     *
     * @throws OidcTokenValidationException When the token has neither jti nor
     *         the iss/sub/iat trio needed to compute a per-issuance synthetic
     *         key. Falling back to a constant placeholder would collide all
     *         tokens for the same (iss, sub) and let a single presented token
     *         lock the user out of subsequent logins.
     */
    private function computeReplayKey(Plain $token): string
    {
        $jti = $token->claims()->get('jti');
        if (is_string($jti) && $jti !== '') {
            return $jti;
        }

        $iat = $token->claims()->get('iat');
        $iatPart = match (true) {
            $iat instanceof \DateTimeInterface => (string) $iat->getTimestamp(),
            is_int($iat) => (string) $iat,
            default => null,
        };
        if ($iatPart === null) {
            throw new OidcTokenValidationException(
                'Token has neither jti nor iat; cannot compute replay key safely',
            );
        }

        $iss = $token->claims()->get('iss');
        $sub = $token->claims()->get('sub');
        if (!is_string($iss) || $iss === '' || !is_string($sub) || $sub === '') {
            throw new OidcTokenValidationException(
                'Token has no jti and missing iss or sub; cannot compute synthetic replay key',
            );
        }

        return 'oidc-synthetic:' . hash('sha256', $iss . '|' . $sub . '|' . $iatPart);
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
