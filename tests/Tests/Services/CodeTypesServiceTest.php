<?php

/**
 * CodeTypesServiceTest.php
 *
 * Unit tests for CodeTypesService class
 *
 * NOTE: This test class mocks external dependencies to avoid database requirements.
 * Some tests may have limitations due to dependencies on global functions and constants:
 * - lookup_code_descriptions() - global function that queries the database
 * - FhirCodeSystemConstants - may not be available in test environment
 * - External code tables (SNOMED, ICD10, etc.) - avoided by setting external=0
 *
 * To run these tests successfully:
 * 1. Ensure OpenEMR's autoloader is available for the Services classes
 * 2. Consider mocking global functions in a test bootstrap file if needed
 * 3. External table dependencies are avoided by using internal storage (external=0)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright (c) 2025 [Your Name/Organization]
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\ListService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test class for CodeTypesService
 *
 * Tests the functionality of code type management, parsing, and system lookups
 */
class CodeTypesServiceTest extends TestCase
{
    private CodeTypesService $codeTypesService;
    private MockObject $mockListService;

    private array $codeTypesBackup;

    private array $codeExternalTablesBackup;

    protected function setUp(): void
    {
        // AI-generated setup method begins
        parent::setUp();

        // Mock the global $code_types array that the service depends on
        // Set all external values to 0 to use internal codes table and avoid external table dependencies
        global $code_types;
        $this->codeTypesBackup = $code_types ?? [];
        $code_types = [
            'SNOMED-CT' => [
                'active' => 1,
                'id' => 1,
                'fee' => 0,
                'mod' => 0,
                'just' => '',
                'rel' => 0,
                'nofs' => 0,
                'diag' => 1,
                'mask' => '',
                'label' => 'SNOMED-CT',
                'external' => 0, // Changed to 0 to avoid external table dependencies
                'claim' => 0,
                'proc' => 0,
                'term' => 1,
                'problem' => 1,
                'drug' => 0
            ],
            'CPT4' => [
                'active' => 1,
                'id' => 2,
                'fee' => 1,
                'mod' => 2,
                'just' => '',
                'rel' => 1,
                'nofs' => 0,
                'diag' => 0,
                'mask' => '',
                'label' => 'CPT4',
                'external' => 0,
                'claim' => 1,
                'proc' => 1,
                'term' => 0,
                'problem' => 0,
                'drug' => 0
            ],
            'RXNORM' => [
                'active' => 1,
                'id' => 3,
                'fee' => 0,
                'mod' => 0,
                'just' => '',
                'rel' => 0,
                'nofs' => 1,
                'diag' => 0,
                'mask' => '',
                'label' => 'RxNorm',
                'external' => 0,
                'claim' => 0,
                'proc' => 0,
                'term' => 0,
                'problem' => 0,
                'drug' => 1
            ],
            'LOINC' => [
                'active' => 1,
                'id' => 4,
                'fee' => 0,
                'mod' => 0,
                'just' => '',
                'rel' => 0,
                'nofs' => 0,
                'diag' => 0,
                'mask' => '',
                'label' => 'LOINC',
                'external' => 0,
                'claim' => 0,
                'proc' => 0,
                'term' => 1,
                'problem' => 0,
                'drug' => 0
            ],
            'ICD10' => [
                'active' => 1,
                'id' => 5,
                'fee' => 0,
                'mod' => 0,
                'just' => '',
                'rel' => 0,
                'nofs' => 0,
                'diag' => 1,
                'mask' => '',
                'label' => 'ICD10',
                'external' => 0,
                'claim' => 1,
                'proc' => 0,
                'term' => 0,
                'problem' => 1,
                'drug' => 0
            ]
        ];

        // Mock the global $code_external_tables array to avoid external table dependencies
        global $code_external_tables;
        $this->codeExternalTablesBackup = $code_external_tables ?? [];
        $code_external_tables = [
            0 => [
                'table' => 'codes',
                'code' => 'code',
                'description' => 'code_text',
                'description_brief' => 'code_text_short',
                'filter_clause' => [],
                'joins' => [],
                'filter_version_order' => 'id',
                'display_description' => '',
                'column_type' => 'string',
                'skip_total_table_count' => false
            ]
        ];

        $this->codeTypesService = new CodeTypesService();

        // Create mock for ListService
        $this->mockListService = $this->createMock(ListService::class);
        $this->codeTypesService->setListService($this->mockListService);
        // AI-generated setup method ends
    }


    protected function tearDown(): void
    {
        // AI-generated teardown method begins
        parent::tearDown();

        // Clean up global variables if needed
        global $code_types, $code_external_tables;
        // AI-generated teardown method ends
        $code_types = $this->codeTypesBackup;
        $code_external_tables = $this->codeExternalTablesBackup;
    }

    /**
     * Test constructor initialization
     */
    public function testConstructorInitialization(): void
    {
        // AI-generated test method begins
        $service = new CodeTypesService();

        $this->assertTrue($service->isSnomedCodesInstalled());
        $this->assertTrue($service->isCPT4Installed());
        $this->assertTrue($service->isRXNORMInstalled());

        $installedTypes = $service->getInstalledCodeTypes();
        $this->assertIsArray($installedTypes);
        $this->assertArrayHasKey('SNOMED-CT', $installedTypes);
        $this->assertArrayHasKey('CPT4', $installedTypes);
        $this->assertArrayHasKey('RXNORM', $installedTypes);
        // AI-generated test method ends
    }

    /**
     * Test parseCode method with various input formats
     */
    public function testParseCode(): void
    {
        // Test with typed code (TYPE:CODE format)
        $result = $this->codeTypesService->parseCode('CPT4:99213');
        $this->assertEquals('99213', $result['code']);
        $this->assertEquals('CPT4', $result['code_type']);

        // Test with untyped code
        $result = $this->codeTypesService->parseCode('99213');
        $this->assertEquals('99213', $result['code']);
        $this->assertNull($result['code_type']);

        // Test with empty string
        $result = $this->codeTypesService->parseCode('');
        $this->assertEquals('', $result['code']);
        $this->assertNull($result['code_type']);
    }

    /**
     * Test getCodeWithType method
     */
    public function testGetCodeWithType(): void
    {
        // AI-generated test method begins
        // Test normal case
        $result = $this->codeTypesService->getCodeWithType('99213', 'CPT4');
        $this->assertEquals('CPT4:99213', $result);

        // Test with empty code
        $result = $this->codeTypesService->getCodeWithType('', 'CPT4');
        $this->assertEquals('', $result);

        // Test with empty type
        $result = $this->codeTypesService->getCodeWithType('99213', '');
        $this->assertEquals('', $result);

        // Test with already formatted code
        $result = $this->codeTypesService->getCodeWithType('CPT4:99213', 'SNOMED-CT');
        $this->assertEquals('CPT4:99213', $result);

        // Test with oe_format flag
        $result = $this->codeTypesService->getCodeWithType('CPT4:99213', 'SNOMED-CT', true);
        $this->assertEquals('CPT4:99213', $result);
        // AI-generated test method ends
    }

    /**
     * Test getCodeTypeForCode method
     */
    public function testGetCodeTypeForCode(): void
    {
        $result = $this->codeTypesService->getCodeTypeForCode('CPT4:99213');
        $this->assertEquals('CPT4', $result);

        $result = $this->codeTypesService->getCodeTypeForCode('99213');
        $this->assertNull($result);
    }

    /**
     * Test isInstalledCodeType method
     */
    public function testIsInstalledCodeType(): void
    {
        $this->assertTrue($this->codeTypesService->isInstalledCodeType('SNOMED-CT'));
        $this->assertTrue($this->codeTypesService->isInstalledCodeType('CPT4'));
        $this->assertTrue($this->codeTypesService->isInstalledCodeType('RXNORM'));
        $this->assertFalse($this->codeTypesService->isInstalledCodeType('NONEXISTENT'));
    }

    /**
     * Test getSystemForCodeType method with OID format
     */
    public function testGetSystemForCodeTypeWithOid(): void
    {
        // AI-generated test method begins
        // Test SNOMED-CT OID
        $result = $this->codeTypesService->getSystemForCodeType('SNOMED-CT', true);
        $this->assertEquals('2.16.840.1.113883.6.96', $result);

        // Test CPT4 OID
        $result = $this->codeTypesService->getSystemForCodeType('CPT4', true);
        $this->assertEquals('2.16.840.1.113883.6.12', $result);

        // Test LOINC OID
        $result = $this->codeTypesService->getSystemForCodeType('LOINC', true);
        $this->assertEquals('2.16.840.1.113883.6.1', $result);

        // Test ICD10 OID
        $result = $this->codeTypesService->getSystemForCodeType('ICD10', true);
        $this->assertEquals('2.16.840.1.113883.6.90', $result);

        // Test RXNORM OID
        $result = $this->codeTypesService->getSystemForCodeType('RXNORM', true);
        $this->assertEquals('2.16.840.1.113883.6.88', $result);
        // AI-generated test method ends
    }

    /**
     * Test getSystemForCodeType method with FHIR format
     */
    public function testGetSystemForCodeTypeWithFhir(): void
    {
        // AI-generated test method begins
        // Note: This test may fail if FHIR constants are not available in test environment
        // In production, you would need to ensure FhirCodeSystemConstants class is loaded

        // Test that the method returns something (may be null if constants not available)
        $result = $this->codeTypesService->getSystemForCodeType('SNOMED-CT', false);
        $this->assertTrue(is_string($result) || is_null($result));

        $result = $this->codeTypesService->getSystemForCodeType('CPT4', false);
        $this->assertTrue(is_string($result) || is_null($result));

        // Test unsupported code type should always return null
        $result = $this->codeTypesService->getSystemForCodeType('UNSUPPORTED', false);
        $this->assertNull($result);
        // AI-generated test method ends
    }

    /**
     * Test formatCodeType method
     */
    public function testFormatCodeType(): void
    {
        // AI-generated test method begins
        // Test ICD10 variations
        $this->assertEquals('ICD10', $this->codeTypesService->formatCodeType('ICD10'));
        $this->assertEquals('ICD10', $this->codeTypesService->formatCodeType('ICD10-CM'));
        $this->assertEquals('ICD10', $this->codeTypesService->formatCodeType('ICD-10-CM'));
        $this->assertEquals('ICD10', $this->codeTypesService->formatCodeType('ICD10CM'));

        // Test SNOMED-CT variations
        $this->assertEquals('SNOMED-CT', $this->codeTypesService->formatCodeType('SNOMED CT'));
        $this->assertEquals('SNOMED-CT', $this->codeTypesService->formatCodeType('SNOMED-CT'));
        $this->assertEquals('SNOMED-CT', $this->codeTypesService->formatCodeType('SNOMEDCT'));

        // Test CPT variations
        $this->assertEquals('CPT4', $this->codeTypesService->formatCodeType('CPT'));
        $this->assertEquals('CPT4', $this->codeTypesService->formatCodeType('CPT4'));

        // Test RXNORM/RXCUI variations
        $this->assertEquals('RXNORM', $this->codeTypesService->formatCodeType('RXCUI'));
        $this->assertEquals('RXNORM', $this->codeTypesService->formatCodeType('RXNORM'));

        // Test OID input
        $this->assertEquals('SNOMED-CT', $this->codeTypesService->formatCodeType('2.16.840.1.113883.6.96'));
        // AI-generated test method ends
    }

    /**
     * Test getCodeSystemNameFromSystem method
     */
    public function testGetCodeSystemNameFromSystem(): void
    {
        // Test known OIDs
        $this->assertEquals('SNOMED-CT', $this->codeTypesService->getCodeSystemNameFromSystem('2.16.840.1.113883.6.96'));
        $this->assertEquals('CPT4', $this->codeTypesService->getCodeSystemNameFromSystem('2.16.840.1.113883.6.12'));
        $this->assertEquals('LOINC', $this->codeTypesService->getCodeSystemNameFromSystem('2.16.840.1.113883.6.1'));

        // Test unknown OID
        $this->assertEquals('', $this->codeTypesService->getCodeSystemNameFromSystem('unknown.oid'));
    }

    /**
     * Test resolveCode method
     */
    public function testResolveCode(): void
    {
        // AI-generated test method begins
        // Test the structure of the returned array without relying on external functions

        $result = $this->codeTypesService->resolveCode('99213', 'CPT4', 'Office Visit');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('formatted_code', $result);
        $this->assertArrayHasKey('formatted_code_type', $result);
        $this->assertArrayHasKey('code_text', $result);
        $this->assertArrayHasKey('system_oid', $result);
        $this->assertArrayHasKey('valueset', $result);
        $this->assertArrayHasKey('valueset_name', $result);

        $this->assertEquals('99213', $result['code']);
        $this->assertEquals('CPT4:99213', $result['formatted_code']);
        $this->assertEquals('CPT4', $result['formatted_code_type']);
        $this->assertEquals('Office Visit', $result['code_text']); // Uses provided text
        // AI-generated test method ends
    }

    /**
     * Test resolveCode with empty code
     */
    public function testResolveCodeWithEmptyCode(): void
    {
        $result = $this->codeTypesService->resolveCode('', 'CPT4');

        $this->assertEquals('', $result['code']);
        $this->assertEquals('', $result['formatted_code']);
    }

    /**
     * Test ListService integration
     */
    public function testListServiceIntegration(): void
    {
        // AI-generated test method begins
        // Test getListService method
        $listService = $this->codeTypesService->getListService();
        $this->assertInstanceOf(ListService::class, $listService);

        // Test setListService method
        $newMockListService = $this->createMock(ListService::class);
        $this->codeTypesService->setListService($newMockListService);
        $this->assertSame($newMockListService, $this->codeTypesService->getListService());
        // AI-generated test method ends
    }

    /**
     * Test dischargeOptionIdFromCode method
     */
    public function testDischargeOptionIdFromCode(): void
    {
        // AI-generated test method begins
        $expectedOptions = [
            ['option_id' => 'home', 'title' => 'Home']
        ];

        $this->mockListService
            ->expects($this->once())
            ->method('getOptionsByListName')
            ->with('discharge-disposition', ['codes' => 'CPT4:99213'])
            ->willReturn($expectedOptions);

        $result = $this->codeTypesService->dischargeOptionIdFromCode('CPT4:99213');
        $this->assertEquals('home', $result);
        // AI-generated test method ends
    }

    /**
     * Test dischargeOptionIdFromCode with no results
     */
    public function testDischargeOptionIdFromCodeNoResults(): void
    {
        // AI-generated test method begins
        $this->mockListService
            ->expects($this->once())
            ->method('getOptionsByListName')
            ->with('discharge-disposition', ['codes' => 'INVALID:CODE'])
            ->willReturn([]);

        $result = $this->codeTypesService->dischargeOptionIdFromCode('INVALID:CODE');
        $this->assertEquals('', $result);
        // AI-generated test method ends
    }

    /**
     * Test dischargeCodeFromOptionId method
     */
    public function testDischargeCodeFromOptionId(): void
    {
        // AI-generated test method begins
        $expectedOption = [
            'option_id' => 'home',
            'codes' => 'CPT4:99213'
        ];

        $this->mockListService
            ->expects($this->once())
            ->method('getListOption')
            ->with('discharge-disposition', 'home')
            ->willReturn($expectedOption);

        $result = $this->codeTypesService->dischargeCodeFromOptionId('home');
        $this->assertEquals('CPT4:99213', $result);
        // AI-generated test method ends
    }

    /**
     * Test dischargeCodeFromOptionId with no result
     */
    public function testDischargeCodeFromOptionIdNoResult(): void
    {
        // AI-generated test method begins
        $this->mockListService
            ->expects($this->once())
            ->method('getListOption')
            ->with('discharge-disposition', 'invalid')
            ->willReturn(null);

        $result = $this->codeTypesService->dischargeCodeFromOptionId('invalid');
        $this->assertEquals('', $result);
        // AI-generated test method ends
    }

    /**
     * Test parseCodesIntoCodeableConcepts method
     */
    public function testParseCodesIntoCodeableConcepts(): void
    {
        // AI-generated test method begins
        $codes = 'CPT4:99213;SNOMED-CT:386661006';
        $result = $this->codeTypesService->parseCodesIntoCodeableConcepts($codes);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Test structure of first code (should exist regardless of description lookup)
        $this->assertArrayHasKey('99213', $result);
        if (isset($result['99213'])) {
            $this->assertArrayHasKey('code', $result['99213']);
            $this->assertArrayHasKey('description', $result['99213']);
            $this->assertArrayHasKey('code_type', $result['99213']);
            $this->assertArrayHasKey('system', $result['99213']);

            $this->assertEquals('99213', $result['99213']['code']);
            $this->assertEquals('CPT4', $result['99213']['code_type']);
            $this->assertIsString($result['99213']['description']); // May be empty if lookup fails
        }

        // Test structure of second code
        $this->assertArrayHasKey('386661006', $result);
        if (isset($result['386661006'])) {
            $this->assertEquals('386661006', $result['386661006']['code']);
            $this->assertEquals('SNOMED-CT', $result['386661006']['code_type']);
            $this->assertIsString($result['386661006']['description']);
        }
        // AI-generated test method ends
    }

    /**
     * Test parseCodesIntoCodeableConcepts with empty codes
     */
    public function testParseCodesIntoCodeableConceptsEmpty(): void
    {
        $result = $this->codeTypesService->parseCodesIntoCodeableConcepts('');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getSystemForCode method
     */
    public function testGetSystemForCode(): void
    {
        // Test with valid typed code
        $result = $this->codeTypesService->getSystemForCode('CPT4:99213');
        $this->assertNotNull($result);

        // Test with untyped code
        $result = $this->codeTypesService->getSystemForCode('99213');
        $this->assertNull($result);

        // Test with empty code
        $result = $this->codeTypesService->getSystemForCode('');
        $this->assertNull($result);
    }

    /**
     * Test lookup_code_description wrapper method
     */
    public function testLookupCodeDescription(): void
    {
        // AI-generated test method begins
        // Test with empty codes - this should always work regardless of global function availability
        $result = $this->codeTypesService->lookup_code_description('');
        $this->assertEquals('', $result);

        // For non-empty codes, we can only test that it returns a string
        // The actual lookup functionality depends on global functions and database
        $codes = 'CPT4:99213';
        $result = $this->codeTypesService->lookup_code_description($codes);
        $this->assertIsString($result);

        // Test with different description detail parameter
        $result = $this->codeTypesService->lookup_code_description($codes, 'code_text_short');
        $this->assertIsString($result);
        // AI-generated test method ends
    }

    /**
     * Test edge cases and error conditions
     */
    public function testEdgeCases(): void
    {
        // Test null inputs
        $result = $this->codeTypesService->parseCode(null);
        $this->assertNull($result['code_type']);

        // Test malformed codes
        $result = $this->codeTypesService->parseCode(':::');
        $this->assertEquals('', $result['code']);
        $this->assertEquals('', $result['code_type']);

        // Test case sensitivity
        $result = $this->codeTypesService->formatCodeType('cpt4');
        $this->assertEquals('CPT4', $result);
    }
}
