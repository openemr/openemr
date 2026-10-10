<?php

/**
 * A layout column on the live table is quoted once.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Database;

use OpenEMR\Common\Database\LayoutColumnUpdate;
use OpenEMR\Common\Database\SqlQueryException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LayoutColumnUpdateTest extends TestCase
{
    #[Test]
    public function testAVisitReasonIsQuotedOnce(): void
    {
        $statement = LayoutColumnUpdate::encounterStatement('reason');

        $this->assertSame(
            'UPDATE form_encounter SET `reason` = ? WHERE pid = ? AND encounter = ?',
            $statement
        );
        $this->assertSame(2, substr_count($statement, '`'));
        $this->assertStringNotContainsString('``', $statement);
    }

    #[Test]
    public function testAPatientNameIsQuotedOnce(): void
    {
        $statement = LayoutColumnUpdate::patientStatement('fname');

        $this->assertSame(
            'UPDATE patient_data SET `fname` = ? WHERE pid = ?',
            $statement
        );
        $this->assertStringNotContainsString('``', $statement);
    }

    #[Test]
    public function testAnUnknownColumnIsRefused(): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::patientStatement('not_a_column');
    }
}
