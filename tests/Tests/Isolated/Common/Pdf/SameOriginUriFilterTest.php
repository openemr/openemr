<?php

/**
 * Isolated SameOriginUriFilter test.
 *
 * Covers the HTMLPurifier URI filter the patient-portal PDF renderer uses
 * to constrain http(s) resource references to a configured host[:port].
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Pdf;

use HTMLPurifier_Config;
use HTMLPurifier_Context;
use HTMLPurifier_URI;
use OpenEMR\Pdf\SameOriginUriFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SameOriginUriFilterTest extends TestCase
{
    public function testParseSpecBareHostname(): void
    {
        $this->assertSame([null, 'example.com', null], SameOriginUriFilter::parseSpec('example.com'));
    }

    public function testParseSpecHostAndPort(): void
    {
        $this->assertSame([null, 'example.com', 9303], SameOriginUriFilter::parseSpec('example.com:9303'));
    }

    public function testParseSpecHttpSchemePrefix(): void
    {
        $this->assertSame(['http', 'example.com', null], SameOriginUriFilter::parseSpec('http://example.com'));
    }

    public function testParseSpecHttpsSchemeWithPort(): void
    {
        $this->assertSame(['https', 'example.com', 9303], SameOriginUriFilter::parseSpec('https://example.com:9303'));
    }

    public function testParseSpecSchemeIsLowercased(): void
    {
        $this->assertSame(['https', 'example.com', null], SameOriginUriFilter::parseSpec('HTTPS://example.com'));
    }

    public function testSplitHostPortIpv6WithoutPort(): void
    {
        $this->assertSame(['[::1]', null], SameOriginUriFilter::splitHostPort('[::1]'));
    }

    public function testSplitHostPortIpv6WithPort(): void
    {
        $this->assertSame(['[::1]', 8080], SameOriginUriFilter::splitHostPort('[::1]:8080'));
    }

    public function testSplitHostPortIpv6FullAddressWithPort(): void
    {
        $this->assertSame(['[2001:db8::1]', 443], SameOriginUriFilter::splitHostPort('[2001:db8::1]:443'));
    }

    public function testSplitHostPortDnsWithoutPort(): void
    {
        $this->assertSame(['example.com', null], SameOriginUriFilter::splitHostPort('example.com'));
    }

    public function testSplitHostPortDnsWithPort(): void
    {
        $this->assertSame(['example.com', 9303], SameOriginUriFilter::splitHostPort('example.com:9303'));
    }

    /** @param array{?string, ?string, ?int, string} $uri */
    #[DataProvider('filterProvider')]
    public function testFilterDecisions(string $allowedSpec, array $uri, bool $expected): void
    {
        [$scheme, $host, $port, $path] = $uri;
        $filter = new SameOriginUriFilter($allowedSpec);
        $purifierUri = self::makeUri($scheme, $host, $port, $path);
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        $this->assertSame($expected, $filter->filter($purifierUri, $config, $context));
    }

    /**
     * HTMLPurifier_URI's parameter stubs are not nullable in phpstan's catalog, but the
     * runtime constructor accepts null for every field. Centralize the construction here
     * so test cases stay type-clean.
     */
    private static function makeUri(?string $scheme, ?string $host, ?int $port, string $path): HTMLPurifier_URI
    {
        // @phpstan-ignore-next-line
        return new HTMLPurifier_URI($scheme, null, $host, $port, $path, null, null);
    }

    /**
     * Each row: [allowedHostSpec, [scheme, host, port, path], expected].
     *
     * @return array<string, array{string, array{?string, ?string, ?int, string}, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function filterProvider(): array
    {
        return [
            // Relative URL (no scheme, no host) always passes.
            'relative-url-no-scheme-no-host' => ['allowed.example', [null, null, null, '/barcode.php'], true],
            // data: URIs are always allowed.
            'data-scheme' => ['allowed.example', ['data', null, null, 'image/png;base64,abc'], true],
            // file:// and php:// and other non-http(s) schemes are rejected.
            'file-scheme' => ['allowed.example', ['file', null, null, '/etc/hostname'], false],
            'php-scheme' => ['allowed.example', ['php', null, null, 'filter/x'], false],
            'javascript-scheme' => ['allowed.example', ['javascript', null, null, 'alert(1)'], false],
            // Same-host http matches.
            'same-host-http' => ['allowed.example', ['http', 'allowed.example', null, '/img.png'], true],
            // Host match is case-insensitive.
            'same-host-case-insensitive' => ['allowed.example', ['http', 'ALLOWED.example', null, '/img.png'], true],
            // Different host is rejected.
            'different-host-http' => ['allowed.example', ['http', 'other.example', null, '/img.png'], false],
            'different-host-https' => ['allowed.example', ['https', 'other.example', null, '/x'], false],
            // Protocol-relative (no scheme) with authority — must match allowed host.
            'protocol-relative-same-host' => ['allowed.example', [null, 'allowed.example', null, '/img.png'], true],
            'protocol-relative-different-host' => ['allowed.example', [null, 'other.example', null, '/img.png'], false],
            // Blank config rejects every remote resource.
            'blank-config-rejects-http' => ['', ['http', 'allowed.example', null, '/x'], false],
            'blank-config-rejects-https' => ['', ['https', 'allowed.example', null, '/x'], false],
            'blank-config-allows-data' => ['', ['data', null, null, 'image/png;base64,abc'], true],
            'blank-config-allows-relative' => ['', [null, null, null, '/barcode.php'], true],
            // Port: no config port means scheme default is expected.
            'default-port-null-port-http' => ['allowed.example', ['http', 'allowed.example', null, '/x'], true],
            'default-port-explicit-80-http' => ['allowed.example', ['http', 'allowed.example', 80, '/x'], true],
            'default-port-explicit-443-https' => ['allowed.example', ['https', 'allowed.example', 443, '/x'], true],
            // Non-default port on same host is rejected.
            'different-port-rejected' => ['allowed.example', ['http', 'allowed.example', 6379, '/x'], false],
            'different-port-rejected-https' => ['allowed.example', ['https', 'allowed.example', 8080, '/x'], false],
            // Configured custom port is accepted; standard ports are not.
            'custom-port-matches' => ['allowed.example:9303', ['http', 'allowed.example', 9303, '/barcode.php'], true],
            'custom-port-null-rejected' => ['allowed.example:9303', ['http', 'allowed.example', null, '/x'], false],
            'custom-port-standard-rejected' => ['allowed.example:9303', ['http', 'allowed.example', 80, '/x'], false],
            // Scheme-pinned config enforces scheme match.
            'scheme-pinned-https-accepts-https' => ['https://allowed.example', ['https', 'allowed.example', null, '/x'], true],
            'scheme-pinned-https-rejects-http' => ['https://allowed.example', ['http', 'allowed.example', null, '/x'], false],
            'scheme-pinned-http-accepts-http' => ['http://allowed.example', ['http', 'allowed.example', null, '/x'], true],
            'scheme-pinned-http-rejects-https' => ['http://allowed.example', ['https', 'allowed.example', null, '/x'], false],
            // Scheme-unpinned config accepts both http and https.
            'scheme-unpinned-accepts-http' => ['allowed.example', ['http', 'allowed.example', null, '/x'], true],
            'scheme-unpinned-accepts-https' => ['allowed.example', ['https', 'allowed.example', null, '/x'], true],
            // Full origin spec covers scheme + host + port.
            'full-origin-matches' => ['https://allowed.example:9303', ['https', 'allowed.example', 9303, '/x'], true],
            'full-origin-port-mismatch' => ['https://allowed.example:9303', ['https', 'allowed.example', 443, '/x'], false],
            'full-origin-scheme-mismatch' => ['https://allowed.example:9303', ['http', 'allowed.example', 9303, '/x'], false],
        ];
    }
}
