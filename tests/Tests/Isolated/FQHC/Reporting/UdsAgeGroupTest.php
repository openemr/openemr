<?php

/**
 * Isolated tests for the UDS Table 4 age grouping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use DomainException;
use OpenEMR\FQHC\Reporting\UdsAgeGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsAgeGroupTest extends TestCase
{
    #[DataProvider('ageProvider')]
    public function testFromAgeSplitsAtEighteen(int $age, UdsAgeGroup $expected): void
    {
        self::assertSame($expected, UdsAgeGroup::fromAge($age));
    }

    public function testFromAgeRejectsNegativeAge(): void
    {
        $this->expectException(DomainException::class);

        UdsAgeGroup::fromAge(-1);
    }

    /**
     * @return array<string, array{int, UdsAgeGroup}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ageProvider(): array
    {
        return [
            'newborn' => [0, UdsAgeGroup::Under18],
            'seventeen is still 0-17' => [17, UdsAgeGroup::Under18],
            'eighteen flips to adult' => [18, UdsAgeGroup::EighteenAndOver],
            'older adult' => [65, UdsAgeGroup::EighteenAndOver],
        ];
    }
}
