<?php

/**
 * Isolated FhirServiceRequestSerializer Test
 *
 * Tests for FhirServiceRequestSerializer::deserialize() without requiring a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

require_once __DIR__ . '/../ProcedureServiceBootstrap.php';

use OpenEMR\Services\FHIR\Serialization\FhirServiceRequestSerializer;
use PHPUnit\Framework\TestCase;

class FhirServiceRequestSerializerTest extends TestCase
{
    public function testDeserializeMinimalResource(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $this->assertSame('active', (string) $result->getStatus());
        $this->assertSame('order', (string) $result->getIntent());
    }

    public function testDeserializeWithCategory(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'category' => [
                ['coding' => [['system' => 'http://snomed.info/sct', 'code' => '108252007', 'display' => 'Laboratory procedure']]],
            ],
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $categories = $result->getCategory();
        $this->assertCount(1, $categories);
        $codings = $categories[0]->getCoding();
        $this->assertSame('108252007', (string) $codings[0]->getCode());
    }

    public function testDeserializeWithNote(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'note' => [
                ['text' => 'Patient reports nausea'],
                ['text' => 'Follow up in 2 weeks'],
            ],
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $notes = $result->getNote();
        $this->assertCount(2, $notes);
        $this->assertSame('Patient reports nausea', (string) $notes[0]->getText());
        $this->assertSame('Follow up in 2 weeks', (string) $notes[1]->getText());
    }

    public function testDeserializeWithPerformer(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'performer' => [
                ['reference' => 'Organization/test-uuid-1'],
            ],
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $performers = $result->getPerformer();
        $this->assertCount(1, $performers);
        $this->assertSame('Organization/test-uuid-1', (string) $performers[0]->getReference());
    }

    public function testDeserializeWithReasonCode(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'reasonCode' => [
                ['coding' => [['system' => 'http://hl7.org/fhir/sid/icd-10-cm', 'code' => 'E11.9']]],
            ],
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $reasonCodes = $result->getReasonCode();
        $this->assertCount(1, $reasonCodes);
        $codings = $reasonCodes[0]->getCoding();
        $this->assertSame('E11.9', (string) $codings[0]->getCode());
    }

    public function testDeserializeWithAllFields(): void
    {
        $json = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'category' => [
                ['coding' => [['system' => 'http://snomed.info/sct', 'code' => '108252007']]],
            ],
            'code' => [
                'coding' => [['system' => 'http://loinc.org', 'code' => '24356-8', 'display' => 'Urinalysis complete']],
                'text' => 'Urinalysis complete',
            ],
            'subject' => ['reference' => 'Patient/test-patient-uuid'],
            'encounter' => ['reference' => 'Encounter/test-encounter-uuid'],
            'requester' => ['reference' => 'Practitioner/test-practitioner-uuid'],
            'authoredOn' => '2026-01-15T00:00:00+00:00',
            'priority' => 'routine',
            'patientInstruction' => 'Collect morning sample',
            'note' => [['text' => 'UTI symptoms reported']],
            'performer' => [['reference' => 'Organization/test-lab-uuid']],
            'reasonCode' => [['coding' => [['system' => 'http://hl7.org/fhir/sid/icd-10-cm', 'code' => 'N39.0']]]],
        ];

        $result = FhirServiceRequestSerializer::deserialize($json);

        $this->assertSame('active', (string) $result->getStatus());
        $this->assertSame('order', (string) $result->getIntent());
        $this->assertSame('Patient/test-patient-uuid', (string) $result->getSubject()->getReference());
        $this->assertSame('Encounter/test-encounter-uuid', (string) $result->getEncounter()->getReference());
        $this->assertSame('Practitioner/test-practitioner-uuid', (string) $result->getRequester()->getReference());
        $this->assertSame('2026-01-15T00:00:00+00:00', (string) $result->getAuthoredOn());
        $this->assertSame('routine', (string) $result->getPriority());
        $this->assertSame('Collect morning sample', (string) $result->getPatientInstruction());
        $this->assertCount(1, $result->getCategory());
        $this->assertCount(1, $result->getNote());
        $this->assertCount(1, $result->getPerformer());
        $this->assertCount(1, $result->getReasonCode());

        $codeCodings = $result->getCode()->getCoding();
        $this->assertSame('24356-8', (string) $codeCodings[0]->getCode());
        $this->assertSame('Urinalysis complete', (string) $result->getCode()->getText());
    }
}
