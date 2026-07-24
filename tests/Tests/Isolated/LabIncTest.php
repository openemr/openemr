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

        $this->assertSame([], $result);
    }

    /**
     * Test unknown billing type returns empty array.
     */
    public function testBuildResponsiblePartyUnknownTypeReturnsEmpty(): void
    {
        $result = buildResponsibleParty('X', [], [], []);

        $this->assertSame([], $result);
    }

    /**
     * Test empty billing type returns empty array.
     */
    public function testBuildResponsiblePartyEmptyTypeReturnsEmpty(): void
    {
        $result = buildResponsibleParty('', [], [], []);

        $this->assertSame([], $result);
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
}
