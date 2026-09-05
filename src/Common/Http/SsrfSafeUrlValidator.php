<?php

/**
 * SsrfSafeUrlValidator — allowlist-based validator for outbound URLs.
 *
 * Enforces a scheme allowlist (http/https by default) and rejects hosts that
 * point at loopback, private, link-local, or cloud-metadata endpoints. Includes
 * a DNS-resolution check so a public-looking hostname that resolves to a
 * private/metadata address is rejected as well.
 *
 * Designed as a shared building block for OAuth URL fields (jwks_uri today;
 * sector_identifier_uri, redirect_uris, request_uris, initiate_login_uri in
 * follow-up work) and any other outbound-HTTP acceptance path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

use Symfony\Component\HttpFoundation\IpUtils;

class SsrfSafeUrlValidator
{
    public const REASON_EMPTY = 'url is empty';
    public const REASON_MALFORMED = 'url is malformed';
    public const REASON_MISSING_SCHEME = 'url is missing a scheme';
    public const REASON_MISSING_HOST = 'url is missing a host';
    public const REASON_DISALLOWED_SCHEME = 'url scheme is not on the allowlist';
    public const REASON_USERINFO = 'url contains userinfo which is not permitted';
    public const REASON_LOOPBACK = 'url host is a loopback address';
    public const REASON_PRIVATE_NETWORK = 'url host is a private-network address';
    public const REASON_LINK_LOCAL = 'url host is a link-local address';
    public const REASON_CLOUD_METADATA = 'url host is a cloud metadata endpoint';
    public const REASON_RESERVED = 'url host is a reserved address';
    public const REASON_DNS_UNRESOLVABLE = 'url host does not resolve to any address';
    public const REASON_DNS_UNSAFE = 'url host resolves to a non-public address';

    /**
     * Cloud metadata service hostnames (lowercased, no scheme, no port).
     * Kept as a class constant so tests and future integrations can reference
     * the same set — every new cloud provider metadata endpoint needs to be
     * added here (and to CLOUD_METADATA_IPS when applicable).
     *
     * @var list<string>
     */
    private const CLOUD_METADATA_HOSTNAMES = [
        'metadata.google.internal',
        'metadata.goog',
        'metadata.azure.com',
        'metadata',
    ];

    /**
     * Cloud metadata service IPv4 literals. Any hostname that resolves to one
     * of these addresses is also rejected via the DNS-resolution check. Matched
     * with `IpUtils::checkIp`, so bare literals (implicit /32) and CIDR blocks
     * can coexist here if a provider ever publishes a range.
     *
     * @var list<string>
     */
    private const CLOUD_METADATA_IPS = [
        '169.254.169.254', // AWS, GCP, Azure, DigitalOcean, Oracle, OpenStack
        '100.100.100.200', // Alibaba Cloud
    ];

    /**
     * CIDR ranges per audit-reason category. Each list is matched with
     * `IpUtils::checkIp`, which handles v4 and v6 in one call and skips any
     * entry whose family does not match the address under test.
     *
     * @var list<string>
     */
    private const LOOPBACK_RANGES = [
        '127.0.0.0/8', // IPv4 loopback
        '0.0.0.0/8',   // IPv4 unspecified (many stacks route to loopback)
        '::1/128',     // IPv6 loopback
        '::/128',      // IPv6 unspecified
    ];

    /** @var list<string> */
    private const LINK_LOCAL_RANGES = [
        '169.254.0.0/16', // IPv4 link-local (covers 169.254.169.254 too, but
                          // CLOUD_METADATA_IPS is checked first for clearer audit reason)
        'fe80::/10',      // IPv6 link-local
    ];

    /** @var list<string> */
    private const PRIVATE_RANGES = [
        '10.0.0.0/8',     // RFC1918
        '172.16.0.0/12',  // RFC1918
        '192.168.0.0/16', // RFC1918
        'fc00::/7',       // IPv6 unique-local (ULA)
    ];

    /**
     * @param list<string> $allowedSchemes Lowercase scheme names.
     * @param bool $resolveDns When true, resolve the hostname and reject if any
     *   resolved address is unsafe. Set to false only in narrow contexts (unit
     *   tests, deployments where outbound DNS is intentionally scoped).
     */
    public function __construct(
        private readonly array $allowedSchemes = ['http', 'https'],
        private readonly bool $resolveDns = true,
    ) {
    }

    /**
     * Validate an outbound URL.
     *
     * @return string|null Null when the URL is safe to fetch; a descriptive
     *   reason string when the URL should be rejected. The reason is suitable
     *   for audit logging but should NOT be echoed verbatim to unauthenticated
     *   callers because it may indicate internal network topology.
     */
    public function validate(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return self::REASON_EMPTY;
        }

        $parts = parse_url($trimmed);
        if ($parts === false) {
            return self::REASON_MALFORMED;
        }

        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        if ($scheme === '') {
            return self::REASON_MISSING_SCHEME;
        }
        if (!in_array($scheme, $this->allowedSchemes, true)) {
            return self::REASON_DISALLOWED_SCHEME;
        }

        // Userinfo (`http://user:pass@host/`) obscures the effective host in
        // logs and is not needed for a JWKS endpoint. Reject to keep the sink
        // free of embedded credentials in URLs.
        if (isset($parts['user']) || isset($parts['pass'])) {
            return self::REASON_USERINFO;
        }

        $host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
        if ($host === '') {
            return self::REASON_MISSING_HOST;
        }

        // Strip IPv6 brackets before ip literal checks.
        $bareHost = $host;
        if (str_starts_with($bareHost, '[') && str_ends_with($bareHost, ']')) {
            $bareHost = substr($bareHost, 1, -1);
        }

        $literalReason = $this->classifyLiteral($bareHost);
        if ($literalReason !== null) {
            return $literalReason;
        }

        // Not an IP literal — a hostname. If the caller opted in, resolve it
        // and re-check each resolved address so a public-looking name that
        // points into RFC1918 or the metadata service is rejected.
        if ($this->resolveDns && !$this->isIpLiteral($bareHost)) {
            $dnsReason = $this->classifyResolvedHost($bareHost);
            if ($dnsReason !== null) {
                return $dnsReason;
            }
        }

        return null;
    }

    /**
     * Classify a bare host string. Returns a reason if the host itself (as a
     * literal name or IP) is on the rejection list; null otherwise. Does NOT
     * perform DNS resolution.
     */
    private function classifyLiteral(string $bareHost): ?string
    {
        if (in_array($bareHost, self::CLOUD_METADATA_HOSTNAMES, true)) {
            return self::REASON_CLOUD_METADATA;
        }

        // Explicit loopback name check — `localhost` never resolves through
        // DNS on many hosts and would otherwise slip past the resolver step.
        if ($bareHost === 'localhost' || str_ends_with($bareHost, '.localhost')) {
            return self::REASON_LOOPBACK;
        }

        if (!$this->isIpLiteral($bareHost)) {
            return null;
        }

        return $this->classifyIp($bareHost);
    }

    /**
     * Classify a single IP address literal (v4 or v6). Returns a reason string
     * for unsafe addresses, null for public / routable addresses.
     *
     * Delegates range membership to `IpUtils::checkIp` for each audit-reason
     * category so all v4/v6 CIDR matching lives in one battle-tested Symfony
     * primitive. `filter_var(FILTER_FLAG_NO_RES_RANGE)` remains the fallback
     * for reserved ranges (documentation, 6to4 relay, etc.) that Symfony's
     * `isPrivateIp` does not cover.
     */
    private function classifyIp(string $ip): ?string
    {
        // Canonicalize (drops leading zeros in v4, expands v6 shortcuts) and
        // unwrap IPv4-mapped IPv6 (`::ffff:x.x.x.x`) so a mapped-v4 loopback
        // gets classified against the v4 CIDR lists below rather than falling
        // through as a public v6 address.
        $normalized = $this->normalizeIp($ip);
        if ($normalized === null) {
            return null;
        }

        if (IpUtils::checkIp($normalized, self::CLOUD_METADATA_IPS)) {
            return self::REASON_CLOUD_METADATA;
        }
        if (IpUtils::checkIp($normalized, self::LOOPBACK_RANGES)) {
            return self::REASON_LOOPBACK;
        }
        if (IpUtils::checkIp($normalized, self::LINK_LOCAL_RANGES)) {
            return self::REASON_LINK_LOCAL;
        }
        if (IpUtils::checkIp($normalized, self::PRIVATE_RANGES)) {
            return self::REASON_PRIVATE_NETWORK;
        }

        // Reserved ranges (documentation, 6to4 relay, broadcast, etc.) aren't
        // covered by any of the above CIDR lists. The address has already
        // survived the RFC1918/ULA/loopback/link-local checks, so if it still
        // fails the built-in reserved-range filter it is a reserved slice.
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (filter_var($normalized, FILTER_VALIDATE_IP, $flags) === false) {
            return self::REASON_RESERVED;
        }
        return null;
    }

    /**
     * Canonicalize an IP literal and unwrap IPv4-mapped IPv6. Returns null
     * when `inet_pton` cannot parse the input (which means the caller passed
     * something that wasn't an IP literal after all).
     */
    private function normalizeIp(string $ip): ?string
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return null;
        }
        // IPv4-mapped IPv6 (`::ffff:0:0/96`): unwrap so the v4 CIDR lists match.
        if (strlen($packed) === 16 && substr($packed, 0, 12) === "\0\0\0\0\0\0\0\0\0\0\xff\xff") {
            $v4 = inet_ntop(substr($packed, 12));
            return is_string($v4) ? $v4 : null;
        }
        $canonical = inet_ntop($packed);
        return is_string($canonical) ? $canonical : null;
    }

    private function isIpLiteral(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Resolve $host and reject if any address is unsafe.
     *
     * Delegates the actual resolver calls to `resolveIpv4` / `resolveIpv6`
     * hooks that subclasses can override for tests. Each returns a list of
     * IP-literal strings; every literal is classified with the same rules
     * used for direct IP-literal input, so the accept/reject decision is
     * identical no matter how the host was expressed.
     */
    private function classifyResolvedHost(string $host): ?string
    {
        $ipv4Addresses = $this->resolveIpv4($host);
        $ipv6Addresses = $this->resolveIpv6($host);

        if ($ipv4Addresses === [] && $ipv6Addresses === []) {
            return self::REASON_DNS_UNRESOLVABLE;
        }

        foreach ($ipv4Addresses as $ip) {
            $reason = $this->classifyIp($ip);
            if ($reason !== null) {
                return self::REASON_DNS_UNSAFE;
            }
        }
        foreach ($ipv6Addresses as $ip) {
            $reason = $this->classifyIp($ip);
            if ($reason !== null) {
                return self::REASON_DNS_UNSAFE;
            }
        }

        return null;
    }

    /**
     * Resolve $host to IPv4 addresses via `gethostbynamel`. Overridable so
     * tests can inject fixed addresses (see
     * SsrfSafeUrlValidatorIsolatedTest).
     *
     * @return list<string>
     */
    protected function resolveIpv4(string $host): array
    {
        $ipv4List = @gethostbynamel($host);
        return is_array($ipv4List) ? $ipv4List : [];
    }

    /**
     * Resolve $host to IPv6 addresses via `dns_get_record(..., DNS_AAAA)`.
     * Overridable so tests can inject fixed addresses.
     *
     * @return list<string>
     */
    protected function resolveIpv6(string $host): array
    {
        $addresses = [];
        // Suppress warnings on lookup failure — treat as "no records".
        $aaaa = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaa)) {
            foreach ($aaaa as $record) {
                if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }
        return $addresses;
    }
}
