<?php

/**
 * Tests for CORSAllowedOriginRegistry::normalizeOrigin (pure function)
 * and for the pure-logic shape of isAllowedForClient (short-circuit
 * cases that do not require the DB).
 *
 * The DB-backed `getAllowedOrigins`/`getOriginsForClient`/`isAllowed`/
 * `isAllowedForClient` paths against real rows are covered by the API
 * suite; only the parser and the input-validation short-circuits are
 * pinned here.
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

    public function testIsAllowedForClientRejectsEmptyClientId(): void
    {
        // Short-circuit before any DB lookup: without a client, there
        // cannot be a per-client origin match.
        $registry = new CORSAllowedOriginRegistry();
        self::assertFalse($registry->isAllowedForClient('', 'https://app.example.com'));
    }

    public function testIsAllowedForClientRejectsEmptyOrigin(): void
    {
        $registry = new CORSAllowedOriginRegistry();
        self::assertFalse($registry->isAllowedForClient('any-client', ''));
    }

    public function testIsAllowedForClientRejectsLiteralNullOrigin(): void
    {
        // Browsers send `Origin: null` for sandboxed iframes, data: URLs,
        // and other opaque origins. Never match these against any
        // client's registered origins.
        $registry = new CORSAllowedOriginRegistry();
        self::assertFalse($registry->isAllowedForClient('any-client', 'null'));
        self::assertFalse($registry->isAllowedForClient('any-client', 'NULL'));
    }

    public function testIsAllowedForClientRejectsUnparseableOrigin(): void
    {
        $registry = new CORSAllowedOriginRegistry();
        self::assertFalse($registry->isAllowedForClient('any-client', 'not-a-url'));
    }

    public function testGetOriginsForClientReturnsEmptyListForEmptyClientId(): void
    {
        $registry = new CORSAllowedOriginRegistry();
        self::assertSame([], $registry->getOriginsForClient(''));
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
