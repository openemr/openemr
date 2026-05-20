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
    /**
     * Hard cap on the raw `X-Forwarded-For` header bytes parsed per
     * request (Aisle round-4 #7 / CWE-400). Bounds the memory cost
     * of `explode()` before the resulting array exists. Real-world
     * XFF rarely exceeds ~200 bytes; 4 KB leaves comfortable margin
     * even for IPv6-heavy chains with verbose proxy stacks.
     */
    private const MAX_XFF_HEADER_BYTES = 4096;

    /**
     * Hard cap on the number of XFF hops the right-to-left walk
     * iterates per request. Bounds CPU cost when a 4 KB header is
     * densely packed with single-character garbage (e.g. `a,a,a,…`
     * explodes into >2000 entries). 20 covers extreme architectures
     * (CDN → WAF → ALB → app gateway → app) with comfortable margin.
     * Slicing keeps the *rightmost* entries: hops closest to us are
     * the hardest to spoof and the most reliable to retain when
     * truncating.
     */
    private const MAX_XFF_HOPS = 20;

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

        // Round-4 #7 (CWE-400). Bound the parse cost before doing any
        // other work. The byte cap limits the array `explode` allocates;
        // the hop slice limits the iteration cost of the right-to-left
        // walk below. Both fire only on adversarially-shaped input —
        // real XFF chains have at most a handful of entries.
        if (strlen($forwardedFor) > self::MAX_XFF_HEADER_BYTES) {
            $forwardedFor = substr($forwardedFor, -self::MAX_XFF_HEADER_BYTES);
            // Truncation rarely lands on a comma boundary; the leading
            // fragment after substr may be the tail half of an IP. The
            // filter_var guard inside the walk would discard it anyway,
            // but trimming it here removes a parsed-then-rejected entry
            // from the hop slice and the hop count.
            $firstComma = strpos($forwardedFor, ',');
            if ($firstComma !== false) {
                $forwardedFor = substr($forwardedFor, $firstComma + 1);
            }
        }
        $hops = array_slice(
            array_map(trim(...), explode(',', $forwardedFor)),
            -self::MAX_XFF_HOPS,
        );
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
