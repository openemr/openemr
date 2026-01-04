<?php

/**
 * JwtService.php
 * Simple helper for creating, validating, and parsing JWTs using lcobucci/jwt v4.
 *
 *
 * @package openemr
 * @license   There are segments of code in this file that have been generated via PHPStorm Junie AI and are licensed as Public Domain.
 * @link      http://www.open-emr.org
 * @author    OpenEMR
 */

namespace OpenEMR\Common\Auth\JWT;

use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use OpenEMR\Common\Logging\SystemLogger;
use Random\RandomException;


/**
 * JwtService provides a focused API for:
 *  - building a JWT with custom claims
 *  - validating a JWT signature and temporal claims
 *  - parsing a JWT and extracting claims
 *
 * This implementation uses RSA (RS384) by default and the lcobucci/jwt 4.x APIs.
 */
readonly class JwtService
{
    private Configuration $config;

    /**
     * @param int $defaultTtlSeconds Default time-to-live in seconds for tokens you create
     * @param int $clockSkewSeconds Allowed clock skew when validating time-based claims
     * @param string $algorithm Signing algorithm to use. Default RS384, available RS256.
     * @param string|null $issuer Optional default issuer claim (iss)
     * @param string|null $audience Optional default audience claim (aud)
     */
    public function __construct(
        private int     $defaultTtlSeconds = 3600,
        private int     $clockSkewSeconds = 60,
        private string  $algorithm = 'RS384',
        private ?string $issuer = null,
        private ?string $audience = null,
    ) {
        $signer = match (strtoupper($this->algorithm)) {
            'RS256' => new Sha256(),
            default => new Sha384(),
        };

        ['privateKey' => $privateKey, 'publicKey' => $publicKey, 'passphrase' => $passphrase] = self::getKeysInfo();

        $privateKey = !empty($passphrase)
            ? InMemory::file($privateKey, $passphrase)
            : InMemory::file($privateKey);
        $publicKey = InMemory::file($publicKey);

        $this->config = Configuration::forAsymmetricSigner($signer, $privateKey, $publicKey);
    }

    public static function getKeysInfo(): array
    {
        // NOTE: we were not able to use Auth -> Config (or database in general),
        // since on `interface/modules/zend_modules/public/index.php` we need first to parse the token
        // before we load `global.php` and get the database connection working
        $privateKey = getenv('OPENEMR_PORTAL_JWT_PRIVATE_KEY_FULL_PATH');
        $publicKey = getenv('OPENEMR_PORTAL_JWT_PUBLIC_KEY_FULL_PATH');
        $passphrase = getenv('OPENEMR_PORTAL_JWT_PRIVATE_KEY_PASSPHRASE');

        if (empty($privateKey) || empty($publicKey)) {
            (new SystemLogger())->debug(self::class . ": JWT signing keys information not found in environment.");
            throw new RuntimeException('Unable to generate token. Missing information.');
        }

        // Check if files exist and are readable
        if (!file_exists($privateKey) || !is_readable($privateKey)) {
            (new SystemLogger())->error(self::class . ": Private key file not found or not readable: " . $privateKey);
            throw new RuntimeException('Unable to generate token. Wrong configuration.');
        }

        if (!file_exists($publicKey) || !is_readable($publicKey)) {
            (new SystemLogger())->error(self::class . ": Public key file not found or not readable: " . $publicKey);
            throw new RuntimeException('Unable to generate token. Wrong configuration.');
        }

        return [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
            'passphrase' => $passphrase,
        ];
    }

    /**
     * Create and sign a JWT.
     *
     * @param array $customClaims Key/value array of custom claims to include (e.g., ['role' => 'admin'])
     * @param int|null $ttlSeconds Token time-to-live; defaults to constructor default
     * @param string|null $subject Optional subject (sub)
     * @param string|null $tokenId Optional JWT ID (jti); if omitted, a random ID is generated
     * @return string The compact JWT string
     * @throws RandomException|\JsonException
     */
    public function createToken(
        array $customClaims = [],
        ?int $ttlSeconds = null,
        ?string $subject = null,
        ?string $tokenId = null
    ): string {
        // Sanitize incoming custom claims to ensure they are JSON-encodable with valid UTF-8 strings
        $customClaims = $this->sanitizeClaims($customClaims);

        $now = new DateTimeImmutable();
        $expiresIn = $ttlSeconds ?? $this->defaultTtlSeconds;
        $exp = $now->modify("+{$expiresIn} seconds");

        $builder = $this->config->builder()
            ->issuedAt($now)
            ->expiresAt($exp)
            ->canOnlyBeUsedAfter($now)
            ->identifiedBy($tokenId ?? bin2hex(random_bytes(16)));

        if ($this->issuer) {
            $builder = $builder->issuedBy($this->issuer);
        }
        if ($this->audience) {
            $builder = $builder->permittedFor($this->audience);
        }
        if (!empty($subject)) {
            $builder = $builder->relatedTo($this->sanitizeString($subject));
        }

        foreach ($customClaims as $name => $value) {
            // lcobucci/jwt reserves some claim names; we rely on API to handle overwriting sensibly.
            $builder = $builder->withClaim($name, $value);
        }

        $token = $builder->getToken($this->config->signer(), $this->config->signingKey());
        return $token->toString();
    }

    /**
     * Recursively sanitize an array of claims so that all values are JSON-encodable and strings are valid UTF-8.
     *
     * - Scalars (int/float/bool/null) are left as-is
     * - Strings are normalized to valid UTF-8; invalid bytes are converted or stripped
     * - Arrays are processed recursively
     * - Objects:
     *   - DateTimeInterface => RFC3339 string
     *   - JsonSerializable  => jsonSerialize() result, sanitized
     *   - Otherwise casts to string, then sanitized
     * - Resources are cast to string (their gettype) to avoid json encoding failures
     *
     * @param array $claims
     * @return array
     * @throws \JsonException
     */
    private function sanitizeClaims(array $claims): array
    {
        $result = [];
        foreach ($claims as $k => $v) {
            // Ensure claim names are strings and valid UTF-8
            $key = is_string($k) ? $this->sanitizeString($k) : (string) $k;
            $result[$key] = $this->sanitizeValue($v);
        }
        return $result;
    }

    /**
     * Sanitize a single value to be JSON-encodable with valid UTF-8 strings.
     * @param mixed $value
     * @return mixed
     * @throws \JsonException
     */
    private function sanitizeValue(mixed $value): mixed
    {
        if (is_null($value) || is_int($value) || is_float($value) || is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        if (is_array($value)) {
            return $this->sanitizeClaims($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return $this->sanitizeString($value->format(DATE_ATOM));
        }

        if ($value instanceof \JsonSerializable) {
            try {
                $serialized = $value->jsonSerialize();
            } catch (\Throwable) {
                $serialized = (string) $value;
            }
            return $this->sanitizeValue($serialized);
        }

        if (is_object($value)) {
            // Fallback: cast to string representation
            return $this->sanitizeString((string) $value);
        }

        if (is_resource($value)) {
            return $this->sanitizeString(get_resource_type($value));
        }

        // Unknown type fallback
        return $this->sanitizeString((string) $value);
    }

    /**
     * Ensure a string is valid UTF-8. Attempt conversion from common encodings; strip invalid bytes as last resort.
     * @param string $str
     * @return string
     * @throws \JsonException
     */
    private function sanitizeString(string $str): string
    {
        // Quick path: already valid UTF-8
        if (function_exists('mb_check_encoding')) {
            if (mb_check_encoding($str, 'UTF-8')) {
                return $str;
            }
        } else {
            // If mbstring not available, try json_encode round-trip to detect issues
            if (json_encode($str, JSON_THROW_ON_ERROR) !== false) {
                return $str;
            }
        }

        // Try converting from likely encodings to UTF-8
        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($str, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            if ($converted !== false) {
                $str = $converted;
            }
        }

        // As a last resort, drop invalid bytes
        $iconv = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
        if ($iconv !== false) {
            $str = $iconv;
        }

        return $str;
    }

    /**
     * Parse a JWT without validating it.
     *
     * @param string $jwt
     * @return Plain
     */
    public function parse(string $jwt): Plain
    {
        /** @var UnencryptedToken $token */
        $token = $this->config->parser()->parse($jwt);
        if (!$token instanceof Plain) {
            // Practical safeguard; lcobucci v4 returns Plain for unsecured/signed tokens
            throw new \RuntimeException('Unsupported token type');
        }
        return $token;
    }

    /**
     * Validate a token. Returns the parsed token on success; throws on validation failure.
     *
     * Validation includes:
     *  - Signature: RS256 using provided public key
     *  - Temporal claims: iat/nbf/exp with configured clock skew
     *  - Optional iss/aud/sub/jti checks when provided
     *
     * @param string $jwt
     * @param array $requirements Optional associative array for additional constraints:
     *                            ['issuer' => 'iss', 'audience' => 'aud', 'subject' => 'sub', 'id' => 'jti']
     * @return Plain
     * @throws \DateInvalidTimeZoneException
     */
    public function validate(string $jwt, array $requirements = []): Plain
    {
        $token = $this->parse($jwt);

        $constraints = [];
        $constraints[] = new SignedWith($this->config->signer(), $this->config->verificationKey());
        $constraints[] = new LooseValidAt(
            new SystemClock(new \DateTimeZone(\date_default_timezone_get())),
            new DateInterval('PT' . max(0, $this->clockSkewSeconds) . 'S')
        );

        $iss = $requirements['issuer'] ?? $this->issuer;
        if (!empty($iss)) {
            $constraints[] = new IssuedBy($iss);
        }
        $aud = $requirements['audience'] ?? $this->audience;
        if (!empty($aud)) {
            $constraints[] = new PermittedFor($aud);
        }
        if (!empty($requirements['subject'])) {
            $constraints[] = new RelatedTo($requirements['subject']);
        }
        if (!empty($requirements['id'])) {
            $constraints[] = new IdentifiedBy($requirements['id']);
        }

        $this->config->setValidationConstraints(...$constraints);
        $this->config->validator()->assert($token, ...$this->config->validationConstraints());

        return $token;
    }

    /**
     * Convenience helper to extract a claim from a compact JWT string.
     *
     * Note: this does NOT validate signature; call validate() first if you need trust.
     *
     * @param string $jwt
     * @param string $claimName
     * @param mixed $default
     * @return mixed
     */
    public function getClaim(string $jwt, string $claimName, $default = null)
    {
        $token = $this->parse($jwt);
        $claims = $token->claims();
        return $claims->has($claimName) ? $claims->get($claimName) : $default;
    }
}
