<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Patient;

use OpenEMR\Services\Patient\DuplicatePatientColumn;
use OpenEMR\Services\Patient\DuplicatePatientCsvWriter;
use OpenEMR\Services\Patient\DuplicatePatientGroup;
use OpenEMR\Services\Patient\DuplicatePatientRow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DuplicatePatientCsvWriterTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private static function patientRow(array $overrides = []): array
    {
        return array_merge([
            'pid' => '7',
            'pubpid' => 'PUB7',
            'dupscore' => '14',
            'lname' => 'Nakamura',
            'fname' => 'Aiko',
            'mname' => '',
            'DOB' => '1984-11-02',
            'sex' => 'Female',
            'email' => 'aiko@example.com',
            'phone_home' => '555-1000',
            'phone_biz' => '',
            'phone_cell' => '',
            'regdate' => '2020-04-01',
            'street' => '12 Elm St',
        ], $overrides);
    }

    private const HIGHLIGHT = 17;

    private static function group(int $number): DuplicatePatientGroup
    {
        return self::rendered(new DuplicatePatientGroup(
            $number,
            DuplicatePatientRow::forPrimary(self::patientRow(), self::HIGHLIGHT),
            [DuplicatePatientRow::forMatch(
                self::patientRow(['pid' => '9', 'myscore' => '20']),
                7,
                self::HIGHLIGHT
            )]
        ));
    }

    /**
     * The controller renders every row's cells before handing the report to the writer.
     */
    private static function rendered(DuplicatePatientGroup $group): DuplicatePatientGroup
    {
        foreach ($group->getRows() as $row) {
            $row->renderCells(DuplicatePatientColumn::defaults());
        }

        return $group;
    }

    #[Test]
    public function writesAHeaderRowEvenWithNoGroups(): void
    {
        $csv = (new DuplicatePatientCsvWriter())->write([], DuplicatePatientColumn::defaults());

        // The headings are the column labels, so the table and the spreadsheet name things the same.
        $this->assertSame(
            '"Group","Score","Pid","Public","Scope","Name","DOB","Gender","Email","Telephone","Registered","Address"',
            rtrim($csv, "\n")
        );
    }

    #[Test]
    public function writesEveryRowOfEveryGroupTaggedWithItsGroupNumber(): void
    {
        $csv = (new DuplicatePatientCsvWriter())->write([self::group(1), self::group(2)], DuplicatePatientColumn::defaults());
        $lines = explode("\n", rtrim($csv, "\n"));

        $this->assertCount(5, $lines, 'a header plus two rows for each of the two groups');
        $this->assertStringStartsWith('"1","14","7"', $lines[1]);
        $this->assertStringStartsWith('"1","20","9"', $lines[2]);
        $this->assertStringStartsWith('"2","14","7"', $lines[3]);
        $this->assertStringStartsWith('"2","20","9"', $lines[4]);
    }

    /**
     * csvEscape() strips the characters a spreadsheet would treat as the start of a formula, which
     * is the whole reason cells are not simply concatenated.
     */
    #[Test]
    public function neutralisesFormulaInjectionInPatientData(): void
    {
        $hostile = DuplicatePatientRow::forPrimary(self::patientRow([
            'lname' => '=cmd|\' /C calc\'!A0',
            'fname' => '+HYPERLINK("http://evil")',
            'street' => '@SUM(A1:A9)',
        ]), self::HIGHLIGHT);
        $csv = (new DuplicatePatientCsvWriter())->write([self::rendered(new DuplicatePatientGroup(1, $hostile, []))], DuplicatePatientColumn::defaults());

        $this->assertStringNotContainsString('=cmd', $csv);
        $this->assertStringNotContainsString('+HYPERLINK', $csv);
        $this->assertStringNotContainsString('@SUM', $csv);
    }

    #[Test]
    public function quotesCellsSoEmbeddedCommasCannotSplitColumns(): void
    {
        $row = DuplicatePatientRow::forPrimary(self::patientRow([
            'phone_home' => '555-1000',
            'phone_cell' => '555-2000',
        ]), self::HIGHLIGHT);
        $csv = (new DuplicatePatientCsvWriter())->write([self::rendered(new DuplicatePatientGroup(1, $row, []))], DuplicatePatientColumn::defaults());

        $this->assertStringContainsString('"555-1000, 555-2000"', $csv);
    }

    #[Test]
    public function buildsADownloadFilenameFromTheInstanceNameAndTimestamp(): void
    {
        $filename = (new DuplicatePatientCsvWriter())->buildFilename('Clinic', '202608111530');

        $this->assertSame('duplicate_patients_Clinic_202608111530.csv', $filename);
    }
}
