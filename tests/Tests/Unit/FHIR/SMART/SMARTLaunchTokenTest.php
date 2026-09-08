<?php

/**
 * SMARTLaunchTokenTest Unit tests the SmartLaunchTokenTest for serialization & deserialization of the token.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\TestCase;

class SMARTLaunchTokenTest extends TestCase
{
    public function testConstructor(): void
    {
        $patientUUID = "555-555-5555";
        $encounterID = "777-777-7777";
        $token = new SMARTLaunchToken($patientUUID, $encounterID);

        $this->assertEquals($patientUUID, $token->getPatient(), "Patient id should have been set in constructor");
        $this->assertEquals($encounterID, $token->getEncounter(), "Encounter id should have been set in constructor");
        $this->assertEquals(null, $token->getIntent(), "Other parameters should not be initialized in constructor");
        $this->assertEquals([], $token->getFhirContext(), "FHIR context should not be initialized in constructor");
        $this->assertEquals(null, $token->getAppContext(), "App context should not be initialized in constructor");

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
    public function testDeserializeToken(): void
    {
        $patientUUID = "555-555-5555";
        $encounterID = "777-777-7777";
        $intent = SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG;
        $token = new SMARTLaunchToken($patientUUID, $encounterID);
        $token->setIntent($intent);
        $token->setAppointmentUuid('999-999-9999');
        $token->addFhirContextReference('Questionnaire', 'questionnaire-uuid');
        $token->addFhirContextReference('QuestionnaireResponse', 'questionnaire-response-uuid');
        $token->setAppContext('{"workflow":"questionnaire-assessment"}');
        $serialized = $token->serialize();

        $this->assertNotEmpty($serialized, "Token serialization should be a valid value");
        $this->assertTrue(is_string($serialized), "Token serialization should be set to a string");

        $deserializedToken = SMARTLaunchToken::deserializeToken($serialized);
        $this->assertEquals($patientUUID, $deserializedToken->getPatient(), "Patient UUID should be set from deserialization");
        $this->assertEquals($encounterID, $deserializedToken->getEncounter(), "Encounter UUID should be set from deserialization");
        $this->assertEquals($intent, $deserializedToken->getIntent(), "SMART Intent context should be set from deserialization");
        $this->assertEquals('999-999-9999', $deserializedToken->getAppointmentUuid(), "Existing appointment context should be preserved");
        $this->assertEquals(
            [
                ['reference' => 'Questionnaire/questionnaire-uuid'],
                ['reference' => 'QuestionnaireResponse/questionnaire-response-uuid'],
            ],
            $deserializedToken->getFhirContext(),
            "FHIR context should be set from deserialization"
        );
        $this->assertEquals(
            '{"workflow":"questionnaire-assessment"}',
            $deserializedToken->getAppContext(),
            "SMART appContext should be set from deserialization"
        );
    }

    public function testAddFhirContextItemAcceptsCanonicalForm(): void
    {
        $token = new SMARTLaunchToken();
        $item = [
            'type' => 'Questionnaire',
            'reference' => 'Questionnaire/questionnaire-uuid',
            'canonical' => 'https://example.org/Questionnaire/123|v2023-05-03',
        ];
        $token->addFhirContextItem($item);
        $this->assertSame([$item], $token->getFhirContext(), "Canonical fhirContext item should be stored as given");
    }

    public function testAddFhirContextItemDeduplicates(): void
    {
        $token = new SMARTLaunchToken();
        $token->addFhirContextItem(['reference' => 'Questionnaire/abc']);
        $token->addFhirContextItem(['reference' => 'Questionnaire/abc']);
        $this->assertCount(1, $token->getFhirContext(), "Identical items should not duplicate");
    }

    /**
     * @param array<string, mixed> $item
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidFhirContextItemProvider')]
    public function testAddFhirContextItemRejectsInvalidItems(array $item): void
    {
        $token = new SMARTLaunchToken();
        $this->expectException(\InvalidArgumentException::class);
        $token->addFhirContextItem($item);
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function invalidFhirContextItemProvider(): array
    {
        return [
            'empty item' => [[]],
            'only type' => [['type' => 'Questionnaire']],
            'unsupported key' => [['reference' => 'Questionnaire/abc', 'display' => 'My Form']],
            'malformed reference' => [['reference' => 'not-a-reference']],
            'lowercase resource type' => [['reference' => 'questionnaire/abc']],
            'patient reference without role' => [['reference' => 'Patient/abc']],
            'encounter reference without role' => [['reference' => 'Encounter/abc']],
            'invalid canonical' => [['canonical' => 'not a url']],
            'empty canonical' => [['canonical' => '']],
            'empty identifier' => [['identifier' => []]],
            'non-array identifier' => [['identifier' => 'urn:oid:1.2.3']],
            'empty role' => [['reference' => 'Questionnaire/abc', 'role' => '']],
            'relative role uri' => [['reference' => 'Questionnaire/abc', 'role' => 'launch']],
            'invalid type' => [['reference' => 'Questionnaire/abc', 'type' => 'questionnaire']],
        ];
    }

    public function testAddFhirContextItemAllowsPatientWithNonLaunchRole(): void
    {
        $token = new SMARTLaunchToken();
        $token->addFhirContextItem([
            'reference' => 'Patient/abc',
            'role' => 'https://example.org/role/related-patient',
        ]);
        $this->assertCount(1, $token->getFhirContext(), "Patient with an absolute non-launch role is permitted");
    }
}
