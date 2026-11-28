<?php

/**
 * Isolated FlowBoardFilters Test
 *
 * How wide the Flow Board's Patient ID box is.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\FlowBoard;

use OpenEMR\Appointment\FlowBoard\FlowBoardFilters;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FlowBoardFiltersTest extends TestCase
{
    /**
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patientIdProvider(): array
    {
        return [
            'no patients' => ['', 4],
            'short IDs' => ['42', 4],
            'long IDs' => ['1234567', 7],
        ];
    }

    #[DataProvider('patientIdProvider')]
    public function testThePatientIdBoxFitsTheLongestId(string $longestId, int $expected): void
    {
        $this->assertSame($expected, FlowBoardFilters::patientIdLength($longestId));
    }
}
