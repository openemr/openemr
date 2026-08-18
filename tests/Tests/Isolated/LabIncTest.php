<?php

/**
 * Unit tests for library/lab.inc.php helper functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated;

use PHPUnit\Framework\TestCase;

// Load the function file under test (no globals/database needed for pure helpers).
require_once __DIR__ . '/../../../library/lab.inc.php';

/**
 * Tests the pure helper functions in library/lab.inc.php.
 */
class LabIncTest extends TestCase
{
    // ── buildResponsibleParty ───────────────────────────────────────────

    /**
     * Test billing type 'C' (Clinic) returns facility data.
     */
    public function testBuildResponsiblePartyClinicBilling(): void
    {
        $facility = [
            'name'        => 'Acme Clinic',
            'street'      => '123 Main St',
            'city'        => 'Springfield',
            'state'       => 'IL',
            'postal_code' => '62704',
        ];

        $result = buildResponsibleParty('C', $facility, [], []);

        $this->assertSame('Acme Clinic', $result['name']);
        $this->assertSame('123 Main St', $result['address']);
        $this->assertSame('Springfield, IL 62704', $result['city_st_zip']);
        $this->assertSame('Client Billing', $result['relationship']);
        $this->assertFalse($result['relationship_is_list']);
    }

    /**
     * Test billing type 'P' (Patient) returns patient data.
     */
    public function testBuildResponsiblePartyPatientBilling(): void
    {
        $pdata = [
            'fname'       => 'John',
            'lname'       => 'Doe',
            'street'      => '456 Oak Ave',
            'city'        => 'Chicago',
            'state'       => 'IL',
            'postal_code' => '60601',
        ];

        $result = buildResponsibleParty('P', [], $pdata, []);

        $this->assertSame('John Doe', $result['name']);
        $this->assertSame('456 Oak Ave', $result['address']);
        $this->assertSame('Chicago, IL 60601', $result['city_st_zip']);
        $this->assertSame('Self', $result['relationship']);
        $this->assertFalse($result['relationship_is_list']);
    }

    /**
     * Test billing type 'T' (Third Party/Insurance) returns subscriber data.
     */
    public function testBuildResponsiblePartyInsuranceBilling(): void
    {
        $ins = [
            'subscriber_fname'        => 'Jane',
            'subscriber_lname'        => 'Smith',
            'line1'                   => '789 Elm Blvd',
            'city'                    => 'Peoria',
            'state'                   => 'IL',
            'zip'                     => '61602',
            'subscriber_relationship' => 'spouse',
        ];

        $result = buildResponsibleParty('T', [], [], $ins);

        $this->assertSame('Jane Smith', $result['name']);
        $this->assertSame('789 Elm Blvd', $result['address']);
        $this->assertSame('Peoria, IL 61602', $result['city_st_zip']);
        $this->assertSame('spouse', $result['relationship']);
        $this->assertTrue($result['relationship_is_list']);
    }

    /**
     * Test billing type 'T' with empty insurance returns empty array.
     */
    public function testBuildResponsiblePartyInsuranceEmptyReturnsEmpty(): void
    {
        $result = buildResponsibleParty('T', [], [], []);

        $this->assertSame([
            'name' => '',
            'address' => '',
            'city_st_zip' => '',
            'relationship' => '',
            'relationship_is_list' => false,
        ], $result);
    }

    /**
     * Test unknown billing type returns empty array.
     */
    public function testBuildResponsiblePartyUnknownTypeReturnsEmpty(): void
    {
        $result = buildResponsibleParty('X', [], [], []);

        $this->assertSame([
            'name' => '',
            'address' => '',
            'city_st_zip' => '',
            'relationship' => '',
            'relationship_is_list' => false,
        ], $result);
    }

    /**
     * Test empty billing type returns empty array.
     */
    public function testBuildResponsiblePartyEmptyTypeReturnsEmpty(): void
    {
        $result = buildResponsibleParty('', [], [], []);

        $this->assertSame([
            'name' => '',
            'address' => '',
            'city_st_zip' => '',
            'relationship' => '',
            'relationship_is_list' => false,
        ], $result);
    }

    /**
     * Test that partial data doesn't cause errors — missing keys default to empty strings.
     */
    public function testBuildResponsiblePartyPartialFacilityData(): void
    {
        $facility = ['name' => 'Test Clinic'];

        $result = buildResponsibleParty('C', $facility, [], []);

        $this->assertSame('Test Clinic', $result['name']);
        $this->assertSame('', $result['address']);
        $this->assertSame(',', $result['city_st_zip']);
        $this->assertSame('Client Billing', $result['relationship']);
    }

    /**
     * Test that partial patient data handles missing keys gracefully.
     */
    public function testBuildResponsiblePartyPartialPatientData(): void
    {
        $pdata = ['fname' => 'Alice'];

        $result = buildResponsibleParty('P', [], $pdata, []);

        $this->assertSame('Alice', $result['name']);
        $this->assertSame('', $result['address']);
        $this->assertSame('Self', $result['relationship']);
    }

    /**
     * Test insurance subscriber_relationship defaults to empty string when missing.
     */
    public function testBuildResponsiblePartyInsuranceMissingRelationship(): void
    {
        $ins = [
            'subscriber_fname' => 'Bob',
            'subscriber_lname' => 'Jones',
            'line1'            => '100 Pine St',
            'city'             => 'Dallas',
            'state'            => 'TX',
            'zip'              => '75201',
        ];

        $result = buildResponsibleParty('T', [], [], $ins);

        $this->assertSame('Bob Jones', $result['name']);
        $this->assertSame('', $result['relationship']);
        $this->assertTrue($result['relationship_is_list']);
    }

    /**
     * Test getProcedureOrderAnswers is defined for requisition AOE rendering.
     */
    public function testGetProcedureOrderAnswersFunctionExists(): void
    {
        $this->assertTrue(function_exists('getProcedureOrderAnswers'));
        $this->assertTrue(function_exists('getLabconfig'));
        $this->assertTrue(function_exists('getProcedureBillingType'));
        $this->assertTrue(function_exists('buildResponsibleParty'));
        $this->assertTrue(function_exists('lab_as_string'));
        $this->assertTrue(function_exists('lab_normalize_row'));
        $this->assertTrue(function_exists('lab_normalize_array_row'));
        $this->assertTrue(function_exists('lab_normalize_rows'));
    }

    // ── lab_as_string / lab_normalize_* ────────────────────────────────

    /**
     * Test lab_as_string converts scalars and rejects non-scalars.
     */
    public function testLabAsStringConvertsScalars(): void
    {
        $this->assertSame('hello', lab_as_string('hello'));
        $this->assertSame('42', lab_as_string(42));
        $this->assertSame('3.5', lab_as_string(3.5));
        $this->assertSame('1', lab_as_string(true));
        $this->assertSame('', lab_as_string(false));
        $this->assertSame('', lab_as_string(null));
        $this->assertSame('', lab_as_string(['x']));
    }

    /**
     * Test lab_normalize_row false passthrough and key stringification.
     */
    public function testLabNormalizeRow(): void
    {
        $this->assertFalse(lab_normalize_row(false));

        $row = lab_normalize_row(['0' => 'a', 'name' => 'Clinic', '2' => 9]);
        $this->assertIsArray($row);
        $this->assertArrayHasKey('0', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('2', $row);
        $this->assertSame('a', $row['0']);
        $this->assertSame('Clinic', $row['name']);
        $this->assertSame(9, $row['2']);

        // Numeric keys are stringified.
        $rowNum = lab_normalize_array_row([0 => 'z', 'name' => 'N']);
        $this->assertArrayHasKey('0', $rowNum);
        $this->assertSame('z', $rowNum['0']);
        $this->assertSame('N', $rowNum['name']);
    }

    /**
     * Test lab_normalize_rows normalizes a list of rows.
     */
    public function testLabNormalizeRows(): void
    {
        $rows = lab_normalize_rows([
            ['code' => 'A1', 'seq' => 'x'],
            ['code' => 'B2'],
        ]);
        $this->assertCount(2, $rows);
        $this->assertArrayHasKey('code', $rows[0]);
        $this->assertArrayHasKey('seq', $rows[0]);
        $this->assertSame('A1', $rows[0]['code']);
        $this->assertSame('x', $rows[0]['seq']);
        $this->assertSame('B2', $rows[1]['code']);
    }

    /**
     * Test buildResponsibleParty city/state/zip spacing with partial insurance.
     */
    public function testBuildResponsiblePartyInsuranceCityStZipFormatting(): void
    {
        $ins = [
            'subscriber_fname' => 'Pat',
            'subscriber_lname' => 'Lee',
            'line1' => '1 Main',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78701',
            'subscriber_relationship' => 'self',
        ];
        $result = buildResponsibleParty('T', [], [], $ins);
        $this->assertSame('Austin, TX 78701', $result['city_st_zip']);
        $this->assertSame('1 Main', $result['address']);
        $this->assertTrue($result['relationship_is_list']);
    }

}
