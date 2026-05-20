<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery;

use OpenEMR\Common\Auth\Oidc\Discovery\OidcDnsResolverInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidationException;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OidcUrlValidatorTest extends TestCase
{
    public function testStrictPolicyAcceptsPublicHttpsUrl(): void
    {
        $validator = new OidcUrlValidator();

        // example.com is one of IANA's reserved domains; it has public DNS.
        $validator->validateDiscoveryUrl('https://example.com/.well-known/openid-configuration');
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function strictRejectionProvider(): array
    {
        return [
            'empty url' => ['', 'URL is required'],
            'malformed url' => ['http://:80', 'URL is malformed'],
            'http scheme rejected' => ['http://example.com/foo', 'must use https'],
            'no scheme' => ['example.com/foo', 'must use https'],
            'ftp scheme' => ['ftp://example.com/foo', 'must use https'],
            'credentials in url' => ['https://user:pass@example.com/foo', 'must not contain credentials'],
            'username only' => ['https://user@example.com/foo', 'must not contain credentials'],
            'loopback ipv4 literal' => ['https://127.0.0.1/foo', 'private/local'],
            'private ipv4 literal' => ['https://10.0.0.1/foo', 'private/local'],
            'rfc1918 192.168 literal' => ['https://192.168.1.1/foo', 'private/local'],
            'link-local ipv4 literal' => ['https://169.254.169.254/foo', 'private/local'],
            'loopback ipv6 literal' => ['https://[::1]/foo', 'private/local'],
        ];
    }

    #[DataProvider('strictRejectionProvider')]
    public function testStrictPolicyRejectsUnsafeUrl(string $url, string $expectedMessageFragment): void
    {
        $validator = new OidcUrlValidator();

        $this->expectException(OidcUrlValidationException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($expectedMessageFragment, '/') . '/');

        $validator->validateDiscoveryUrl($url);
    }

    public function testPermissivePolicyAcceptsHttpAndPrivateHost(): void
    {
        $validator = new OidcUrlValidator(
            requireHttps: false,
            blockPrivateIps: false,
        );

        $validator->validateDiscoveryUrl('http://oidc-mock:9400/.well-known/openid-configuration');
        $validator->validateDiscoveryUrl('http://127.0.0.1:8080/.well-known/openid-configuration');
        $this->expectNotToPerformAssertions();
    }

    public function testPermissivePolicyStillRejectsCredentialsAndOddSchemes(): void
    {
        $validator = new OidcUrlValidator(
            requireHttps: false,
            blockPrivateIps: false,
        );

        $caughtSchemes = false;
        try {
            $validator->validateDiscoveryUrl('gopher://oidc-mock:9400/foo');
        } catch (OidcUrlValidationException) {
            $caughtSchemes = true;
        }

        $caughtCreds = false;
        try {
            $validator->validateDiscoveryUrl('http://user:pass@oidc-mock:9400/foo');
        } catch (OidcUrlValidationException) {
            $caughtCreds = true;
        }

        self::assertTrue($caughtSchemes, 'Non-http(s) scheme must still be rejected when requireHttps=false');
        self::assertTrue($caughtCreds, 'URL credentials must still be rejected even in permissive mode');
    }

    public function testJwksUriRejectsHostThatDoesNotMatchIssuer(): void
    {
        // Permissive on private-IP so the docs-reserved host doesn't trigger a
        // DNS lookup — this test is about host matching, not IP filtering.
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        $this->expectException(OidcUrlValidationException::class);
        $this->expectExceptionMessage('does not match expected host');

        $validator->validateJwksUri('https://evil.example.com/jwks', 'https://accounts.example.com');
    }

    public function testJwksUriAcceptsHostThatMatchesIssuer(): void
    {
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        $validator->validateJwksUri('https://accounts.example.com/jwks', 'https://accounts.example.com');
        $this->expectNotToPerformAssertions();
    }

    public function testJwksUriHostMatchIsCaseInsensitive(): void
    {
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        $validator->validateJwksUri('https://Accounts.Example.COM/jwks', 'https://accounts.example.com');
        $this->expectNotToPerformAssertions();
    }

    public function testJwksUriWithoutIssuerSkipsHostPinningButStillEnforcesUrlSafety(): void
    {
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        // Cross-host is fine without an issuer to pin against.
        $validator->validateJwksUri('https://different-host.example.net/jwks');

        // But scheme/creds policy still applies.
        $caught = false;
        try {
            $validator->validateJwksUri('http://different-host.example.net/jwks');
        } catch (OidcUrlValidationException) {
            $caught = true;
        }
        self::assertTrue($caught, 'http:// jwks_uri must be rejected even without an issuer');
    }

    public function testStrictPolicyRejectsHostThatDoesNotResolve(): void
    {
        $validator = new OidcUrlValidator();

        $this->expectException(OidcUrlValidationException::class);
        // Either "could not be resolved" or "private/local" depending on how
        // the resolver is configured to answer for nonexistent TLDs.
        $this->expectExceptionMessageMatches('/(could not be resolved|private\/local)/');

        $validator->validateDiscoveryUrl('https://this-host-definitely-does-not-exist.invalid/foo');
    }

    public function testResolveAndAssertReturnsIpLiteralWithoutDns(): void
    {
        // Permissive on private IPs so the loopback literal isn't rejected
        // — the point of this test is to confirm the IP-literal short-circuit.
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        // No DNS lookup is attempted; the literal flows through unchanged.
        self::assertSame(['127.0.0.1'], $validator->resolveAndAssert('127.0.0.1'));
        self::assertSame(['203.0.113.7'], $validator->resolveAndAssert('203.0.113.7'));
    }

    public function testResolveAndAssertStripsBracketsFromIpv6Literal(): void
    {
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        self::assertSame(['::1'], $validator->resolveAndAssert('[::1]'));
        self::assertSame(['2001:db8::1'], $validator->resolveAndAssert('[2001:db8::1]'));
    }

    public function testResolveAndAssertRejectsIpLiteralInPrivateRangeUnderStrictPolicy(): void
    {
        $validator = new OidcUrlValidator();

        $this->expectException(OidcUrlValidationException::class);
        $this->expectExceptionMessage('private/local');

        $validator->resolveAndAssert('169.254.169.254');
    }

    public function testResolveAndAssertReturnsIpsForPublicHostUnderStrictPolicy(): void
    {
        $validator = new OidcUrlValidator();

        // example.com is IANA-reserved with public DNS records. Use it as
        // the canonical "definitely-resolves-to-something-public" host.
        // The non-empty-list return type means the bare "is non-empty"
        // check would be redundant; assert each entry parses as a real IP.
        $ips = $validator->resolveAndAssert('example.com');

        foreach ($ips as $ip) {
            self::assertNotFalse(filter_var($ip, FILTER_VALIDATE_IP), "Expected a valid IP, got: {$ip}");
        }
    }

    public function testResolveAndAssertThrowsOnEmptyHost(): void
    {
        $validator = new OidcUrlValidator(blockPrivateIps: false);

        $this->expectException(OidcUrlValidationException::class);
        $this->expectExceptionMessage('host is required');

        $validator->resolveAndAssert('');
    }

    /**
     * Aisle round-4 #5 (CWE-400) regression. An attacker controlling
     * authoritative DNS for an allow-listed host can publish thousands
     * of A/AAAA records to inflate the per-IP validation loop and the
     * downstream `CURLOPT_RESOLVE` string. The validator must fail
     * closed once the dedup'd count exceeds MAX_IPS_PER_HOST.
     */
    public function testResolveAndAssertRejectsHostWithMoreThanMaxDnsRecords(): void
    {
        // One more than the cap — pre-dedup, all distinct.
        $records = [];
        for ($i = 1; $i <= OidcUrlValidator::MAX_IPS_PER_HOST + 1; $i++) {
            $records[] = ['type' => 'A', 'ip' => '203.0.113.' . $i];
        }
        $validator = new OidcUrlValidator(
            blockPrivateIps: false,
            dnsResolver: $this->stubResolver($records),
        );

        $this->expectException(OidcUrlValidationException::class);
        $this->expectExceptionMessage('too many DNS records');

        $validator->resolveAndAssert('attacker.example');
    }

    public function testResolveAndAssertAcceptsExactlyMaxDnsRecords(): void
    {
        $records = [];
        for ($i = 1; $i <= OidcUrlValidator::MAX_IPS_PER_HOST; $i++) {
            $records[] = ['type' => 'A', 'ip' => '203.0.113.' . $i];
        }
        $validator = new OidcUrlValidator(
            blockPrivateIps: false,
            dnsResolver: $this->stubResolver($records),
        );

        $ips = $validator->resolveAndAssert('limit.example');

        // At-cap acceptance — boundary just below the throw.
        self::assertCount(OidcUrlValidator::MAX_IPS_PER_HOST, $ips);
    }

    public function testResolveAndAssertDedupesRepeatedRecords(): void
    {
        // 10 records but only 3 distinct IPs. Pre-fix this would have
        // returned 10 entries; post-fix returns 3, in first-seen order.
        $records = [
            ['type' => 'A', 'ip' => '203.0.113.1'],
            ['type' => 'A', 'ip' => '203.0.113.2'],
            ['type' => 'A', 'ip' => '203.0.113.1'],
            ['type' => 'A', 'ip' => '203.0.113.3'],
            ['type' => 'A', 'ip' => '203.0.113.2'],
            ['type' => 'A', 'ip' => '203.0.113.1'],
            ['type' => 'A', 'ip' => '203.0.113.3'],
            ['type' => 'A', 'ip' => '203.0.113.2'],
            ['type' => 'A', 'ip' => '203.0.113.1'],
            ['type' => 'A', 'ip' => '203.0.113.3'],
        ];
        $validator = new OidcUrlValidator(
            blockPrivateIps: false,
            dnsResolver: $this->stubResolver($records),
        );

        $ips = $validator->resolveAndAssert('dup.example');

        self::assertSame(['203.0.113.1', '203.0.113.2', '203.0.113.3'], $ips);
    }

    /**
     * Padding the answer with thousands of repeats of a single IP is
     * the cheapest version of the attack — DNS message size is
     * smaller than the distinct-IP variant. Dedup must short-circuit
     * the cap so a single repeated IP never fires the
     * "too many DNS records" branch.
     */
    public function testResolveAndAssertDedupSurvivesPaddedDuplicates(): void
    {
        $records = [];
        for ($i = 0; $i < 500; $i++) {
            $records[] = ['type' => 'A', 'ip' => '203.0.113.42'];
        }
        $validator = new OidcUrlValidator(
            blockPrivateIps: false,
            dnsResolver: $this->stubResolver($records),
        );

        $ips = $validator->resolveAndAssert('flood.example');

        self::assertSame(['203.0.113.42'], $ips);
    }

    public function testResolveAndAssertHandlesMixedAaaaAndA(): void
    {
        $records = [
            ['type' => 'A', 'ip' => '203.0.113.10'],
            ['type' => 'AAAA', 'ipv6' => '2001:db8::a'],
            ['type' => 'A', 'ip' => '203.0.113.10'], // dup of first
        ];
        $validator = new OidcUrlValidator(
            blockPrivateIps: false,
            dnsResolver: $this->stubResolver($records),
        );

        $ips = $validator->resolveAndAssert('dual.example');

        self::assertSame(['203.0.113.10', '2001:db8::a'], $ips);
    }

    /**
     * @param list<array<string, mixed>>|false $records
     */
    private function stubResolver(array|false $records): OidcDnsResolverInterface
    {
        return new class ($records) implements OidcDnsResolverInterface {
            /**
             * @param list<array<string, mixed>>|false $records
             */
            public function __construct(private readonly array|false $records)
            {
            }

            public function getRecords(string $host): array|false
            {
                return $this->records;
            }
        };
    }
}
