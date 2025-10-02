<?php

/*
 * ObservationHeartRateTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification\SinglePatientApi\Observation;

use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait\SinglePatientApiTestTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ObservationHeartRateTest extends TestCase
{
    use SinglePatientApiTestTrait;

    #[Test]
    public function testHeartRateObservationSearch_Patient_Code(): void
    {
        // we are testing for LOINC code 8867-4 (Heart rate) as that was giving us 500 errors in Inferno.
        // at some point we'll cover all the other tests, but for now just verify this one works.
        // note this requires the inferno-files db dataset to be loaded as that is where the data for this patient is.
        $response = self::$testClient->get(
            '/apis/default/fhir/Observation',
            [
            'patient' => self::PATIENT_ID_PRIMARY
            ,'code' => '8867-4'
            ]
        );
        $this->assertEquals(200, $response->getStatusCode(), 'Expected HTTP 200');
        $body = $response->getBody()->getContents();
        $this->assertNotEmpty($body, "Response body is empty");
        $data = json_decode((string) $body, true);
        $this->assertArrayHasKey('resourceType', $data, 'resourceType missing');
        $this->assertEquals('Bundle', $data['resourceType'], 'resourceType not Bundle');
        $this->assertArrayHasKey('entry', $data, 'entry missing');
        $this->assertNotEmpty($data['entry'], 'entry is empty');
        foreach ($data['entry'] as $entry) {
            $this->assertArrayHasKey('resource', $entry, 'resource missing from entry');
            $this->assertEquals('Observation', $entry['resource']['resourceType'], 'resourceType not Observation');
            $this->assertArrayHasKey('code', $entry['resource'], 'code missing from resource');
            $this->assertArrayHasKey('coding', $entry['resource']['code'], 'coding missing from code');
            $this->assertNotEmpty($entry['resource']['code']['coding'], 'coding is empty from code');
            $found = false;
            foreach ($entry['resource']['code']['coding'] as $coding) {
                if (isset($coding['code']) && $coding['code'] === '8867-4') {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected LOINC code 8867-4 not found in coding');
        }
        // need to verify profile is present
        $this->assertArrayHasKey('meta', $data['entry'][0]['resource'], 'meta missing from resource');
        $this->assertArrayHasKey('profile', $data['entry'][0]['resource']['meta'], 'profile missing from meta');
        $this->assertContains(FhirObservationVitalsService::USCDI_PROFILE_HEART_RATE_V3_1_1 . '|' . FhirObservationVitalsService::PROFILE_VERSION_3_1_1, $data['entry'][0]['resource']['meta']['profile'], 'Expected profile not found in resource meta');
        foreach (FhirObservationVitalsService::PROFILE_VERSIONS_V2 as $profileVersion) {
            $this->assertContains(FhirObservationVitalsService::USCDI_PROFILE_HEART_RATE . (!empty($profileVersion) ? "|" . $profileVersion : ""), $data['entry'][0]['resource']['meta']['profile'], 'Expected profile not found in resource meta');
        }
    }
}
