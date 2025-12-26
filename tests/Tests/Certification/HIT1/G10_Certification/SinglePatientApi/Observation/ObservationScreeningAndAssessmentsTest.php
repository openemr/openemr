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

use OpenEMR\Services\FHIR\Observation\FhirObservationObservationFormService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait\SinglePatientApiTestTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ObservationScreeningAndAssessmentsTest extends TestCase
{
    use SinglePatientApiTestTrait;

    #[Test]
    public function testObservationSearch_Patient_Category(): void
    {
        // we are testing for searching for observations with category 'survey'
        // at some point we'll cover all the other tests, but for now just verify this one works.
        // note this requires the inferno-files db dataset to be loaded as that is where the data for this patient is.
        $response = self::$testClient->get(
            '/apis/default/fhir/Observation',
            [
            'patient' => self::PATIENT_ID_PRIMARY
            ,'category' => 'survey'
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
            $this->assertArrayHasKey('category', $entry['resource'], 'category missing from resource');
            $this->assertCount(1, $entry['resource']['category'], 'Unexpected number of categories in resource');
            $this->assertArrayHasKey('coding', $entry['resource']['category'][0], 'coding missing from category');
            $this->assertNotEmpty($entry['resource']['category'][0]['coding'], 'coding is empty from code');
            $found = false;
            foreach ($entry['resource']['category'][0]['coding'] as $coding) {
                if (isset($coding['code']) && $coding['code'] === 'survey') {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected category code "survey" not found in coding');
        }
        // need to verify profile is present
        $this->assertArrayHasKey('meta', $data['entry'][0]['resource'], 'meta missing from resource');
        $this->assertArrayHasKey('profile', $data['entry'][0]['resource']['meta'], 'profile missing from meta');
        $this->assertCount(2 * count(FhirObservationVitalsService::PROFILE_VERSIONS_V2), $data['entry'][0]['resource']['meta']['profile'], 'Unexpected number of profiles in resource meta');
        foreach (FhirObservationVitalsService::PROFILE_VERSIONS_V2 as $profileVersion) {
            $this->assertContains(FhirObservationObservationFormService::USCGI_PROFILE_URI . (!empty($profileVersion) ? "|" . $profileVersion : ""), $data['entry'][0]['resource']['meta']['profile'], 'Expected profile not found in resource meta');
            $this->assertContains(FhirObservationObservationFormService::USCGI_SCREENING_ASSESSMENT_URI . (!empty($profileVersion) ? "|" . $profileVersion : ""), $data['entry'][0]['resource']['meta']['profile'], 'Expected profile not found in resource meta');
        }
    }
}
