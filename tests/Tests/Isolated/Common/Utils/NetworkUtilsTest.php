<?php

/**
 * NetworkUtils Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\NetworkUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NetworkUtilsTest extends TestCase
{
    private NetworkUtils $networkUtils;

    protected function setUp(): void
    {
        $this->networkUtils = new NetworkUtils();
    }

    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function loopbackAddressProvider(): iterable
    {
        yield 'ipv4 127.0.0.1' => ['127.0.0.1'];
        yield 'ipv4 127.0.0.2' => ['127.0.0.2'];
        yield 'ipv4 127.255.255.255' => ['127.255.255.255'];
        yield 'ipv6 ::1' => ['::1'];
        yield 'localhost' => ['localhost'];
        yield 'localhost.localdomain' => ['localhost.localdomain'];
        yield 'LOCALHOST uppercase' => ['LOCALHOST'];
        yield 'Localhost mixed case' => ['Localhost'];
    }

    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function nonLoopbackAddressProvider(): iterable
    {
        yield 'public ip' => ['8.8.8.8'];
        yield 'private 192.168' => ['192.168.1.1'];
        yield 'private 10.0' => ['10.0.0.1'];
        yield 'ipv6 public' => ['2001:db8::1'];
        yield 'example.com' => ['example.com'];
    }

    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function loopbackUrlProvider(): iterable
    {
        yield 'http localhost' => ['http://localhost'];
        yield 'https localhost' => ['https://localhost'];
        yield 'http 127.0.0.1' => ['http://127.0.0.1'];
        yield 'https 127.0.0.1' => ['https://127.0.0.1'];
        yield 'http localhost with path' => ['http://localhost/api/v1'];
        yield 'http localhost with port' => ['http://localhost:8080'];
        yield 'http 127.0.0.1 with port and path' => ['http://127.0.0.1:9300/oauth2'];
    }

    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function nonLoopbackUrlProvider(): iterable
    {
        yield 'https example.com' => ['https://example.com'];
        yield 'http 8.8.8.8' => ['http://8.8.8.8'];
        yield 'https with path' => ['https://api.openemr.org/v1/patients'];
    }

    #[DataProvider('loopbackAddressProvider')]
    public function testLoopbackAddressesAreDetected(string $address): void
    {
        $this->assertTrue(
            $this->networkUtils->isLoopbackAddress($address),
            "Expected loopback: {$address}"
        );
    }

    #[DataProvider('nonLoopbackAddressProvider')]
    public function testNonLoopbackAddressesAreRejected(string $address): void
    {
        $this->assertFalse(
            $this->networkUtils->isLoopbackAddress($address),
            "Expected non-loopback: {$address}"
        );
    }

    #[DataProvider('loopbackUrlProvider')]
    public function testLoopbackUrlsAreDetected(string $url): void
    {
        $this->assertTrue(
            $this->networkUtils->isLoopbackAddress($url),
            "Expected loopback URL: {$url}"
        );
    }

    #[DataProvider('nonLoopbackUrlProvider')]
    public function testNonLoopbackUrlsAreRejected(string $url): void
    {
        $this->assertFalse(
            $this->networkUtils->isLoopbackAddress($url),
            "Expected non-loopback URL: {$url}"
        );
    }

    public function testBracketedIpv6Loopback(): void
    {
        $this->assertTrue($this->networkUtils->isLoopbackAddress('[::1]'));
    }

    public function testEmptyStringIsNotLoopback(): void
    {
        $this->assertFalse($this->networkUtils->isLoopbackAddress(''));
    }
}
