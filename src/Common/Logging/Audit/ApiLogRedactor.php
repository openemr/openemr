<?php

/**
 * Replaces transient OAuth2 field values with a stable placeholder before
 * they are written to the api_log table.
 *
 * Scoped to the two OAuth2 endpoints whose payloads carry high-churn
 * runtime values that are not useful in a long-lived audit row:
 * - /oauth2/*\/token (RFC 6749)
 * - /oauth2/*\/registration (RFC 7591 Dynamic Client Registration)
 *
 * The listed field values are replaced with "[REDACTED]" rather than
 * removed, so an audit reader can still see which fields were present. All
 * other endpoints pass through unchanged.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class ApiLogRedactor
{
    public const SENTINEL = '[REDACTED]';

    /**
     * Fields whose values are replaced on the OAuth2 token endpoint request
     * body (both JSON and form-encoded forms are handled the same way).
     *
     * @var list<string>
     */
    private const TOKEN_REQUEST_FIELDS = ['password', 'client_secret', 'code_verifier'];

    /**
     * Fields whose values are replaced on the OAuth2 token endpoint response.
     *
     * @var list<string>
     */
    private const TOKEN_RESPONSE_FIELDS = ['access_token', 'refresh_token', 'id_token'];

    /**
     * Fields whose values are replaced on the OAuth2 Dynamic Client
     * Registration response. The request carries only descriptive metadata
     * (redirect_uris, scopes, client_name) and is left as-is.
     *
     * @var list<string>
     */
    private const REGISTRATION_RESPONSE_FIELDS = ['client_secret', 'registration_access_token'];

    public function redactRequest(string $url, string $body): string
    {
        if ($body === '') {
            return '';
        }
        if (self::isTokenUrl($url)) {
            return self::redactKeys($body, self::TOKEN_REQUEST_FIELDS);
        }
        return $body;
    }

    public function redactResponse(string $url, string $body): string
    {
        if ($body === '') {
            return '';
        }
        if (self::isTokenUrl($url)) {
            return self::redactKeys($body, self::TOKEN_RESPONSE_FIELDS);
        }
        if (self::isRegistrationUrl($url)) {
            return self::redactKeys($body, self::REGISTRATION_RESPONSE_FIELDS);
        }
        return $body;
    }

    private static function isTokenUrl(string $url): bool
    {
        return str_contains(self::stripQuery($url), '/oauth2/')
            && str_ends_with(self::stripQuery($url), '/token');
    }

    private static function isRegistrationUrl(string $url): bool
    {
        return str_contains(self::stripQuery($url), '/oauth2/')
            && str_ends_with(self::stripQuery($url), '/registration');
    }

    private static function stripQuery(string $url): string
    {
        $queryStart = strpos($url, '?');
        return $queryStart === false ? $url : substr($url, 0, $queryStart);
    }

    /**
     * Replaces the value of every listed key with SENTINEL. Handles both JSON
     * object bodies and application/x-www-form-urlencoded bodies — a token
     * endpoint accepts either per RFC 6749 and we may be asked to redact
     * whichever shape was actually sent.
     *
     * @param list<string> $keys
     */
    private static function redactKeys(string $body, array $keys): string
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $redactedAny = false;
            foreach ($keys as $key) {
                if (array_key_exists($key, $decoded)) {
                    $decoded[$key] = self::SENTINEL;
                    $redactedAny = true;
                }
            }
            if (!$redactedAny) {
                return $body;
            }
            $encoded = json_encode($decoded);
            return $encoded === false ? $body : $encoded;
        }

        // parse_str is permissive — it will produce a non-empty array for any
        // non-empty string, including plain prose. Only re-encode via
        // http_build_query when we actually need to redact something, so that
        // bodies with no sensitive keys round-trip unchanged.
        parse_str($body, $parsed);
        $redactedAny = false;
        foreach ($keys as $key) {
            if (array_key_exists($key, $parsed)) {
                $parsed[$key] = self::SENTINEL;
                $redactedAny = true;
            }
        }
        return $redactedAny ? http_build_query($parsed) : $body;
    }
}
