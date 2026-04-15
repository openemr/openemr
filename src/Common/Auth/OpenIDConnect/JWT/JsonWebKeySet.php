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
use OpenEMR\BC\ServiceContainer;
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

    private $jwks = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

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
            $content = $this->getJWKFromUriWithCache($jwks_uri);
        }

        // grab the keys array from the content
        $jwks = json_decode((string) $content);
        if (!property_exists($jwks, 'keys')) {
            throw new JWKValidatorException("Malformed jwks missing keys property");
        }
        $this->jwks = $jwks->keys;

        $this->content = $content;
        $this->passphrase = $passphrase;
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
            if ($key->kty === 'RSA') {
                if (!isset($kid) || $key->kid === $kid) {
                    return $key;
                }
            } else {
                if (isset($key->alg) && $key->alg === $alg && $key->kid === $kid) {
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
