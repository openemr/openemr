<?php

/**
 * Isolated tests for the Ensora address eRxGlobals hands out.
 *
 * interface/eRxGlobals.php declares nothing but the class, so it can be
 * required on its own and given a plain configuration array.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use eRxGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class ErxGlobalsPathTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../interface/eRxGlobals.php';
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pathProvider(): array
    {
        return [
            'https kept' => ['https://ensora.test/v7/WebRx/RxEntry.aspx', 'https://ensora.test/v7/WebRx/RxEntry.aspx'],
            'http upgraded' => ['http://ensora.test/v7/WebRx/RxEntry.aspx', 'https://ensora.test/v7/WebRx/RxEntry.aspx'],
            'uppercase http upgraded' => ['HTTP://ensora.test/RxEntry.aspx', 'https://ensora.test/RxEntry.aspx'],
            'http default port dropped' => ['http://ensora.test:80/RxEntry.aspx', 'https://ensora.test/RxEntry.aspx'],
        ];
    }

    #[DataProvider('pathProvider')]
    public function testGetPathReturnsAnHttpsUrl(string $configured, string $expected): void
    {
        $configuration = ['erx_newcrop_path' => $configured];

        $this->assertSame($expected, (new eRxGlobals($configuration))->getPath());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedProvider(): array
    {
        return [
            'missing' => [[]],
            'not a string' => [['erx_newcrop_path' => 1]],
            'other scheme' => [['erx_newcrop_path' => 'ftp://ensora.test/RxEntry.aspx']],
            'no scheme' => [['erx_newcrop_path' => 'ensora.test/RxEntry.aspx']],
            'no host' => [['erx_newcrop_path' => 'https:RxEntry.aspx']],
            'malformed' => [['erx_newcrop_path' => 'https://']],
        ];
    }

    /**
     * @param array<string, mixed> $configuration
     */
    #[DataProvider('refusedProvider')]
    public function testGetPathRefusesAnythingElse(array $configuration): void
    {
        $globals = new eRxGlobals($configuration);

        $this->expectException(UnexpectedValueException::class);

        $globals->getPath();
    }
}
