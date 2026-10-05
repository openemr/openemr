<?php

/**
 * Layout columns are saved with a literal statement, not a built identifier.
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

class LayoutColumnUpdateTest extends TestCase
{
    #[Test]
    public function testAVisitReasonUsesOneLiteralStatement(): void
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
    public function testAPatientNameUsesOneLiteralStatement(): void
    {
        $statement = LayoutColumnUpdate::patientStatement('fname');

        $this->assertSame(
            'UPDATE patient_data SET `fname` = ? WHERE pid = ?',
            $statement
        );
        $this->assertSame(2, substr_count($statement, '`'));
        $this->assertStringNotContainsString('``', $statement);
    }

    #[Test]
    public function testAnIdentityColumnIsRefused(): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::patientStatement('pid');
    }

    #[Test]
    public function testAnUnknownColumnIsRefused(): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::encounterStatement('not_a_column');
    }

    #[Test]
    public function testAQuotedFieldIdIsNotAStatement(): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::encounterStatement('reason` = 1; --');
    }
}
