<?php

/**
 * Resolves the "real" client IP for rate-limiting decisions when the
 * server may sit behind one or more reverse proxies / load balancers.
 *
 * Round-2 finding #3 (CWE-807) tightened login rate-limiting to key on
 * `REMOTE_ADDR` only, which prevents an attacker from rotating
 * `X-Forwarded-For` to bypass the bucket. Round-3 finding #4 (CWE-400)
 * pointed out the symmetric problem: in proxy / CDN / NAT deployments,
 * `REMOTE_ADDR` is the proxy's egress IP shared by every client, so
 * one bad actor can lock out everyone behind the same proxy.
 *
 * This resolver closes the gap. When the immediate peer (`REMOTE_ADDR`)
 * matches a configured trusted-proxy entry, `X-Forwarded-For` is
 * walked right-to-left — skipping further trusted hops — to find the
 * first untrusted, valid IP. That's the rate-limit key. With no
 * trusted proxies configured the resolver returns `REMOTE_ADDR`
 * unchanged, preserving round-2 #3's "no XFF trust" stance.
 *
 * The right-to-left walk is deliberate: an attacker can prepend
 * arbitrary entries to `X-Forwarded-For`, but cannot remove or modify
 * the entries appended by trusted proxies they don't control. The
 * right-most untrusted entry is therefore the closest the server can
 * get to the real client.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

use Symfony\Component\HttpFoundation\IpUtils;

final readonly class TrustedProxyClientIpResolver
{
    /** @var list<string> */
    private array $trustedProxies;

    /**
     * @param list<string> $trustedProxies CIDR notation or IP literals.
     *   Empty list disables proxy parsing entirely (the resolver becomes
     *   a no-op pass-through of `REMOTE_ADDR`).
     */
    public function __construct(array $trustedProxies = [])
    {
        // Drop empty/whitespace entries that might appear when parsing a
        // user-supplied comma-separated config string.
        $this->trustedProxies = array_values(array_filter(
            array_map(trim(...), $trustedProxies),
            static fn(string $entry): bool => $entry !== '',
        ));
    }

    /**
     * Parse a comma-separated config string (e.g. "10.0.0.0/8, 192.168.1.5")
     * into the constructor shape. Convenience for call sites that read
     * the trusted-proxy list from a global setting.
     */
    public static function fromConfigString(string $config): self
    {
        if (trim($config) === '') {
            return new self();
        }
        return new self(explode(',', $config));
    }

    /**
     * Return the IP address that should be used as the client identity
     * for rate-limiting (or any other security-decision keyed on the
     * caller's network identity).
     *
     * @param string      $remoteAddr    `$_SERVER['REMOTE_ADDR']` (or equivalent).
     * @param string|null $forwardedFor  `$_SERVER['HTTP_X_FORWARDED_FOR']` raw value, or null when absent.
     */
    public function resolveClientIp(string $remoteAddr, ?string $forwardedFor = null): string
    {
        if ($remoteAddr === '') {
            return '';
        }

        // No trusted proxies configured (or REMOTE_ADDR isn't one of them):
        // the immediate peer IS the rate-limit identity. Discard XFF
        // because it's attacker-controlled in this scenario.
        if (!$this->isTrustedProxy($remoteAddr)) {
            return $remoteAddr;
        }

        // REMOTE_ADDR is a trusted proxy. Walk the XFF chain right-to-left,
        // skipping further trusted hops, return the first untrusted, valid
        // IP. This handles multi-proxy chains (e.g. CDN -> LB -> app).
        if ($forwardedFor === null || $forwardedFor === '') {
            return $remoteAddr;
        }

        $hops = array_map(trim(...), explode(',', $forwardedFor));
        for ($i = count($hops) - 1; $i >= 0; $i--) {
            $candidate = $hops[$i];
            if ($candidate === '' || filter_var($candidate, FILTER_VALIDATE_IP) === false) {
                continue;
            }
            if (!$this->isTrustedProxy($candidate)) {
                return $candidate;
            }
        }

        // Whole chain was trusted (or empty after filtering). Defensive
        // fallback to REMOTE_ADDR rather than guessing.
        return $remoteAddr;
    }

    private function isTrustedProxy(string $ip): bool
    {
        if ($this->trustedProxies === []) {
            return false;
        }
        return IpUtils::checkIp($ip, $this->trustedProxies);
    }
}
