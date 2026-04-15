<?php

/**
 * JsonWebKeySet represents a signing key used for JWT signature verification.  It can take a JSON Web Key Set (JWKS)
 * as a string or as a URI.  If a URI is provided it will retrieve the JWKS and store them internally.
 *
 * When a PSR-16 cache is supplied, JWKS documents fetched from a URI are cached to avoid
 * re-fetching on every instantiation. Cache failures fall back to HTTP so cache problems
 * never break signature verification.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\JWT;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Utils\HttpUtils;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class JsonWebKeySet implements Key
{
    /** Cache key prefix for JWKS documents fetched by URI. */
    private const CACHE_KEY_PREFIX = 'oidc_jwks_';

    /** Default TTL for cached JWKS documents in seconds. */
    private const DEFAULT_CACHE_TTL_SECONDS = 86400;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /** @var list<object> */
    private array $jwks = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    private ?string $jwksUri = null;

    public function __construct(
        ClientInterface $httpClient,
        $jwks_uri = null,
        $jwks = null,
        ?LoggerInterface $logger = null,
        private readonly ?CacheInterface $cache = null,
        private readonly int $cacheTtlSeconds = self::DEFAULT_CACHE_TTL_SECONDS,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
        $this->setHttpClient($httpClient);

        $passphrase = '';
        if (empty($jwks_uri) && empty($jwks)) {
            throw new JWKValidatorException("jwks_uri or jwks string must be provided");
        }
        $content = $jwks;
        if (!empty($jwks_uri) && is_string($jwks_uri)) {
            $this->jwksUri = $jwks_uri;
            $content = $this->getJWKFromUriWithCache($jwks_uri);
        }

        // grab the keys array from the content
        $jwks = json_decode((string) $content);
        if (!property_exists($jwks, 'keys')) {
            throw new JWKValidatorException("Malformed jwks missing keys property");
        }
        $this->jwks = $this->extractKeys($jwks);

        $this->content = $content;
        $this->passphrase = $passphrase;
    }

    /**
     * Narrow the decoded JWKS document's "keys" array to a list of objects.
     *
     * @return list<object>
     */
    private function extractKeys(mixed $decoded): array
    {
        if (!is_object($decoded) || !property_exists($decoded, 'keys') || !is_array($decoded->keys)) {
            return [];
        }

        $keys = [];
        foreach ($decoded->keys as $key) {
            if (is_object($key)) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Force-refresh the JWKS from its URI, bypassing the cache read path.
     *
     * Intended for key-rotation scenarios: when a token's kid isn't present
     * in the currently-loaded JWKS, a fresh fetch may bring newly rotated
     * keys. Cache is re-populated on success. No-op when the set was
     * constructed from inline JWKS content (no URI to refresh from).
     *
     * @throws JWKValidatorException If the re-fetched document is malformed.
     */
    public function refresh(): void
    {
        if ($this->jwksUri === null) {
            return;
        }

        $json = $this->getJWKFromUri($this->jwksUri);

        if ($this->cache !== null) {
            $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $this->jwksUri);
            try {
                $this->cache->set($cacheKey, $json, $this->cacheTtlSeconds);
            } catch (\RuntimeException | \InvalidArgumentException $exception) {
                $this->logger->warning(
                    "JWKS cache write failed on refresh",
                    ['jwks_uri' => $this->jwksUri, 'exception' => $exception],
                );
            }
        }

        $decoded = json_decode($json);
        if (!is_object($decoded) || !property_exists($decoded, 'keys')) {
            throw new JWKValidatorException("Malformed jwks on refresh");
        }

        $this->jwks = $this->extractKeys($decoded);
        $this->content = $json;
    }

    /**
     * Resolve a specific JWK by kid and algorithm to a PEM-wrapped signing key.
     *
     * Strict matching: requires exact kid match. If the JWK declares an "alg"
     * field it must match the requested algorithm; JWKs without an "alg" field
     * are accepted (callers derive the algorithm from the token header). Keys
     * marked with use=="enc" are excluded.
     *
     * On cache miss the JWKS is refreshed once to tolerate provider key
     * rotation. If the kid is still not present after refresh the call fails.
     *
     * @param non-empty-string $kid The JWT header "kid" value.
     * @param non-empty-string $alg The JWT header "alg" value (e.g. "RS256").
     * @throws JWKValidatorException On unknown kid, unsupported key type, or malformed key material.
     */
    public function getSigningKeyAsPem(string $kid, string $alg): InMemory
    {
        $jwk = $this->findJwkStrict($kid, $alg);
        if ($jwk === null) {
            $this->refresh();
            $jwk = $this->findJwkStrict($kid, $alg);
        }

        if ($jwk === null) {
            throw new JWKValidatorException("No signing key found for kid '{$kid}'");
        }

        return $this->jwkToPem($jwk);
    }

    private function findJwkStrict(string $kid, string $alg): ?object
    {
        foreach ($this->jwks as $jwk) {
            $props = get_object_vars($jwk);
            $jwkKid = isset($props['kid']) && is_string($props['kid']) ? $props['kid'] : null;
            if ($jwkKid !== $kid) {
                continue;
            }
            $jwkUse = isset($props['use']) && is_string($props['use']) ? $props['use'] : null;
            if ($jwkUse !== null && $jwkUse !== 'sig') {
                continue;
            }
            $jwkAlg = isset($props['alg']) && is_string($props['alg']) ? $props['alg'] : null;
            if ($jwkAlg !== null && $jwkAlg !== $alg) {
                continue;
            }
            return $jwk;
        }
        return null;
    }

    private function jwkToPem(object $jwk): InMemory
    {
        $props = get_object_vars($jwk);

        $kty = isset($props['kty']) && is_string($props['kty']) ? $props['kty'] : null;
        if ($kty !== 'RSA') {
            throw new JWKValidatorException('Unsupported JWK key type: ' . ($kty ?? 'unknown'));
        }

        $n = isset($props['n']) && is_string($props['n']) ? $props['n'] : null;
        $e = isset($props['e']) && is_string($props['e']) ? $props['e'] : null;
        if ($n === null || $e === null) {
            throw new JWKValidatorException('RSA JWK missing "n" or "e" parameter');
        }

        $bigN = new BigInteger(HttpUtils::base64url_decode($n), 256);
        $bigE = new BigInteger(HttpUtils::base64url_decode($e), 256);

        /** @var \phpseclib3\Crypt\RSA\PublicKey $rsaKey */
        $rsaKey = PublicKeyLoader::load(['n' => $bigN, 'e' => $bigE]);

        $pem = $rsaKey->toString('PKCS8');
        assert(is_string($pem) && $pem !== '');

        return InMemory::plainText($pem);
    }
    /**
     * Returns a JWK that matches the given key id and algorithm in the JWK Set.
     * @param $kid The key id for the JWK we are attempting to retrieve
     * @param $alg The algorithm for the JWK we are attempting to retrieve
     * @return object
     */
    public function getJSONWebKey($kid, $alg)
    {
        $this->logger->debug("JsonWebKeySet::getJSONWebKey() Attempting to find web key for kid & alg", ['kid' => $kid, 'alg' => $alg]);
        foreach ($this->jwks as $key) {
            $props = get_object_vars($key);
            $kty = $props['kty'] ?? null;
            $keyKid = $props['kid'] ?? null;
            $keyAlg = $props['alg'] ?? null;
            if ($kty === 'RSA') {
                if (!isset($kid) || $keyKid === $kid) {
                    return $key;
                }
            } else {
                if ($keyAlg !== null && $keyAlg === $alg && $keyKid === $kid) {
                    return $key;
                }
            }
        }
        $this->logger->debug("Failed to find jwk key");
        return null;
    }

    public function setHttpClient(?ClientInterface $client)
    {
        $this->httpClient = $client;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    protected function getJWKFromUri($jwk_uri): string
    {
        try {
            $request = new Request('GET', $jwk_uri);
            $body = $this->httpClient->sendRequest($request)->getBody();
            $json = $body->getContents();
            return $json;
        } catch (RequestException | ConnectException $exception) {
            throw new JWKValidatorException("failed to retrieve jwk contents from jwk_uri", 0, $exception);
        } catch (\Throwable $exception) {
            $this->logger->error("Failed to retrieve jwk contents from jwk_uri and unknown error occurred", ['exception' => $exception, 'jwk_uri' => $jwk_uri]);
            throw new JWKValidatorException("failed to retrieve jwk contents from jwk_uri", 0, $exception);
        }
    }

    /**
     * Fetch JWKS by URI, consulting the injected cache when one is available.
     *
     * Cache failures are logged and fall through to HTTP so a broken cache backend
     * never breaks signature verification.
     */
    private function getJWKFromUriWithCache(string $jwksUri): string
    {
        if ($this->cache === null) {
            $json = $this->getJWKFromUri($jwksUri);
            $this->logger->debug("Retrieved jwk content from jwks_uri", ['jwks_uri' => $jwksUri]);
            return $json;
        }

        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $jwksUri);

        try {
            $cached = $this->cache->get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                $this->logger->debug("Retrieved jwk content from cache", ['jwks_uri' => $jwksUri]);
                return $cached;
            }
        } catch (\RuntimeException | \InvalidArgumentException $exception) {
            $this->logger->warning(
                "JWKS cache read failed, falling back to HTTP",
                ['jwks_uri' => $jwksUri, 'exception' => $exception],
            );
        }

        $json = $this->getJWKFromUri($jwksUri);
        $this->logger->debug("Retrieved jwk content from jwks_uri", ['jwks_uri' => $jwksUri]);

        try {
            $this->cache->set($cacheKey, $json, $this->cacheTtlSeconds);
        } catch (\RuntimeException | \InvalidArgumentException $exception) {
            $this->logger->warning(
                "JWKS cache write failed",
                ['jwks_uri' => $jwksUri, 'exception' => $exception],
            );
        }

        return $json;
    }

    /** @return non-empty-string */
    public function contents(): string
    {
        if ($this->content === '') {
            throw new JWKValidatorException('JWKS content is empty');
        }
        return $this->content;
    }

    /** @return string */
    public function passphrase(): string
    {
        return $this->passphrase;
    }
}
