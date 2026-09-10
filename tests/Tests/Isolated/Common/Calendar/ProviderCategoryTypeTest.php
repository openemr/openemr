<?php

/**
 * Isolated tests for ProviderCategoryType.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\ProviderCategoryType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProviderCategoryTypeTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function toDomMarkerProvider(): array
    {
        return [
            'int provider status' => [1, 'true'],
            'string provider status' => ['1', 'true'],
            'float-ish provider status' => [1.0, 'true'],
            'patient category zero' => [0, ''],
            'string zero' => ['0', ''],
            'other category' => [2, ''],
            'null' => [null, ''],
            'empty string' => ['', ''],
            'non-numeric' => ['provider', ''],
            'bool true is not numeric' => [true, ''],
            'bool false' => [false, ''],
        ];
    }

    #[DataProvider('toDomMarkerProvider')]
    public function testToDomMarker(mixed $cattype, string $expected): void
    {
        $this->assertSame($expected, ProviderCategoryType::toDomMarker($cattype));
    }

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function isProviderStatusProvider(): array
    {
        return [
            'provider' => [1, true],
            'patient' => [0, false],
            'null' => [null, false],
            'string one' => ['1', true],
        ];
    }

    #[DataProvider('isProviderStatusProvider')]
    public function testIsProviderStatus(mixed $cattype, bool $expected): void
    {
        $this->assertSame($expected, ProviderCategoryType::isProviderStatus($cattype));
    }
}
