<?php

/**
 * Isolated tests for legacy database charset reporting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Utils;

use OpenEMR\Services\Utils\DatabaseCharsetAudit;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DatabaseCharsetAuditTest extends TestCase
{
    public function testGroupsLegacyTableAndColumnCharsetsWithoutDuplicates(): void
    {
        $result = DatabaseCharsetAudit::groupLegacyTables([
            [
                'TABLE_NAME' => 'pnotes',
                'TABLE_COLLATION' => 'utf8mb4_general_ci',
                'CHARACTER_SET_NAME' => 'utf8',
            ],
            [
                'TABLE_NAME' => 'old_patient_data',
                'TABLE_COLLATION' => 'latin1_swedish_ci',
                'CHARACTER_SET_NAME' => 'latin1',
            ],
            [
                'TABLE_NAME' => 'old_patient_data',
                'TABLE_COLLATION' => 'latin1_swedish_ci',
                'CHARACTER_SET_NAME' => 'latin1',
            ],
            [
                'TABLE_NAME' => 'current_table',
                'TABLE_COLLATION' => 'utf8mb4_general_ci',
                'CHARACTER_SET_NAME' => 'utf8mb4',
            ],
        ]);

        self::assertSame([
            'latin1' => ['old_patient_data'],
            'utf8mb3' => ['pnotes'],
        ], $result);
    }

    public function testLegacyTableDefaultIsReportedWithoutLegacyColumns(): void
    {
        $result = DatabaseCharsetAudit::groupLegacyTables([
            [
                'TABLE_NAME' => 'old_audit',
                'TABLE_COLLATION' => 'latin1_swedish_ci',
                'CHARACTER_SET_NAME' => null,
            ],
        ]);

        self::assertSame(['latin1' => ['old_audit']], $result);
    }

    public function testMissingTableNameFailsExplicitly(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        DatabaseCharsetAudit::groupLegacyTables([
            [
                'TABLE_COLLATION' => 'latin1_swedish_ci',
                'CHARACTER_SET_NAME' => 'latin1',
            ],
        ]);
    }
}
