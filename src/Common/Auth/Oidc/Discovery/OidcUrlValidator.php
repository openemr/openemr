<?php

/**
 * SSRF safety gate for outbound OIDC HTTP requests (discovery URL, jwks_uri).
 *
 * Production wiring should construct this with both flags enabled. The dev
 * composition root flips them off so the docker oidc-mock service (plain HTTP
 * on a private docker hostname) keeps working.
 *
 * Checks performed:
 *  - URL is parseable and has a host
 *  - scheme is https (or http when {@see $requireHttps} is false)
 *  - URL has no userinfo component (`user:pass@host`)
 *  - host matches an optional expected host (case-insensitive)
 *  - host does not resolve to a private/loopback/link-local/reserved IP range
 *    (only when {@see $blockPrivateIps} is true)
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

final readonly class OidcUrlValidator
{
    public function __construct(
        private bool $requireHttps = true,
        private bool $blockPrivateIps = true,
    ) {
    }

    /**
     * Validate the URL of an OIDC discovery document
     * (`/.well-known/openid-configuration`). No host pinning; the issuer URL
     * itself is the host of record.
     *
     * @throws OidcUrlValidationException When any policy check fails.
     */
    public function validateDiscoveryUrl(string $discoveryUrl): void
    {
        $this->validate($discoveryUrl);
    }

    /**
     * Validate a `jwks_uri`. When an issuer URL is supplied, the jwks_uri host
     * must match the issuer host (case-insensitive) — this is the SSRF guard
     * against a discovery document that points jwks_uri at an attacker host.
     * Callers without an issuer (e.g. admin-registered client JWKS) pass null.
     *
     * @throws OidcUrlValidationException When any policy check fails.
     */
    public function validateJwksUri(string $jwksUri, ?string $issuerUrl = null): void
    {
        $expectedHost = null;
        if ($issuerUrl !== null) {
            $host = parse_url($issuerUrl, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $expectedHost = $host;
            }
        }

        $this->validate($jwksUri, $expectedHost);
    }

    /**
     * @param string $url The URL to validate before issuing an outbound request.
     * @param string|null $expectedHost If non-null, the URL host must match exactly (case-insensitive).
     * @throws OidcUrlValidationException When any policy check fails.
     */
    private function validate(string $url, ?string $expectedHost = null): void
    {
        if ($url === '') {
            throw new OidcUrlValidationException('URL is required');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            throw new OidcUrlValidationException('URL is malformed');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if ($this->requireHttps) {
            if ($scheme !== 'https') {
                throw new OidcUrlValidationException('URL must use https');
            }
        } else {
            if ($scheme !== 'https' && $scheme !== 'http') {
                throw new OidcUrlValidationException('URL must use http or https');
            }
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new OidcUrlValidationException('URL must not contain credentials');
        }

        $host = $parts['host'] ?? '';
        if ($host === '') {
            throw new OidcUrlValidationException('URL host is required');
        }

        if ($expectedHost !== null && strcasecmp($host, $expectedHost) !== 0) {
            throw new OidcUrlValidationException('URL host does not match expected host');
        }

        if ($this->blockPrivateIps) {
            $this->assertNoPrivateIp($host);
        }
    }

    private function assertNoPrivateIp(string $host): void
    {
        // IPv6 literals come back from parse_url with surrounding brackets.
        $bareHost = (str_starts_with($host, '[') && str_ends_with($host, ']'))
            ? substr($host, 1, -1)
            : $host;

        if (filter_var($bareHost, FILTER_VALIDATE_IP) !== false) {
            if ($this->isPrivateOrLocalIp($bareHost)) {
                throw new OidcUrlValidationException('URL host is a private/local address');
            }
            return;
        }

        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if ($records === false || $records === []) {
            throw new OidcUrlValidationException('URL host could not be resolved');
        }

        foreach ($records as $rec) {
            $ip = null;
            if (isset($rec['ip']) && is_string($rec['ip'])) {
                $ip = $rec['ip'];
            } elseif (isset($rec['ipv6']) && is_string($rec['ipv6'])) {
                $ip = $rec['ipv6'];
            }
            if ($ip !== null && $this->isPrivateOrLocalIp($ip)) {
                throw new OidcUrlValidationException('URL host resolves to a private/local address');
            }
        }
    }

    private function isPrivateOrLocalIp(string $ip): bool
    {
        // FILTER_FLAG_NO_PRIV_RANGE blocks RFC1918 + IPv6 ULA (fc00::/7).
        // FILTER_FLAG_NO_RES_RANGE blocks loopback (127/8, ::1), link-local
        // (169.254/16, fe80::/10), multicast, and reserved ranges including
        // the cloud metadata IP.
        $valid = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );
        return $valid === false;
    }
}
