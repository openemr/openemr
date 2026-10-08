<?php

/**
 * Layout columns are quoted from the live table, not a frozen list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use OpenEMR\Common\Database\LayoutColumnUpdate;
use OpenEMR\Common\Database\SqlQueryException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LayoutColumnCatalogTest extends TestCase
{
    #[Test]
    public function testTheQuoterUsesTheLiveTable(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 5) . '/src/Common/Database/LayoutColumnUpdate.php');

        $this->assertStringContainsString('escape_sql_column_name(', $source);
        $this->assertStringNotContainsString('private const PATIENT', $source);
        $this->assertStringNotContainsString('private const ENCOUNTER', $source);

        $sql = (string) file_get_contents(dirname(__DIR__, 5) . '/sql/database.sql');
        foreach (['patient_data' => 'fname', 'form_encounter' => 'reason'] as $table => $column) {
            $this->assertSame(1, preg_match('/CREATE TABLE `' . $table . '` /', $sql));
            $this->assertStringNotContainsString('SET `' . $column . '`', $source);
        }
    }

    #[Test]
    public function testRefusedIdentityColumnsThrow(): void
    {
        foreach (['id', 'uuid', 'pid', 'encounter', ''] as $column) {
            try {
                LayoutColumnUpdate::patientStatement($column);
                $this->fail($column . ' was accepted for the patient');
            } catch (SqlQueryException) {
                $this->addToAssertionCount(1);
            }
        }

        foreach (['id', 'uuid', 'pid', 'encounter', ''] as $column) {
            try {
                LayoutColumnUpdate::encounterStatement($column);
                $this->fail($column . ' was accepted for the visit');
            } catch (SqlQueryException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
