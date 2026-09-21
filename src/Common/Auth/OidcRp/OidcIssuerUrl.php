<?php

/**
 * Validates issuer and related OIDC endpoint URLs before fetching them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

final class OidcIssuerUrl
{
    /**
     * @var list<string>
     */
    private const BLOCKED_HOSTS = [
        'metadata.google.internal',
        'metadata.google.internal.',
        '169.254.169.254',
    ];

    public static function assertSafe(string $url, bool $allowHttp, ?string $expectedHost = null): void
    {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new OidcRpException('OIDC URL is not valid');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '') {
            throw new OidcRpException('OIDC URL is missing a host');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new OidcRpException('OIDC URL must not contain credentials');
        }

        if ($scheme === 'http') {
            if (!$allowHttp) {
                throw new OidcRpException('OIDC issuer must use HTTPS');
            }
            if (!self::isLoopbackHost($host)) {
                throw new OidcRpException('HTTP OIDC URLs are limited to loopback hosts');
            }
        } elseif ($scheme !== 'https') {
            throw new OidcRpException('OIDC URL must use HTTP or HTTPS');
        }

        // IPv4 link-local is the 169.254. prefix parse_url() returns (not
        // decimal or IPv6-mapped encodings). IPv6 link-local is fe80::/10.
        // Discovery endpoints are also pinned to the issuer host.
        if (self::isBlockedHost($host)) {
            throw new OidcRpException('OIDC URL host is not allowed');
        }

        if ($expectedHost !== null && $host !== strtolower($expectedHost)) {
            throw new OidcRpException('OIDC endpoint host must match the configured issuer');
        }
    }

    public static function hostOf(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            throw new OidcRpException('OIDC URL is missing a host');
        }
        return strtolower($host);
    }

    private static function isBlockedHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));
        if (in_array($host, self::BLOCKED_HOSTS, true) || str_starts_with($host, '169.254.')) {
            return true;
        }

        return self::isLinkLocalIp($host);
    }

    /**
     * Link-local for either family, compared on the parsed address.
     *
     * A textual prefix test does not express fe80::/10: fe90::1 and febf::1 sit
     * in that range but do not start with "fe80:". An IPv4-mapped address such
     * as ::ffff:169.254.169.254 is likewise link-local while matching neither
     * the "169.254." nor the IPv6 prefix test.
     */
    private static function isLinkLocalIp(string $host): bool
    {
        $packed = @inet_pton($host);
        if ($packed === false) {
            return false;
        }

        if (strlen($packed) === 4) {
            return $packed[0] === "\xa9" && $packed[1] === "\xfe";
        }

        if (strlen($packed) !== 16) {
            return false;
        }

        // ::ffff:0:0/96 carries an IPv4 address in the last four bytes.
        if (str_starts_with($packed, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff")) {
            return $packed[12] === "\xa9" && $packed[13] === "\xfe";
        }

        // fe80::/10 -- the top ten bits are 1111 1110 10.
        return ord($packed[0]) === 0xfe && (ord($packed[1]) & 0xc0) === 0x80;
    }

    private static function isLoopbackHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));
        if ($host === 'localhost' || $host === 'localhost.') {
            return true;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && str_starts_with($host, '127.')) {
            return true;
        }

        return $host === '::1';
    }
}
