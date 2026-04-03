<?php

/**
 * Fetches and caches JSON Web Key Sets (JWKS) from OIDC provider endpoints.
 *
 * Handles key rotation gracefully: when a token's kid doesn't match any cached
 * key, the JWKS is re-fetched once. If the kid still doesn't match after a
 * fresh fetch, the token is rejected. This prevents both stale-cache failures
 * and cache-busting DoS attacks.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7517
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

final readonly class JwksClient
{
    private const CACHE_KEY_PREFIX = 'oidc_jwks_';
    private const DEFAULT_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private ClientInterface $httpClient,
        private CacheInterface $cache,
        private int $ttlSeconds = self::DEFAULT_TTL_SECONDS,
    ) {
    }

    /**
     * Get a signing key by kid from the given JWKS URI.
     *
     * On a cache miss for the kid, re-fetches the JWKS once (key rotation
     * handling). If the kid is still not found, throws.
     *
     * @throws JwksException If the kid is not found or the JWKS cannot be fetched.
     */
    public function getSigningKey(string $jwksUri, string $kid): JsonWebKey
    {
        $keys = $this->getKeys($jwksUri);

        $key = $this->findSigningKey($keys, $kid);
        if ($key !== null) {
            return $key;
        }

        // kid not in cache — provider may have rotated keys. Refetch once.
        $keys = $this->refreshKeys($jwksUri);

        $key = $this->findSigningKey($keys, $kid);
        if ($key !== null) {
            return $key;
        }

        throw new JwksException("No signing key found for kid '{$kid}'");
    }

    /**
     * Get all keys from a JWKS URI, using cache when available.
     *
     * @return list<JsonWebKey>
     */
    public function getKeys(string $jwksUri): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $jwksUri);

        /** @var list<array<string, mixed>>|null $cached */
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $this->parseKeys($cached);
        }

        return $this->fetchAndCache($jwksUri, $cacheKey);
    }

    /**
     * Force-refresh the JWKS from the provider, bypassing cache.
     *
     * @return list<JsonWebKey>
     */
    public function refreshKeys(string $jwksUri): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $jwksUri);

        return $this->fetchAndCache($jwksUri, $cacheKey);
    }

    /**
     * @return list<JsonWebKey>
     */
    private function fetchAndCache(string $jwksUri, string $cacheKey): array
    {
        $rawKeys = $this->fetchJwks($jwksUri);
        $keys = $this->parseKeys($rawKeys);

        $this->cache->set($cacheKey, $rawKeys, $this->ttlSeconds);

        return $keys;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchJwks(string $jwksUri): array
    {
        try {
            $request = new Request('GET', $jwksUri);
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new JwksException('Failed to fetch JWKS', 0, $e);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new JwksException("JWKS endpoint returned HTTP {$statusCode}");
        }

        $body = $response->getBody()->getContents();

        try {
            $document = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JwksException('JWKS response is not valid JSON', 0, $e);
        }

        if (!is_array($document) || !isset($document['keys']) || !is_array($document['keys'])) {
            throw new JwksException('JWKS document missing "keys" array');
        }

        /** @var list<array<string, mixed>> */
        return $document['keys'];
    }

    /**
     * @param list<mixed> $rawKeys
     * @return list<JsonWebKey>
     */
    private function parseKeys(array $rawKeys): array
    {
        $keys = [];
        foreach ($rawKeys as $rawKey) {
            if (!is_array($rawKey)) {
                continue;
            }

            try {
                $keys[] = JsonWebKey::fromArray($rawKey);
            } catch (JwksException) {
                // Skip malformed keys — a JWKS may contain keys we don't understand
                continue;
            }
        }

        return $keys;
    }

    /**
     * @param list<JsonWebKey> $keys
     */
    private function findSigningKey(array $keys, string $kid): ?JsonWebKey
    {
        foreach ($keys as $key) {
            if ($key->kid === $kid && $key->isSigningKey()) {
                return $key;
            }
        }

        return null;
    }
}
