<?php

/**
 * HttpUtils Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\HttpUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HttpUtilsTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function roundTripDataProvider(): iterable
    {
        yield 'simple string' => ['hello world'];
        yield 'empty string' => [''];
        yield 'binary data' => ["\x00\x01\x02\xFF\xFE"];
        yield 'json payload' => ['{"key":"value","num":42}'];
        yield 'special chars' => ['foo+bar/baz=qux'];
        yield 'long string' => [str_repeat('OpenEMR', 100)];
        yield 'unicode' => ['José García — España'];
    }

    #[DataProvider('roundTripDataProvider')]
    public function testBase64UrlRoundTrip(string $data): void
    {
        $encoded = HttpUtils::base64url_encode($data);
        $decoded = HttpUtils::base64url_decode($encoded);
        $this->assertSame($data, $decoded);
    }

    public function testBase64UrlEncodeProducesUrlSafeOutput(): void
    {
        // Standard base64 uses + / = which are not URL-safe
        // base64url uses - _ and no padding
        $data = "\xFF\xFE\xFD\xFC\xFB";
        $encoded = HttpUtils::base64url_encode($data);

        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    public function testBase64UrlEncodeIsNotStandardBase64(): void
    {
        // Data that produces + or / in standard base64
        $data = "\xFF\xFE";
        $standardBase64 = base64_encode($data);
        $urlSafe = HttpUtils::base64url_encode($data);

        // They should differ (standard has padding, possibly + or /)
        $this->assertNotSame($standardBase64, $urlSafe);
    }
}
