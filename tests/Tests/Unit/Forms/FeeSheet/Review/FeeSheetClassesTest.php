<?php

/**
 * FeeSheetClassesTest.php
 *
 * @package   OpenEMR
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEmr Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Forms\FeeSheet\Review;

use OpenEMR\Forms\FeeSheet\Review\CodeInfo;
use OpenEMR\Forms\FeeSheet\Review\EncounterInfo;
use OpenEMR\Forms\FeeSheet\Review\Procedure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeInfo::class)]
#[CoversClass(Procedure::class)]
#[CoversClass(EncounterInfo::class)]
#[Group('isolated')]
class FeeSheetClassesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up minimal code_types global for check_code_set_filters()
        $GLOBALS['code_types'] = [
            'ICD10' => [
                'active' => 1,
                'diag' => 1,
                'problem' => 1,
                'fee' => 0,
            ],
            'CPT4' => [
                'active' => 1,
                'diag' => 0,
                'problem' => 0,
                'fee' => 1,
            ],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['code_types']);
        parent::tearDown();
    }

    public function testCodeInfoConstruction(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        $this->assertEquals('A01.0', $codeInfo->code);
        $this->assertEquals('ICD10', $codeInfo->code_type);
        $this->assertEquals('Typhoid fever', $codeInfo->description);
        $this->assertTrue($codeInfo->selected);
    }

    public function testCodeInfoConstructionWithSelectedFalse(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever', false);

        $this->assertFalse($codeInfo->selected);
    }

    public function testCodeInfoGetKey(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        $this->assertEquals('ICD10|A01.0', $codeInfo->getKey());
    }

    public function testCodeInfoGetCode(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        $this->assertEquals('A01.0', $codeInfo->getCode());
    }

    public function testCodeInfoGetCodeType(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        $this->assertEquals('ICD10', $codeInfo->getCode_type());
    }

    public function testCodeInfoAddArrayParams(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');
        $arr = ['existing'];

        $codeInfo->addArrayParams($arr);

        $this->assertEquals(['existing', 'ICD10', 'A01.0', 'Typhoid fever'], $arr);
    }

    public function testCodeInfoAllowedToCreateProblemFromDiagnosis(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        // ICD10 has problem=1 in our mock, so this should be TRUE
        $this->assertEquals('TRUE', $codeInfo->allowed_to_create_problem_from_diagnosis);
    }

    public function testCodeInfoAllowedToCreateDiagnosisFromProblem(): void
    {
        $codeInfo = new CodeInfo('A01.0', 'ICD10', 'Typhoid fever');

        // ICD10 has diag=1 in our mock, so this should be TRUE
        $this->assertEquals('TRUE', $codeInfo->allowed_to_create_diagnosis_from_problem);
    }

    public function testProcedureConstruction(): void
    {
        $procedure = new Procedure(
            '99213',
            'CPT4',
            'Office visit',
            75.00,
            'ICD10:A01.0',
            '25',
            1,
            2,
            ''
        );

        $this->assertEquals('99213', $procedure->code);
        $this->assertEquals('CPT4', $procedure->code_type);
        $this->assertEquals('Office visit', $procedure->description);
        $this->assertEquals(75.00, $procedure->fee);
        $this->assertEquals('ICD10:A01.0', $procedure->justify);
        $this->assertEquals('25', $procedure->modifiers);
        $this->assertEquals(1, $procedure->units);
        $this->assertEquals(2, $procedure->mod_size);
        $this->assertTrue($procedure->selected);
    }

    public function testProcedureInheritsFromCodeInfo(): void
    {
        $procedure = new Procedure(
            '99213',
            'CPT4',
            'Office visit',
            75.00,
            'ICD10:A01.0',
            '25',
            1,
            2,
            ''
        );

        // Test inherited methods from CodeInfo
        $this->assertEquals('CPT4|99213', $procedure->getKey());
        $this->assertEquals('99213', $procedure->getCode());
        $this->assertEquals('CPT4', $procedure->getCode_type());
    }

    public function testProcedureAddProcParameters(): void
    {
        $procedure = new Procedure(
            '99213',
            'CPT4',
            'Office visit',
            75.00,
            'ICD10:A01.0',
            '25',
            1,
            2,
            ''
        );
        $params = ['existing'];

        $procedure->addProcParameters($params);

        $this->assertEquals(['existing', '25', 1, 75.00, 'ICD10:A01.0'], $params);
    }

    public function testEncounterInfoConstruction(): void
    {
        $encounterInfo = new EncounterInfo(123, '2026-01-05');

        $this->assertEquals(123, $encounterInfo->id);
        $this->assertEquals('2026-01-05', $encounterInfo->date);
    }

    public function testEncounterInfoGetID(): void
    {
        $encounterInfo = new EncounterInfo(456, '2026-01-05');

        $this->assertEquals(456, $encounterInfo->getID());
    }
}
