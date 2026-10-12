<?php

/**
 * Tests for CORSAllowedOriginRegistry::normalizeOrigin (pure function).
 *
 * The DB-backed `getAllowedOrigins`/`isAllowed` path is covered by the
 * API suite; only the parser is pinned here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect;

use OpenEMR\Common\Auth\OpenIDConnect\CORSAllowedOriginRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CORSAllowedOriginRegistryTest extends TestCase
{
    #[DataProvider('normalizeOriginProvider')]
    public function testNormalizeOrigin(string $input, ?string $expected): void
    {
        self::assertSame($expected, CORSAllowedOriginRegistry::normalizeOrigin($input));
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function normalizeOriginProvider(): array
    {
        return [
            'basic https origin'              => ['https://app.example.com', 'https://app.example.com'],
            'origin with path strips path'    => ['https://app.example.com/callback', 'https://app.example.com'],
            'http default port omitted'       => ['http://app.example.com:80', 'http://app.example.com'],
            'https default port omitted'      => ['https://app.example.com:443', 'https://app.example.com'],
            'non-default port kept'           => ['https://app.example.com:8443', 'https://app.example.com:8443'],
            'host lowercased'                 => ['https://APP.Example.COM', 'https://app.example.com'],
            'scheme lowercased'               => ['HTTPS://app.example.com', 'https://app.example.com'],
            'empty string returns null'       => ['', null],
            'relative path returns null'      => ['/callback', null],
            'missing scheme returns null'     => ['app.example.com', null],
            'missing host returns null'       => ['https://', null],
            'whitespace is trimmed'           => ['  https://app.example.com  ', 'https://app.example.com'],
            'query string stripped'           => ['https://app.example.com/cb?x=1', 'https://app.example.com'],
            'fragment stripped'               => ['https://app.example.com/cb#frag', 'https://app.example.com'],
        ];
    }
}
