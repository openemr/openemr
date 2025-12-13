<?php

/*
 * CodeTypesServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use InvalidArgumentException;
use OpenEMR\Services\CodeTypesService;
use PHPUnit\Framework\TestCase;

class CodeTypesServiceTest extends TestCase
{
    private $codeTypesService;
    private $originalCodeTypes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codeTypesService = new CodeTypesService();

        // Store original global $code_types if it exists
        global $code_types;
        $this->originalCodeTypes = $code_types ?? null;
    }

    protected function tearDown(): void
    {
        // Restore original global $code_types
        global $code_types;
        $code_types = $this->originalCodeTypes;
        parent::tearDown();
    }

    public function testFormatCodeType(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetCodeWithType(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testResolveCode(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testLookup_code_description(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testLookupFromValueset(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetSystemForCode(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testParseCode(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetCodeSystemNameFromSystem(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testDischargeCodeFromOptionId(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testIsSnomedCodesInstalled(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetInstalledCodeTypes(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetCodeTypeForCode(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testIsCPT4Installed(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testDischargeOptionIdFromCode(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testParseCodesIntoCodeableConcepts(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testIsInstalledCodeType(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testGetSystemForCodeType(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    public function testIsRXNORMInstalled(): void
    {
        $this->markTestIncomplete("This test has not been implemented yet.");
    }

    /**
     * AI Generated Tests for collectCodeTypes method
     * Set up mock code types for testing
     */
    private function setupMockCodeTypes(): void
    {
        global $code_types;
        $code_types = [
            'ICD10' => [
                'active' => true,
                'diag' => true,
                'proc' => false,
                'term' => false,
                'problem' => true,
                'drug' => false
            ],
            'CPT4' => [
                'active' => true,
                'diag' => false,
                'proc' => true,
                'term' => false,
                'problem' => false,
                'drug' => false
            ],
            'SNOMED-CT' => [
                'active' => true,
                'diag' => true,
                'proc' => true,
                'term' => true,
                'problem' => true,
                'drug' => false
            ],
            'RXNORM' => [
                'active' => true,
                'diag' => false,
                'proc' => false,
                'term' => false,
                'problem' => false,
                'drug' => true
            ],
            'INACTIVE_CODE' => [
                'active' => false,
                'diag' => true,
                'proc' => true,
                'term' => true,
                'problem' => true,
                'drug' => true
            ],
            'LOINC' => [
                'active' => true,
                'diag' => false,
                'proc' => false,
                'term' => true,
                'problem' => false,
                'drug' => false
            ]
        ];
    }

    public function testCollectCodeTypesDiagnosisCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'array');

        $expected = ['ICD10', 'SNOMED-CT'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesProcedureCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('procedure', 'array');

        $expected = ['CPT4', 'SNOMED-CT'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesClinicalTermCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('clinical_term', 'array');

        $expected = ['SNOMED-CT', 'LOINC'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesActiveCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('active', 'array');

        $expected = ['ICD10', 'CPT4', 'SNOMED-CT', 'RXNORM', 'LOINC'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesMedicalProblemCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('medical_problem', 'array');

        $expected = ['ICD10', 'SNOMED-CT'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesDrugCategory(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('drug', 'array');

        $expected = ['RXNORM'];
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesExcludesInactiveCodeTypes(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'array');

        // Should not include 'INACTIVE_CODE' even though it has diag=true
        $this->assertNotContains('INACTIVE_CODE', $result);
        $this->assertEquals(['ICD10', 'SNOMED-CT'], $result);
    }

    public function testCollectCodeTypesCsvFormat(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'csv');

        // Assuming csv_like_join function joins with commas
        $expected = 'ICD10,SNOMED-CT';
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesCsvFormatSingleResult(): void
    {
        $this->setupMockCodeTypes();

        $result = $this->codeTypesService->collectCodeTypes('drug', 'csv');

        $expected = 'RXNORM';
        $this->assertEquals($expected, $result);
    }

    public function testCollectCodeTypesDefaultReturnFormat(): void
    {
        $this->setupMockCodeTypes();

        // Test that default format is array when no return_format specified
        $result = $this->codeTypesService->collectCodeTypes('diagnosis');

        $this->assertIsArray($result);
        $this->assertEquals(['ICD10', 'SNOMED-CT'], $result);
    }

    public function testCollectCodeTypesEmptyCodeTypesGlobal(): void
    {
        global $code_types;
        $code_types = [];

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'array');

        $this->assertEquals([], $result);
    }

    public function testCollectCodeTypesWithCodeTypesMissingFlags(): void
    {
        global $code_types;
        $code_types = [
            'INCOMPLETE_CODE' => [
                'active' => true
                // Missing diag, proc, term, etc. flags
            ]
        ];

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'array');

        // Should return empty array since 'diag' flag is missing/false
        $this->assertEquals([], $result);
    }

    public function testCollectCodeTypesPreservesOrder(): void
    {
        global $code_types;
        $code_types = [
            'ZCODE' => [
                'active' => true,
                'diag' => true,
                'proc' => false,
                'term' => false,
                'problem' => false,
                'drug' => false
            ],
            'ACODE' => [
                'active' => true,
                'diag' => true,
                'proc' => false,
                'term' => false,
                'problem' => false,
                'drug' => false
            ],
            'MCODE' => [
                'active' => true,
                'diag' => true,
                'proc' => false,
                'term' => false,
                'problem' => false,
                'drug' => false
            ]
        ];

        $result = $this->codeTypesService->collectCodeTypes('diagnosis', 'array');

        // Should preserve the order from the global array
        $expected = ['ZCODE', 'ACODE', 'MCODE'];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test all categories in one test to ensure comprehensive coverage
     */
    public function testAllCategoriesComprehensive(): void
    {
        $this->setupMockCodeTypes();

        $testCases = [
            'diagnosis' => ['ICD10', 'SNOMED-CT'],
            'procedure' => ['CPT4', 'SNOMED-CT'],
            'clinical_term' => ['SNOMED-CT', 'LOINC'],
            'active' => ['ICD10', 'CPT4', 'SNOMED-CT', 'RXNORM', 'LOINC'],
            'medical_problem' => ['ICD10', 'SNOMED-CT'],
            'drug' => ['RXNORM']
        ];

        foreach ($testCases as $category => $expected) {
            $result = $this->codeTypesService->collectCodeTypes($category, 'array');
            $this->assertEquals($expected, $result, "Failed for category: $category");
        }
    }

    // end AI Generated Tests for collectCodeTypes method

    public function testCollectCodeTypesInvalidReturnTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->codeTypesService->collectCodeTypes("diagnosis", "invalid_format");
    }

    public function testCollectCodeTypesInvalidCategoryThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->codeTypesService->collectCodeTypes("invalid_category");
    }
}
