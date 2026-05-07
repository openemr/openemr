<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\TrustedProxyClientIpResolver;
use PHPUnit\Framework\TestCase;

final class TrustedProxyClientIpResolverTest extends TestCase
{
    // -- Empty config (no trusted proxies): preserves round-2 #3 behavior --

    public function testEmptyTrustedProxiesReturnsRemoteAddr(): void
    {
        $resolver = new TrustedProxyClientIpResolver();

        self::assertSame('203.0.113.7', $resolver->resolveClientIp('203.0.113.7'));
    }

    public function testEmptyTrustedProxiesIgnoresXffEvenWhenPresent(): void
    {
        // (CWE-807) protection: with no trusted proxies, an
        // attacker-supplied XFF must NOT influence the rate-limit key.
        $resolver = new TrustedProxyClientIpResolver();

        self::assertSame(
            '203.0.113.7',
            $resolver->resolveClientIp('203.0.113.7', '1.2.3.4, 5.6.7.8'),
        );
    }

    public function testEmptyRemoteAddrReturnsEmpty(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        self::assertSame('', $resolver->resolveClientIp(''));
    }

    // -- REMOTE_ADDR not in trusted list: ignore XFF --

    public function testUntrustedRemoteAddrReturnsRemoteAddr(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        // 203.0.113.7 is NOT in 10.0.0.0/8 — XFF is discarded.
        self::assertSame(
            '203.0.113.7',
            $resolver->resolveClientIp('203.0.113.7', '1.2.3.4'),
        );
    }

    // -- Trusted proxy + XFF: parse the chain --

    public function testTrustedRemoteAddrWithSingleHopXffReturnsClient(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        // CDN at 10.0.0.5 forwarded the original client 198.51.100.42.
        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('10.0.0.5', '198.51.100.42'),
        );
    }

    public function testTrustedRemoteAddrSkipsTrustedHopsRightToLeft(): void
    {
        // Multi-tier: client -> CDN edge -> internal LB -> app.
        // REMOTE_ADDR is the LB (10.0.0.5); the CDN edge (192.168.1.10)
        // is also trusted; the actual client is 198.51.100.42. Walking
        // right-to-left, we skip 192.168.1.10 (trusted) and return
        // 198.51.100.42.
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8', '192.168.0.0/16']);

        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('10.0.0.5', '198.51.100.42, 192.168.1.10'),
        );
    }

    public function testTrustedRemoteAddrIgnoresSpoofedLeftEntries(): void
    {
        // Attacker prepends "1.2.3.4" to XFF; the trusted edge appended
        // the real client 198.51.100.42 to the right. Right-to-left walk
        // returns the right-most untrusted IP — the real one. The
        // attacker's prepended entry is NEVER returned.
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('10.0.0.5', '1.2.3.4, 198.51.100.42'),
        );
    }

    public function testTrustedRemoteAddrWithEmptyXffFallsBackToRemoteAddr(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        self::assertSame('10.0.0.5', $resolver->resolveClientIp('10.0.0.5', ''));
        self::assertSame('10.0.0.5', $resolver->resolveClientIp('10.0.0.5', null));
    }

    public function testWholeXffChainTrustedFallsBackToRemoteAddr(): void
    {
        // Chain consists entirely of trusted proxies (no real client
        // forwarded — misconfigured proxy chain). Defensive fall-back
        // to REMOTE_ADDR rather than guessing.
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        self::assertSame(
            '10.0.0.5',
            $resolver->resolveClientIp('10.0.0.5', '10.0.0.10, 10.0.0.20'),
        );
    }

    public function testInvalidIpInChainIsSkipped(): void
    {
        // A garbled XFF entry (typo, malicious payload) is skipped during
        // the right-to-left walk; the next valid entry is used.
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('10.0.0.5', '198.51.100.42, not-an-ip'),
        );
    }

    public function testIpv6InChain(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['fc00::/7']);

        self::assertSame(
            '2001:db8::1',
            $resolver->resolveClientIp('fc00::5', '2001:db8::1'),
        );
    }

    // -- Config string parsing --

    public function testFromConfigStringParsesCommaSeparated(): void
    {
        $resolver = TrustedProxyClientIpResolver::fromConfigString('10.0.0.0/8, 192.168.1.5 ,  172.16.0.0/12');

        // Each of the three configured proxies should be recognized.
        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('192.168.1.5', '198.51.100.42'),
        );
        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('172.16.0.10', '198.51.100.42'),
        );
        self::assertSame(
            '198.51.100.42',
            $resolver->resolveClientIp('10.0.0.5', '198.51.100.42'),
        );
    }

    public function testFromConfigStringEmptyMakesPassThroughResolver(): void
    {
        $resolver = TrustedProxyClientIpResolver::fromConfigString('');

        self::assertSame(
            '203.0.113.7',
            $resolver->resolveClientIp('203.0.113.7', '1.2.3.4'),
        );
    }

    public function testFromConfigStringWhitespaceOnlyMakesPassThroughResolver(): void
    {
        $resolver = TrustedProxyClientIpResolver::fromConfigString("   \t  ");

        self::assertSame(
            '203.0.113.7',
            $resolver->resolveClientIp('203.0.113.7', '1.2.3.4'),
        );
    }
}
