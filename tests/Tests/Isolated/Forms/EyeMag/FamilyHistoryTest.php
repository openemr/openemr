<?php

/**
 * The eye exam's family history panel: what it shows and what it saves.
 *
 * The medical rows (cancer, diabetes, HTN, ...) were shown only for fields
 * the History layout lists as used in group 3, so a practice that hid them
 * there saved them without ever seeing them again. And every panel save
 * wrote relatives_mental_illness and relatives_suicide, which the panel has
 * no field for, clearing them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\FamilyHistory;
use PHPUnit\Framework\TestCase;

class FamilyHistoryTest extends TestCase
{
    public function testRecordedMedicalEntriesComeFromTheHistoryRowAlone(): void
    {
        $history = [
            'relatives_cancer' => 'mother',
            'relatives_diabetes' => 'Y',
            'relatives_high_blood_pressure' => '',
            'relatives_epilepsy' => '0',
            'relatives_stroke' => null,
            'relatives_suicide' => 'uncle',
            'usertext11' => 'glaucoma, father',
            'relatives_strabismus' => 'brother',
        ];

        $this->assertSame(
            ['relatives_cancer' => 'mother', 'relatives_diabetes' => 'Y', 'relatives_suicide' => 'uncle'],
            FamilyHistory::recordedMedical($history)
        );
    }

    public function testRecordedMedicalEntriesKeepTheStockOrder(): void
    {
        $history = ['relatives_stroke' => 'Y', 'relatives_cancer' => 'Y', 'relatives_diabetes' => 'Y'];

        $this->assertSame(
            ['relatives_cancer', 'relatives_diabetes', 'relatives_stroke'],
            array_keys(FamilyHistory::recordedMedical($history))
        );
    }

    public function testDisplayIsTheRawValueForThePanelsToEscape(): void
    {
        $this->assertSame('mother & father', FamilyHistory::display('mother & father'));
    }

    public function testDisplayCutsLongValuesByCharacter(): void
    {
        $display = FamilyHistory::display(str_repeat('a', 99) . 'éé');

        $this->assertSame(str_repeat('a', 99) . 'é', $display);
        $this->assertTrue(mb_check_encoding($display, 'UTF-8'));
    }

    public function testSaveWritesOnlyThePostedFamilyHistoryColumns(): void
    {
        // What the panel posts: its six medical fields, the eye fields, and
        // other inputs of the same form.
        $posted = [
            'form_type' => 'FH',
            'relatives_cancer' => 'mother',
            'relatives_diabetes' => '',
            'relatives_high_blood_pressure' => 'Y',
            'relatives_heart_problems' => '',
            'relatives_stroke' => '',
            'relatives_epilepsy' => 'sister',
            'usertext11' => 'father',
            'usertext18' => '',
            'radio_relatives_diabetes' => 'on',
        ];

        $columns = FamilyHistory::postedColumns($posted);

        $this->assertSame(
            [
                'relatives_cancer' => 'mother',
                'relatives_diabetes' => '',
                'relatives_high_blood_pressure' => 'Y',
                'relatives_heart_problems' => '',
                'relatives_stroke' => '',
                'relatives_epilepsy' => 'sister',
                'usertext11' => 'father',
                'usertext18' => '',
            ],
            $columns
        );
        $this->assertArrayNotHasKey('relatives_mental_illness', $columns);
        $this->assertArrayNotHasKey('relatives_suicide', $columns);
    }

    public function testNonStringPostedValueIsNull(): void
    {
        $this->assertSame(['relatives_cancer' => null], FamilyHistory::postedColumns(['relatives_cancer' => ['x']]));
    }

    public function testUpdateKeepsAColumnWhoseValueIsNull(): void
    {
        $sql = FamilyHistory::updateSql();

        $this->assertStringStartsWith('UPDATE history_data SET `relatives_cancer` = COALESCE(?, `relatives_cancer`), ', $sql);
        $this->assertStringContainsString('`relatives_suicide` = COALESCE(?, `relatives_suicide`)', $sql);
        $this->assertStringEndsWith('`usertext18` = COALESCE(?, `usertext18`) WHERE pid = ?', $sql);
        $this->assertSame(17, substr_count($sql, 'COALESCE(?, '));
    }

    public function testUpdateValuesFollowTheColumnOrderWithNullForUnposted(): void
    {
        $values = FamilyHistory::updateValues(['relatives_diabetes' => '', 'relatives_cancer' => 'mother', 'usertext18' => 'RD']);

        $this->assertSame(
            ['mother', null, '', null, null, null, null, null, null, null, null, null, null, null, null, null, 'RD'],
            $values
        );
        $this->assertCount(substr_count(FamilyHistory::updateSql(), '?') - 1, $values);
    }

    public function testNothingPostedWritesNothing(): void
    {
        $this->assertSame([], FamilyHistory::postedColumns(['form_type' => 'FH']));
    }
}
