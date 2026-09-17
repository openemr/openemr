<?php

/**
 * Validates issuer and related OIDC endpoint URLs before fetching them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
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
        } elseif ($scheme !== 'https') {
            throw new OidcRpException('OIDC URL must use HTTP or HTTPS');
        }

        if (in_array($host, self::BLOCKED_HOSTS, true) || str_starts_with($host, '169.254.')) {
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
}
