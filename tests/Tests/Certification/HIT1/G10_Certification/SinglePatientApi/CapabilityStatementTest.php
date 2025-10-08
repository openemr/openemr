<?php

/*
 * CapabilityStatementTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification\SinglePatientApi;

use OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait\G10ApiTestTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CapabilityStatementTest extends TestCase
{
    use G10ApiTestTrait;

    const V3_HISTORICAL_PROFILES = [
        'http://hl7.org/fhir/StructureDefinition/bp'
        ,'http://hl7.org/fhir/StructureDefinition/bodyheight'
        ,'http://hl7.org/fhir/StructureDefinition/bodyweight'
        ,'http://hl7.org/fhir/StructureDefinition/heartrate'
        ,'http://hl7.org/fhir/StructureDefinition/resprate'
        ,'http://hl7.org/fhir/StructureDefinition/bodytemp'
        ,'http://hl7.org/fhir/R4/observation-vitalsigns'
    ];
    const V3_CORE_PROFILES = [
        // 3.1.1 profiles
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-allergyintolerance',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careplan',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-careteam',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-implantable-device',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-diagnosticreport-lab',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-diagnosticreport-note',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-documentreference',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-encounter',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-goal',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-immunization',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-location',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medication',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationrequest',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-lab',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-smokingstatus',
        'http://hl7.org/fhir/us/core/StructureDefinition/pediatric-bmi-for-age',
        'http://hl7.org/fhir/us/core/StructureDefinition/pediatric-weight-for-height',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-pulse-oximetry',
        'http://hl7.org/fhir/us/core/StructureDefinition/head-occipital-frontal-circumference-percentile',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-organization',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-patient',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-practitioner',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-practitionerrole',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-procedure',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-provenance',
    ];

    const V7_CORE_PROFILES = [
        // 7.0.0 profiles
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis', // first came in 5.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns', // first came in 5.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-coverage', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-care-experience-preference',
        // we will add in these additional observations when we implement them
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medicationdispense', // first came in 6.0.0
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-clinical-result',
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-occupation', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-pregnancyintent', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-pregnancystatus', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-screening-assessment', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-sexual-orientation', // first came in 5.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-simple-observation', // first came in 6.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-treatment-intervention-preference',
        // the below vitals have corresponding profiles in 3.1.1, but they were redefined w/ new uris in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-vital-signs', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-bmi', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-body-height', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-body-temperature', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-body-weight', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-head-circumference', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-heart-rate', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-respiratory-rate', // first came in 4.0.0
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-questionnaireresponse', // first came in 5.0.0
        // we will add in these additional resources when we implement them
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson', // first came in 5.0.0
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest', // first came in 5.0.0
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-specimen', // first came in 6.0.0
    ];

    const V8_CORE_PROFILES = [
        // 8.0.0 profiles
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-adi-documentreference',
//        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-average-blood-pressure',
        'http://hl7.org/fhir/us/core/StructureDefinition/us-core-observation-adi-documentation',
    ];

    #[Test]
    public function testCapabilityStatement_v311(): void
    {
        // Expected profiles based on the G10 Certification requirements
        // Add more assertions as needed to validate the structure and content of the CapabilityStatement
        $supportedProfiles = $this->getSupportedProfiles();
        $expectedProfiles = array_merge(self::V3_CORE_PROFILES, self::V3_HISTORICAL_PROFILES);
        $this->assertNotEmpty($supportedProfiles, "No supported profiles found in CapabilityStatement");
        $this->assertProfilesSupported($supportedProfiles, $expectedProfiles, "3.1.1");
        // make sure we are backwards compatible with non-versioned profile checks
        $this->assertProfilesSupported($supportedProfiles, $expectedProfiles, "");
    }

    #[Test]
    public function testCapabilityStatement_v700(): void
    {
        // Expected profiles based on the G10 Certification requirements
        // Add more assertions as needed to validate the structure and content of the CapabilityStatement
        $supportedProfiles = $this->getSupportedProfiles();
        $expectedProfiles = array_merge(self::V3_CORE_PROFILES, self::V7_CORE_PROFILES);
        $version = "7.0.0";
        $this->assertNotEmpty($supportedProfiles, "No supported profiles found in CapabilityStatement");
        $this->assertProfilesSupported($supportedProfiles, $expectedProfiles, $version);
    }

    #[Test]
    public function testCapabilityStatement_v800(): void
    {
        $supportedProfiles = $this->getSupportedProfiles();
        $expectedProfiles = array_merge(self::V3_CORE_PROFILES, self::V7_CORE_PROFILES, self::V8_CORE_PROFILES);
        $version = "8.0.0";
        $this->assertNotEmpty($supportedProfiles, "No supported profiles found in CapabilityStatement");
        $this->assertProfilesSupported($supportedProfiles, $expectedProfiles, $version);
    }

    protected function getSupportedProfiles()
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: self::DEFAULT_OPENEMR_BASE_URL_API;
        $url = $baseUrl . '/apis/default/fhir/metadata';
        $response = file_get_contents($url);
        if ($response === false) {
            return [];
        }
        $capabilityStatement = json_decode($response, true);
        if ($capabilityStatement === null) {
            return [];
        }
        $profilesToCheck = [];
        foreach ($capabilityStatement['rest'] as $value) {
            foreach ($value['resource'] as $resource) {
                if (empty($resource['supportedProfile'])) {
                    continue;
                }
                $profiles = $resource['supportedProfile'];
                foreach ($profiles as $profile) {
                    $profile = (string)$profile;
                    $profilesToCheck[$profile] = $profile;
                }
            }
        }
        return $profilesToCheck;
    }

    protected function assertProfilesSupported(array $supportedProfiles, array $expectedProfiles, string $version): void
    {
        $suffix = !empty($version) ? "|" . $version : "";
        foreach ($expectedProfiles as $profile) {
            $this->assertContains($profile . $suffix, $supportedProfiles, "Profile {$profile} is expected in US Core " . $version . " CapabilityStatement");
        }
    }
}
