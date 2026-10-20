<?php

/**
 * Resolves the set of origins eligible to receive CORS response headers
 * from the REST API, with two scopes:
 *
 * - getAllowedOrigins() / isAllowed() return the union of every enabled
 *   oauth_clients row's redirect_uri origins. Used only for the OPTIONS
 *   preflight path, where the authenticated client is not yet known
 *   (CORS forbids credentials on preflight, so no Authorization header
 *   is attached and the server cannot derive clientId). A lenient
 *   preflight is harmless on its own because the real security gate is
 *   the per-client check on the actual response.
 *
 * - getOriginsForClient() / isAllowedForClient() return only the
 *   authenticated OAuth2 client's own registered redirect_uri origins.
 *   This is the response-side gate: the CORSListener consults it with
 *   the clientId set on request attributes by
 *   BearerTokenAuthorizationStrategy. Session-cookie-authenticated
 *   responses (LocalApiAuthorizationController) and unauthenticated
 *   responses never have a clientId set and so never receive an
 *   Access-Control-Allow-Origin header.
 *
 * Query results are cached per-instance for the lifetime of a request
 * so the CORSListener does not re-query on every response event.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect;

use OpenEMR\Common\Database\QueryUtils;

class CORSAllowedOriginRegistry
{
    /** @var list<string>|null */
    private ?array $cache = null;

    /** @var array<string, list<string>> */
    private array $clientOriginsCache = [];

    public function isAllowed(string $origin): bool
    {
        if ($origin === '' || strcasecmp($origin, 'null') === 0) {
            return false;
        }
        $normalized = self::normalizeOrigin($origin);
        if ($normalized === null) {
            return false;
        }
        return in_array($normalized, $this->getAllowedOrigins(), true);
    }

    /**
     * Response-side gate: is this origin registered under this specific
     * OAuth2 client's redirect_uris? Called by the CORSListener against
     * the clientId set on request attributes by
     * BearerTokenAuthorizationStrategy. A caller's origin is accepted
     * only for a response to a token issued to a client that itself
     * registered that origin -- an attacker's self-registered client
     * can list its origin, but that origin is never paired with a
     * different client's token in a response.
     */
    public function isAllowedForClient(string $clientId, string $origin): bool
    {
        if ($clientId === '' || $origin === '' || strcasecmp($origin, 'null') === 0) {
            return false;
        }
        $normalized = self::normalizeOrigin($origin);
        if ($normalized === null) {
            return false;
        }
        return in_array($normalized, $this->getOriginsForClient($clientId), true);
    }

    /**
     * @return list<string>
     */
    public function getOriginsForClient(string $clientId): array
    {
        if ($clientId === '') {
            return [];
        }
        if (isset($this->clientOriginsCache[$clientId])) {
            return $this->clientOriginsCache[$clientId];
        }
        $origins = [];
        $row = QueryUtils::fetchRecords(
            "SELECT redirect_uri FROM oauth_clients WHERE client_id = ? AND is_enabled = 1 LIMIT 1",
            [$clientId]
        );
        if (isset($row[0])) {
            $value = $row[0]['redirect_uri'] ?? '';
            if (is_string($value) && $value !== '') {
                // oauth_clients.redirect_uri stores one or more URIs separated by "|"
                foreach (explode('|', $value) as $uri) {
                    $normalized = self::normalizeOrigin($uri);
                    if ($normalized !== null) {
                        $origins[$normalized] = true;
                    }
                }
            }
        }
        $this->clientOriginsCache[$clientId] = array_keys($origins);
        return $this->clientOriginsCache[$clientId];
    }

    /**
     * @return list<string>
     */
    public function getAllowedOrigins(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }
        $origins = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT redirect_uri FROM oauth_clients WHERE is_enabled = 1 AND redirect_uri IS NOT NULL AND redirect_uri <> ''",
            []
        );
        foreach ($rows as $row) {
            $value = $row['redirect_uri'] ?? '';
            if (!is_string($value) || $value === '') {
                continue;
            }
            // oauth_clients.redirect_uri stores one or more URIs separated by "|"
            foreach (explode('|', $value) as $uri) {
                $normalized = self::normalizeOrigin($uri);
                if ($normalized !== null) {
                    $origins[$normalized] = true;
                }
            }
        }
        $this->cache = array_keys($origins);
        return $this->cache;
    }

    /**
     * Returns the scheme+host+port authority of a URL in canonical form,
     * omitting the port when it is the default for the scheme. Returns
     * null for inputs that are not parseable as absolute URLs with a
     * scheme and host.
     */
    public static function normalizeOrigin(string $urlOrOrigin): ?string
    {
        $trimmed = trim($urlOrOrigin);
        if ($trimmed === '') {
            return null;
        }
        $parts = parse_url($trimmed);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }
        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $port = null;
        }
        return $scheme . '://' . $host . ($port !== null ? ':' . $port : '');
    }
}
