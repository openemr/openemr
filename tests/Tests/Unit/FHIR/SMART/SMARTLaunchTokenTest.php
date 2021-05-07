<?php

/**
 * SMARTLaunchTokenTest Unit tests the SmartLaunchTokenTest for serialization & deserialization of the token.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\TestCase;

class SMARTLaunchTokenTest extends TestCase
{
    public function testConstructor()
    {
        $patientUUID = "555-555-5555";
        $encounterID = "777-777-7777";
        $token = new SMARTLaunchToken($patientUUID, $encounterID);

        $this->assertEquals($patientUUID, $token->getPatient(), "Patient id should have been set in constructor");
        $this->assertEquals($encounterID, $token->getEncounter(), "Encounter id should have been set in constructor");
        $this->assertEquals(null, $token->getIntent(), "Other parameters should not be initialized in constructor");

        $token = new SMARTLaunchToken();
        $this->assertEquals(null, $token->getPatient(), "Patient id on empty constructor should be null");
        $this->assertEquals(null, $token->getEncounter(), "Encounter id on empty constructor should be null");

        $token = new SMARTLaunchToken($patientUUID);
        $this->assertEquals($patientUUID, $token->getPatient(), "Patient id should be set");
        $this->assertEquals(null, $token->getEncounter(), "Encounter id on empty initialization should be null");

        $token = new SMARTLaunchToken(null, $encounterID);
        $this->assertEquals(null, $token->getPatient(), "Patient id on empty initialization should be set");
        $this->assertEquals($encounterID, $token->getEncounter(), "Encounter id should be set");
    }
    /**
     * Checks to make sure the hasScope method is working properly
     */
    public function testDeserializeToken()
    {
        $patientUUID = "555-555-5555";
        $encounterID = "777-777-7777";
        $intent = SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG;
        $token = new SMARTLaunchToken($patientUUID, $encounterID);
        $token->setIntent($intent);
        $serialized = $token->serialize();

        $this->assertNotEmpty($serialized, "Token serialization should be a valid value");
        $this->assertTrue(is_string($serialized), "Token serialization should be set to a string");

        $deserializedToken = SMARTLaunchToken::deserializeToken($serialized);
        $this->assertInstanceOf(SMARTLaunchToken::class, $deserializedToken, "deserializedToken should return a valid token object");
        $this->assertEquals($patientUUID, $deserializedToken->getPatient(), "Patient UUID should be set from deserialization");
        $this->assertEquals($encounterID, $deserializedToken->getEncounter(), "Encounter UUID should be set from deserialization");
        $this->assertEquals($intent, $deserializedToken->getIntent(), "SMART Intent context should be set from deserialization");
    }
}
