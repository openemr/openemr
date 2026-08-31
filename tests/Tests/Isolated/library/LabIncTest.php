<?php

/**
 * Unit tests for library/lab.inc.php helper functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

// Load the function file under test (no globals/database needed for pure helpers).
require_once __DIR__ . '/../../../../library/lab.inc.php';

/**
 * Tests the pure helper functions in library/lab.inc.php.
 */
#[Small]
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
        $this->assertSame('', $result['city_st_zip']);
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

        $this->assertSame(
            ['id' => 'a', 'name' => 'Clinic', 'count' => 9],
            lab_normalize_row(['id' => 'a', 'name' => 'Clinic', 'count' => 9])
        );

        $this->assertSame(
            ['id' => 'z', 'name' => 'N'],
            lab_normalize_array_row(['id' => 'z', 'name' => 'N'])
        );
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
        $this->assertSame(
            [
                ['code' => 'A1', 'seq' => 'x'],
                ['code' => 'B2'],
            ],
            $rows
        );
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

    // ── coercion / formatting helpers ──────────────────────────────────

    /**
     * @return array<string, array{mixed, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonNegativeIntProvider(): array
    {
        return [
            'int' => [42, 42],
            'zero' => [0, 0],
            'negative int' => [-3, 0],
            'digit string' => ['17', 17],
            'empty string' => ['', 0],
            'alpha string' => ['abc', 0],
            'null' => [null, 0],
            'float' => [3.2, 0],
            'bool' => [true, 0],
        ];
    }

    #[DataProvider('nonNegativeIntProvider')]
    public function testLabCoerceNonNegativeInt(mixed $input, int $expected): void
    {
        $this->assertSame($expected, lab_coerce_non_negative_int($input));
    }

    /**
     * @return array<string, array{mixed, int|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function optionalIntProvider(): array
    {
        return [
            'int' => [9, 9],
            'digit string' => ['8', 8],
            'empty string' => ['', null],
            'null' => [null, null],
            'alpha' => ['x', null],
        ];
    }

    #[DataProvider('optionalIntProvider')]
    public function testLabCoerceOptionalInt(mixed $input, ?int $expected): void
    {
        $this->assertSame($expected, lab_coerce_optional_int($input));
    }

    public function testLabCoerceProviderIdString(): void
    {
        $this->assertSame('12', lab_coerce_provider_id_string(12));
        $this->assertSame('ab', lab_coerce_provider_id_string('ab'));
        $this->assertSame('', lab_coerce_provider_id_string(''));
        $this->assertSame('', lab_coerce_provider_id_string(null));
        $this->assertSame('', lab_coerce_provider_id_string([]));
    }

    public function testLabFormatCityStateZip(): void
    {
        $this->assertSame('Austin, TX 78701', lab_format_city_state_zip('Austin', 'TX', '78701'));
        $this->assertSame('Austin 78701', lab_format_city_state_zip('Austin', '', '78701'));
        $this->assertSame('TX 78701', lab_format_city_state_zip('', 'TX', '78701'));
        $this->assertSame('', lab_format_city_state_zip('', '', ''));
        $this->assertSame('78701', lab_format_city_state_zip('', '', '78701'));
    }

    public function testLabFormatPersonName(): void
    {
        $this->assertSame('Jane Doe', lab_format_person_name('Jane', 'Doe'));
        $this->assertSame('Jane', lab_format_person_name('Jane', ''));
        $this->assertSame('Doe', lab_format_person_name('', 'Doe'));
        $this->assertSame('', lab_format_person_name('', ''));
    }

    public function testLabHasResponsibleParty(): void
    {
        $this->assertFalse(lab_has_responsible_party([
            'name' => '',
            'address' => '',
            'city_st_zip' => '',
            'relationship' => '',
        ]));
        $this->assertTrue(lab_has_responsible_party(['name' => 'Acme']));
        $this->assertTrue(lab_has_responsible_party(['address' => '1 Main']));
        $this->assertTrue(lab_has_responsible_party(['city_st_zip' => 'Austin, TX']));
        $this->assertTrue(lab_has_responsible_party(['relationship' => 'Self']));
    }

    public function testLabResolveExistingBarcode(): void
    {
        $this->assertSame(
            ['found' => true, 'barcode' => '998877'],
            lab_resolve_existing_barcode(['req_id' => '998877'])
        );
        $this->assertSame(
            ['found' => false, 'barcode' => ''],
            lab_resolve_existing_barcode(['req_id' => ''])
        );
        $this->assertSame(
            ['found' => false, 'barcode' => ''],
            lab_resolve_existing_barcode([])
        );
        $this->assertSame(
            ['found' => false, 'barcode' => ''],
            lab_resolve_existing_barcode('')
        );
    }

    public function testLabBillingTypeLabelKey(): void
    {
        $this->assertSame('Clinic Billing', lab_billing_type_label_key('C'));
        $this->assertSame('Patient Billing', lab_billing_type_label_key('P'));
        $this->assertSame('Third Party / Insurance', lab_billing_type_label_key('T'));
        $this->assertSame('Not Specified', lab_billing_type_label_key(''));
        $this->assertSame('Not Specified', lab_billing_type_label_key('X'));
    }

    public function testLabRelationshipDisplayModes(): void
    {
        $this->assertSame(
            ['mode' => 'empty', 'value' => ''],
            lab_relationship_display(['relationship' => '', 'relationship_is_list' => false])
        );
        $this->assertSame(
            ['mode' => 'list', 'value' => 'spouse'],
            lab_relationship_display(['relationship' => 'spouse', 'relationship_is_list' => true])
        );
        $this->assertSame(
            ['mode' => 'client', 'value' => 'Client Billing'],
            lab_relationship_display(['relationship' => 'Client Billing', 'relationship_is_list' => false])
        );
        $this->assertSame(
            ['mode' => 'self', 'value' => 'Self'],
            lab_relationship_display(['relationship' => 'Self', 'relationship_is_list' => false])
        );
        $this->assertSame(
            ['mode' => 'raw', 'value' => 'Guardian'],
            lab_relationship_display(['relationship' => 'Guardian', 'relationship_is_list' => false])
        );
    }

    public function testLabNormalizeInsuranceRows(): void
    {
        $rows = lab_normalize_insurance_rows([
            ['plan' => 'A', 1 => 'x'],
            ['plan' => 'B'],
        ]);
        $this->assertSame('A', $rows[0]['plan']);
        $this->assertArrayHasKey('1', $rows[0]);
        $this->assertSame('B', $rows[1]['plan']);
        $this->assertSame([], lab_normalize_insurance_rows(null));
        $this->assertSame([], lab_normalize_insurance_rows([]));
    }

    public function testLabCollectAoeAnswersSkipsIncompleteRowsAndAggregates(): void
    {
        $orders = [
            ['procedure_code' => 'CBC', 'procedure_order_seq' => '1'],
            ['procedure_code' => '', 'procedure_order_seq' => '2'],
            ['procedure_code' => 'CMP', 'procedure_order_seq' => ''],
            ['procedure_code' => 'CMP', 'procedure_order_seq' => '3'],
        ];

        $calls = [];
        $fetcher = static function (int|string $oid, int|string $lab, int|string $code, int|string $seq) use (&$calls): array {
            $codeText = (string) $code;
            $seqText = (string) $seq;
            $calls[] = [$oid, $lab, $codeText, $seqText];
            return [
                ['question_text' => 'Q for ' . $codeText, 'answer' => 'A' . $seqText],
            ];
        };

        $result = lab_collect_aoe_answers($orders, 55, 9, $fetcher);

        $this->assertCount(2, $calls);
        $this->assertSame([55, 9, 'CBC', '1'], $calls[0]);
        $this->assertSame([55, 9, 'CMP', '3'], $calls[1]);
        $this->assertSame(
            [
                ['question_text' => 'Q for CBC', 'answer' => 'A1'],
                ['question_text' => 'Q for CMP', 'answer' => 'A3'],
            ],
            $result
        );
        $this->assertSame([], lab_collect_aoe_answers([], 1, 1, $fetcher));
    }


    public function testGetFacilityInfoRejectsMalformedIds(): void
    {
        $this->assertFalse(getFacilityInfo('onlyone'));
        $this->assertFalse(getFacilityInfo('facility_12_extra'));
        $this->assertFalse(getFacilityInfo('facility_not-an-id'));
        $this->assertFalse(getFacilityInfo('facility_0'));
        $this->assertFalse(getFacilityInfo('facility_-1'));
    }

    public function testLabAsStringFloatAndZeroEdgeCases(): void
    {
        $this->assertSame('0', lab_as_string(0));
        $this->assertSame('0', lab_as_string(0.0));
        $this->assertSame('', lab_as_string(new \stdClass()));
    }

    public function testLabNormalizeArrayRowStringifiesIntegerKeys(): void
    {
        $normalized = lab_normalize_array_row([0 => 'a', 2 => 'b', 'name' => 'x']);
        $this->assertSame(['0' => 'a', '2' => 'b', 'name' => 'x'], $normalized);
    }

}
