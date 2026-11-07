<?php

/**
 * Isolated PatientFinderColumn Test
 *
 * Covers the column value object's constructors and flags: the table/SQL/
 * enriched flavours, the SQL search-expression default, and the sort-direction
 * default and wither.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use PHPUnit\Framework\TestCase;

class PatientFinderColumnTest extends TestCase
{
    public function testFromTableDefaults(): void
    {
        $col = PatientFinderColumn::fromTable('sex', 'Sex');

        $this->assertSame('sex', $col->key);
        $this->assertSame('Sex', $col->title);
        $this->assertSame(PatientFinderColumn::TYPE_PATIENT_DATA, $col->type);
        $this->assertTrue($col->searchable);
        $this->assertTrue($col->sortable);
        $this->assertSame('patient_data', $col->table);
        $this->assertSame('asc', $col->defaultSortDir);
        $this->assertFalse($col->isSql());
        $this->assertFalse($col->isEnriched());
    }

    public function testFromSqlSearchExpressionDefaultsToSelectExpression(): void
    {
        $expr = '(SELECT i.ihi_number FROM ihi_data i WHERE i.pid = patient_data.pid LIMIT 1)';
        $col = PatientFinderColumn::fromSql('ihi_number', 'IHI', $expr);

        $this->assertTrue($col->isSql());
        $this->assertSame($expr, $col->selectExpression);
        $this->assertSame($expr, $col->searchExpression);
        $this->assertNull($col->joinClause);
    }

    public function testFromSqlHonoursExplicitSearchExpressionAndJoin(): void
    {
        $col = PatientFinderColumn::fromSql(
            'ihi_number',
            'IHI',
            'ihi.ihi_number',
            'LEFT JOIN ihi_data ihi ON ihi.pid = patient_data.pid',
            'ihi.ihi_number',
            [],
            [],
            true,
            true,
            'desc'
        );

        $this->assertSame('ihi.ihi_number', $col->searchExpression);
        $this->assertSame('LEFT JOIN ihi_data ihi ON ihi.pid = patient_data.pid', $col->joinClause);
        $this->assertSame('desc', $col->defaultSortDir);
    }

    public function testEnrichedColumnIsDisplayOnly(): void
    {
        $col = PatientFinderColumn::enriched('care_flag', 'Care Flag');

        $this->assertTrue($col->isEnriched());
        $this->assertFalse($col->searchable);
        $this->assertFalse($col->sortable);
    }

    public function testWithDefaultSortDirReturnsCopy(): void
    {
        $col = PatientFinderColumn::fromTable('DOB', 'DOB');
        $desc = $col->withDefaultSortDir('desc');

        $this->assertSame('asc', $col->defaultSortDir, 'original is unchanged');
        $this->assertSame('desc', $desc->defaultSortDir);
        $this->assertSame('DOB', $desc->key);
        $this->assertNotSame($col, $desc);
    }
}
