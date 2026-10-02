<?php

/**
 * Isolated SsrfSafeUrlValidator Test
 *
 * Covers each rejection category (scheme, loopback, private-network, cloud
 * metadata, link-local, DNS-resolution) plus the accept path for
 * public-looking hostnames. DNS-resolution cases run against a subclass
 * with the resolver hooks overridden, so the test does not depend on live
 * DNS.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\SsrfSafeUrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SsrfSafeUrlValidatorIsolatedTest extends TestCase
{
    /**
     * Sanity check that the resolver-off constructor path accepts a URL that
     * would otherwise trigger a DNS lookup — the DNS-off surface is the one
     * used by the rest of this suite, so its accept case has to hold.
     */
    public function testResolverDisabledAcceptsPublicLookingHost(): void
    {
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $this->assertNull($validator->validate('https://example.com/jwks.json'));
    }

    /**
     * Data provider — safe URLs that must be accepted (with resolver OFF so
     * the check runs purely on host classification).
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function safeUrlsProvider(): array
    {
        return [
            'plain https public host' => ['https://example.com/.well-known/jwks.json'],
            'plain http public host' => ['http://public.example.org/jwks'],
            'https with port' => ['https://issuer.example.com:8443/jwks'],
            'https with query and fragment' => ['https://issuer.example.com/jwks?v=1#a'],
            'public IPv4 literal' => ['https://8.8.8.8/jwks'],
            'public IPv6 literal' => ['https://[2606:4700:4700::1111]/jwks'],
        ];
    }

    #[DataProvider('safeUrlsProvider')]
    public function testValidateAcceptsSafeUrls(string $url): void
    {
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $this->assertNull(
            $validator->validate($url),
            sprintf('Expected %s to be accepted', $url)
        );
    }

    /**
     * Data provider — unsafe URLs and the expected rejection reason constant.
     *
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsafeUrlsProvider(): array
    {
        return [
            'empty string' => ['', SsrfSafeUrlValidator::REASON_EMPTY],
            'whitespace only' => ['   ', SsrfSafeUrlValidator::REASON_EMPTY],
            'malformed url' => ['http://:80', SsrfSafeUrlValidator::REASON_MALFORMED],
            'missing scheme' => ['example.com/jwks', SsrfSafeUrlValidator::REASON_MISSING_SCHEME],
            'file scheme'    => ['file:///etc/passwd', SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME],
            'gopher scheme'  => ['gopher://example.com/', SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME],
            'dict scheme'    => ['dict://example.com/', SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME],
            'ftp scheme'     => ['ftp://example.com/', SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME],
            'javascript scheme' => ['javascript:alert(1)', SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME],
            'userinfo present'  => ['http://user:pass@example.com/', SsrfSafeUrlValidator::REASON_USERINFO],
            'localhost literal' => ['http://localhost/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            'subdomain of localhost' => ['http://foo.localhost/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            '127.0.0.1'      => ['http://127.0.0.1/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            '127.42.42.42'   => ['http://127.42.42.42/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            '0.0.0.0'        => ['http://0.0.0.0/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            'IPv6 ::1'       => ['http://[::1]/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            'IPv6 unspecified' => ['http://[::]/jwks', SsrfSafeUrlValidator::REASON_LOOPBACK],
            '10.0.0.1 RFC1918' => ['http://10.0.0.1/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            '172.16.0.1 RFC1918' => ['http://172.16.0.1/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            '172.31.255.255 RFC1918 top' => ['http://172.31.255.255/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            '192.168.1.1 RFC1918' => ['http://192.168.1.1/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            'IPv6 fc00 ULA'  => ['http://[fc00::1]/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            'IPv6 fd12 ULA'  => ['http://[fd12:3456:789a::1]/jwks', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
            'IPv6 fe80 link-local' => ['http://[fe80::1]/jwks', SsrfSafeUrlValidator::REASON_LINK_LOCAL],
            '169.254 link-local' => ['http://169.254.1.1/jwks', SsrfSafeUrlValidator::REASON_LINK_LOCAL],
            'AWS metadata IP' => ['http://169.254.169.254/latest/meta-data/', SsrfSafeUrlValidator::REASON_CLOUD_METADATA],
            'Alibaba metadata IP' => ['http://100.100.100.200/latest/meta-data/', SsrfSafeUrlValidator::REASON_CLOUD_METADATA],
            'GCP metadata name' => ['http://metadata.google.internal/', SsrfSafeUrlValidator::REASON_CLOUD_METADATA],
            'Azure metadata name' => ['http://metadata.azure.com/', SsrfSafeUrlValidator::REASON_CLOUD_METADATA],
            'IPv4-mapped IPv6 loopback' => ['http://[::ffff:127.0.0.1]/', SsrfSafeUrlValidator::REASON_LOOPBACK],
            'IPv4-mapped IPv6 RFC1918' => ['http://[::ffff:10.0.0.1]/', SsrfSafeUrlValidator::REASON_PRIVATE_NETWORK],
        ];
    }

    #[DataProvider('unsafeUrlsProvider')]
    public function testValidateRejectsUnsafeUrls(string $url, string $expectedReason): void
    {
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $reason = $validator->validate($url);
        $this->assertSame(
            $expectedReason,
            $reason,
            sprintf('URL %s should have been rejected with %s', $url, $expectedReason)
        );
    }

    /**
     * DNS-rebind coverage — subclass overrides the resolver hooks so we do
     * not depend on live DNS. A hostname that "resolves" to 127.0.0.1 (or a
     * private / metadata address) must be rejected even though the literal
     * hostname is public-looking.
     */
    public function testValidateRejectsHostnameResolvingToLoopback(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['127.0.0.1'];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DNS_UNSAFE,
            $validator->validate('https://rebind.example.com/jwks')
        );
    }

    public function testValidateRejectsHostnameResolvingToMetadataIp(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['169.254.169.254'];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DNS_UNSAFE,
            $validator->validate('https://metadata-proxy.example.com/latest/')
        );
    }

    public function testValidateRejectsHostnameResolvingToIpv6PrivateAddress(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return [];
            }

            protected function resolveIpv6(string $host): array
            {
                return ['fc00::1'];
            }
        };
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DNS_UNSAFE,
            $validator->validate('https://ipv6-only.example.com/jwks')
        );
    }

    public function testValidateRejectsHostnameThatResolvesToNothing(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return [];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DNS_UNRESOLVABLE,
            $validator->validate('https://nx.example.invalid/jwks')
        );
    }

    /**
     * A hostname that resolves ONLY to public addresses (both v4 and v6) must
     * pass the resolver step — proves the resolver isn't over-rejecting.
     */
    public function testValidateAcceptsHostnameResolvingToPublicAddress(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['203.0.113.10'];
            }

            protected function resolveIpv6(string $host): array
            {
                return ['2606:4700:4700::1111'];
            }
        };
        $this->assertNull($validator->validate('https://public-only.example.com/jwks'));
    }

    /**
     * Split-horizon behavior: if EVEN ONE of several resolved addresses is
     * unsafe, the whole URL is rejected. Otherwise a caller could shim in
     * a private address alongside a public one and pass validation.
     */
    public function testValidateRejectsHostnameWithMixedSafeAndUnsafeAddresses(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['8.8.8.8', '10.0.0.1'];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DNS_UNSAFE,
            $validator->validate('https://split-horizon.example.com/jwks')
        );
    }

    /**
     * Custom allowed-schemes constructor arg governs which schemes pass.
     */
    public function testValidateRespectsCustomAllowedSchemes(): void
    {
        $validator = new SsrfSafeUrlValidator(['https'], false);
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME,
            $validator->validate('http://example.com/jwks')
        );
        $this->assertNull($validator->validate('https://example.com/jwks'));
    }

    // -------------------------------------------------------------------------
    // validateAndPin() — resolved-address carry-out for fetch-time pinning.
    // The DNS check and the connect step both live in one process, but they
    // do not share a resolver cache, so a second resolution at fetch time
    // can see a different answer than the check did. Callers avoid that
    // gap by pinning the connect step to the address the validator returned.
    // -------------------------------------------------------------------------

    public function testValidateAndPinCarriesResolvedIpsForHostname(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['203.0.113.10', '203.0.113.11'];
            }

            protected function resolveIpv6(string $host): array
            {
                return ['2606:4700:4700::1111'];
            }
        };
        $result = $validator->validateAndPin('https://issuer.example.com:8443/jwks');

        $this->assertNull($result['reason']);
        $this->assertSame('issuer.example.com', $result['host']);
        $this->assertSame(8443, $result['port']);
        $this->assertSame('https', $result['scheme']);
        $this->assertSame(
            ['203.0.113.10', '203.0.113.11', '2606:4700:4700::1111'],
            $result['ips'],
            'Both v4 and v6 resolved addresses must be handed to the caller'
        );
    }

    public function testValidateAndPinDefaultsPortByScheme(): void
    {
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['http', 'https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['203.0.113.10'];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };

        $this->assertSame(443, $validator->validateAndPin('https://example.com/jwks')['port']);
        $this->assertSame(80, $validator->validateAndPin('http://example.com/jwks')['port']);
    }

    public function testValidateAndPinReturnsEmptyIpsForIpLiteral(): void
    {
        // IP literal — nothing for the caller to pin against, the URL
        // already names the target address. `ips` empty is the signal.
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $result = $validator->validateAndPin('https://8.8.8.8/jwks');

        $this->assertNull($result['reason']);
        $this->assertSame('8.8.8.8', $result['host']);
        $this->assertSame([], $result['ips']);
    }

    public function testValidateAndPinReturnsEmptyIpsWhenResolverOff(): void
    {
        // Resolver off — no lookup happened, no addresses to pin against.
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $result = $validator->validateAndPin('https://example.com/jwks');

        $this->assertNull($result['reason']);
        $this->assertSame([], $result['ips']);
    }

    public function testValidateAndPinCarriesRejectionReason(): void
    {
        $validator = new SsrfSafeUrlValidator(['https'], false);
        $result = $validator->validateAndPin('http://example.com/jwks');

        $this->assertSame(SsrfSafeUrlValidator::REASON_DISALLOWED_SCHEME, $result['reason']);
        $this->assertSame([], $result['ips']);
    }

    public function testValidateAndPinRejectsWhenAnyResolvedAddressIsUnsafe(): void
    {
        // Mirrors testValidateRejectsHostnameWithMixedSafeAndUnsafeAddresses:
        // if even one resolved address is unsafe, the whole URL is rejected
        // and no pin data is returned.
        $validator = new class extends SsrfSafeUrlValidator {
            public function __construct()
            {
                parent::__construct(['https'], true);
            }

            protected function resolveIpv4(string $host): array
            {
                return ['8.8.8.8', '10.0.0.1'];
            }

            protected function resolveIpv6(string $host): array
            {
                return [];
            }
        };
        $result = $validator->validateAndPin('https://split-horizon.example.com/jwks');

        $this->assertSame(SsrfSafeUrlValidator::REASON_DNS_UNSAFE, $result['reason']);
        $this->assertSame([], $result['ips']);
    }

    public function testValidateStillReturnsReasonAfterRefactor(): void
    {
        // validate() is now a thin wrapper over validateAndPin(). Lock the
        // legacy string|null return contract so callers of validate() do not
        // regress silently when validateAndPin() evolves.
        $validator = new SsrfSafeUrlValidator(['http', 'https'], false);
        $this->assertNull($validator->validate('https://example.com/jwks'));
        $this->assertSame(
            SsrfSafeUrlValidator::REASON_LOOPBACK,
            $validator->validate('http://127.0.0.1/jwks')
        );
    }
}
