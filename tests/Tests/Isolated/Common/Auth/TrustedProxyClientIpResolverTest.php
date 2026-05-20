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

    /**
     * Aisle round-4 #7 (CWE-400) + round-5 #2 (CWE-807) regression —
     * the byte cap must keep the *right* side of the header. XFF
     * semantics put the original client at the LEFT and each proxy
     * appends to the RIGHT; the rightmost entries are the closest
     * to us and the hardest to spoof. An earlier shape of the byte
     * cap kept the left side (`substr(0, MAX)`), which let an
     * attacker who could send a >4 KB header through a trusted
     * proxy push the legitimate proxy-appended tail off the right
     * edge, then fill the kept left portion with their own IPs.
     *
     * This test packs the leftmost ~6 KB with trusted-proxy entries
     * and appends the legitimate untrusted client IP at the very
     * right. Pre-fix: trailing IP got truncated off, walk fell back
     * to REMOTE_ADDR. Post-fix: trailing IP is kept (rightmost
     * 4 KB preserved), walk finds it and returns it.
     */
    public function testTruncatesOversizedXffHeaderKeepsRightmostTail(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        // ~6 KB of trusted-proxy entries — well above MAX_XFF_HEADER_BYTES.
        // Each entry is "10.0.0.5, " (~10 bytes), so 600 entries ≈ 6 KB.
        $padding = implode(', ', array_fill(0, 600, '10.0.0.5'));
        $oversizedXff = $padding . ', 198.51.100.42';

        $resolved = $resolver->resolveClientIp('10.0.0.5', $oversizedXff);

        // Rightmost 4 KB preserved → the trailing legit client IP
        // is still in the parsed hops → walk returns it.
        self::assertSame('198.51.100.42', $resolved);
    }

    /**
     * Aisle round-5 #2 (CWE-807) — the security-relevant scenario
     * the truncation flip protects against. An attacker bombs
     * `X-Forwarded-For` with their own *untrusted* IP repeated
     * thousands of times; the proxy chain forwards (rather than
     * sanitizes) the header and appends its own legitimate hops at
     * the right.
     *
     * Pre-fix layout (bug):
     *   [attacker.ip × N]  + ", real_client, trusted_proxy"
     *   `substr(0, 4096)` keeps the left chunk → walker sees only
     *   attacker.ip → returns attacker.ip → rate-limit bucket
     *   binds to the attacker's choice instead of the real client.
     *
     * Post-fix:
     *   `substr(-4096)` keeps the legitimate chain → walker
     *   returns real_client. Pin this directly.
     */
    public function testRejectsAttackerStuffedLeftPad(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        // ~6 KB of attacker-controlled "untrusted" IP (203.0.113.99
        // is in TEST-NET-3 — public-routable shape, not trusted).
        $attackerPad = implode(', ', array_fill(0, 600, '203.0.113.99'));
        // Realistic chain at the right: legit client, then the
        // trusted proxy that appended its peer's IP last.
        $oversizedXff = $attackerPad . ', 198.51.100.42, 10.0.0.5';

        $resolved = $resolver->resolveClientIp('10.0.0.5', $oversizedXff);

        // Walk right-to-left through the kept tail: 10.0.0.5 (trusted,
        // skip), 198.51.100.42 (untrusted, return). Attacker's
        // 203.0.113.99 spam never reaches the walk because the
        // truncation now drops the LEFT side, not the right. A
        // regression that flips the truncation back would land
        // 203.0.113.99 here and break this assertion loudly.
        self::assertSame('198.51.100.42', $resolved);
    }

    /**
     * Aisle round-4 #7 (CWE-400) regression — hop count cap. Even
     * when the byte cap doesn't fire (header fits in 4 KB), the
     * right-to-left walk's iteration cost is bounded by the slice
     * of the rightmost MAX_XFF_HOPS entries. This test packs 50
     * hops into a header that stays well under 4 KB and pins that
     * we walk only the rightmost slice.
     *
     * Layout: 50 trusted-proxy entries on the left, one untrusted
     * client IP at position N-1 (the rightmost). Without the cap,
     * the walk would still find it at position N-1 → bounded already.
     * The cap-relevant test is the *opposite* layout: untrusted IP
     * at position 0 (leftmost). Slicing to keep the rightmost 20
     * hops drops position 0, the walk sees only trusted entries,
     * fall back to REMOTE_ADDR. That's what we test.
     */
    public function testCapsXffHopCountKeepsRightmostEntries(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        // Untrusted real IP at the leftmost position; 49 trusted-proxy
        // hops follow. Total header well under 4 KB (~500 bytes), so
        // only the hop-count cap matters here.
        $hops = array_merge(
            ['198.51.100.42'],
            array_fill(0, 49, '10.0.0.5'),
        );
        $xff = implode(', ', $hops);

        $resolved = $resolver->resolveClientIp('10.0.0.5', $xff);

        // The leftmost real IP is outside the rightmost-20 slice;
        // the visible portion is all trusted; fall back to REMOTE_ADDR.
        self::assertSame('10.0.0.5', $resolved);
    }

    /**
     * Boundary at-cap acceptance. 20 hops where the legit untrusted
     * IP sits at the leftmost position — exactly inside the slice.
     * The walk should still find and return it. Pins the off-by-one:
     * if a future tightening reduces MAX_XFF_HOPS, this test is the
     * deliberate, visible signal.
     */
    public function testRespectsXffJustUnderCap(): void
    {
        $resolver = new TrustedProxyClientIpResolver(['10.0.0.0/8']);

        $hops = array_merge(
            ['198.51.100.42'],
            array_fill(0, 19, '10.0.0.5'),
        );
        $xff = implode(', ', $hops);

        $resolved = $resolver->resolveClientIp('10.0.0.5', $xff);

        self::assertSame('198.51.100.42', $resolved);
    }
}
