<?php

/**
 * Resolves the set of origins eligible to receive CORS response headers
 * from the REST API. An origin is eligible when the scheme+host+port
 * portion matches the authority of a registered redirect_uri belonging
 * to an enabled oauth_clients row.
 *
 * Query result is cached per-instance for the lifetime of a request so
 * the CORSListener does not re-query on every response event.
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
