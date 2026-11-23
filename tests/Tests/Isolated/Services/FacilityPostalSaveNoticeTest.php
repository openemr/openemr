<?php

/**
 * Facility-screen postal notice.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

if (!defined('OPENEMR_STATIC_ANALYSIS')) {
    define('OPENEMR_STATIC_ANALYSIS', true);
}

use OpenEMR\Services\FacilityService;
use OpenEMR\Validators\FacilityValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FacilityPostalSaveNoticeTest extends TestCase
{
    /**
     * @return array<string, array{string, bool, bool, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noticeProvider(): array
    {
        $billing = 'This billing facility was saved. The postal code is not 9 digits, '
            . 'so a claim billed from this facility can be rejected.';
        $service = 'This service facility was saved. The postal code is not 9 digits, '
            . 'so a claim that uses this service location can be rejected.';
        $both = 'This facility was saved as a billing and service location. '
            . 'The postal code is not 9 digits, so a claim that uses it can be rejected.';

        return [
            'billing five digits' => ['12345', true, false, $billing, 'United States'],
            'service five digits' => ['12345', false, true, $service, 'US'],
            'both roles' => ['12345', true, true, $both, 'USA'],
            'empty postal' => ['', true, false, $billing, 'United States'],
            'zip plus 4 is nine digits' => ['12345-6789', true, true, '', 'US'],
            'nine digits' => ['123456789', false, true, '', 'US'],
            'letters after nine digits' => ['123456789abc', true, false, '', 'US'],
            'digits with a space' => ['12345 6789', true, true, '', 'US'],
            'blank country with a canadian shape stays quiet' => ['K1A 0B1', false, true, '', ''],
            'canada stays quiet' => ['K1A 0B1', false, true, '', 'Canada'],
            'ca stays quiet' => ['K1A0B1', true, false, '', 'CA'],
            'ireland stays quiet' => ['D02 AF30', true, true, '', 'Ireland'],
            'ie stays quiet' => ['D02AF30', true, false, '', 'IE'],
            'blank country stays quiet even with phone code 1' => ['12345', true, false, '', ''],
            'united states still notices' => ['12345', true, false, $billing, 'United States'],
            'usa abbreviation still notices' => ['12345', false, true, $service, 'U.S.A.'],
            'us code still notices' => ['12345', true, true, $both, 'US'],
            'nine digits with a blank country' => ['123456789', true, false, '', ''],
            'not a service or billing location' => ['12345', false, false, '', 'Canada'],
        ];
    }

    /**
     * The save dialog says the row was stored.
     */
    #[DataProvider('noticeProvider')]
    public function testSaveNoticeFollowsTheFacilityRole(
        string $postal,
        bool $billing,
        bool $service,
        string $expected,
        string $country,
    ): void {
        $notice = FacilityService::facilityPostalSaveNotice($postal, $billing, $service, $country);
        $this->assertSame($expected, $notice);
        if ($notice === '') {
            return;
        }
        $this->assertStringContainsString('was saved', $notice);
        $this->assertStringContainsString('can be rejected', $notice);
        $this->assertStringNotContainsString('***', $notice);
        $this->assertStringNotContainsString('CSC', $notice);
        $this->assertStringNotContainsString('MA114', $notice);
        $this->assertStringNotContainsString('277', $notice);
    }

    /**
     * The dialog body names the save and stays JSON when the sentence cannot be encoded.
     */
    public function testSaveDialogBodyNamesTheSave(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, 'Notice'), true);
        $this->assertIsArray($saved);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('Notice', $saved['message']);
        $open = json_decode(FacilityService::facilitySaveDialogBody(false, 'Still open'), true);
        $this->assertIsArray($open);
        $this->assertSame('not_saved', $open['status']);
        $this->assertSame('Still open', $open['message']);
    }

    /**
     * A sentence that is not valid text drops the notice and still reports saved.
     */
    public function testSaveDialogBodyStaysSavedWhenTheSentenceCannotBeEncoded(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, "\xB1\x31"), true);
        $this->assertIsArray($saved);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('', $saved['message']);
    }

    /**
     * The dialog body carries an errors object keyed by field when validation fails.
     */
    public function testSaveDialogBodyIncludesErrorsWhenProvided(): void
    {
        $body = FacilityService::facilitySaveDialogBody(
            false,
            'Not saved',
            ['name' => ['name is required'], 'phone' => ['phone is too long']]
        );
        $this->assertIsString($body);
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('not_saved', $decoded['status']);
        $this->assertSame('Not saved', $decoded['message']);
        $this->assertSame(
            ['name' => ['name is required'], 'phone' => ['phone is too long']],
            $decoded['errors']
        );
    }

    /**
     * Without errors the dialog body stays byte-identical to {status, message}.
     */
    public function testSaveDialogBodyOmitsErrorsWhenEmpty(): void
    {
        $body = FacilityService::facilitySaveDialogBody(true, 'Notice');
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame(['status' => 'saved', 'message' => 'Notice'], $decoded);
        $this->assertArrayNotHasKey('errors', $decoded);

        $body = FacilityService::facilitySaveDialogBody(true, 'Notice', []);
        $decoded = json_decode($body, true);
        $this->assertSame(['status' => 'saved', 'message' => 'Notice'], $decoded);
        $this->assertArrayNotHasKey('errors', $decoded);
    }

    /**
     * Build a FacilityService without its DB-dependent constructor so the
     * isolated suite can exercise validateFormValues() with no database.
     */
    private function newFacilityService(): FacilityService
    {
        $service = (new \ReflectionClass(FacilityService::class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(FacilityService::class, 'facilityValidator');
        $property->setAccessible(true);
        $property->setValue($service, new FacilityValidator());
        return $service;
    }

    public function testValidateFormValuesValidSetPasses(): void
    {
        $values = [
            'name' => 'General Hospital',
            'service_location' => '',
            'billing_location' => '',
            'accepts_assignment' => '',
            'primary_business_entity' => '',
            'inactive' => '',
            'pos_code' => '',
            'domain_identifier' => '05D1234567',
            'facility_npi' => '',
        ];
        $result = $this->newFacilityService()->validateFormValues($values);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getValidationMessages());

        // checkboxes normalized in place to '0'
        $this->assertSame('0', $values['service_location']);
        $this->assertSame('0', $values['billing_location']);
        $this->assertSame('0', $values['accepts_assignment']);
        $this->assertSame('0', $values['primary_business_entity']);
        $this->assertSame('0', $values['inactive']);
        // pos_code normalized to null
        $this->assertNull($values['pos_code']);
    }

    public function testValidateFormValuesEmptyNameFails(): void
    {
        $values = ['name' => ''];
        $result = $this->newFacilityService()->validateFormValues($values);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('name', $result->getValidationMessages());
    }

    public function testValidateFormValuesNameTooLongFails(): void
    {
        $values = ['name' => str_repeat('A', 256)];
        $result = $this->newFacilityService()->validateFormValues($values);

        $this->assertFalse($result->isValid());
    }

    public function testValidateFormValuesPosCodePassesAndNormalizes(): void
    {
        $values = ['name' => 'General Hospital', 'pos_code' => '11'];
        $result = $this->newFacilityService()->validateFormValues($values);
        $this->assertTrue($result->isValid());
        $this->assertSame('11', $values['pos_code']);

        $values2 = ['name' => 'General Hospital', 'pos_code' => ''];
        $result2 = $this->newFacilityService()->validateFormValues($values2);
        $this->assertTrue($result2->isValid());
        $this->assertNull($values2['pos_code']);
    }

    public function testValidateFormValuesDomainIdentifierPasses(): void
    {
        $values = ['name' => 'General Hospital', 'domain_identifier' => '05D1234567'];
        $result = $this->newFacilityService()->validateFormValues($values);
        $this->assertTrue($result->isValid());
    }
}
