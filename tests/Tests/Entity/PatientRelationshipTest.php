<?php

namespace OpenEMR\Tests\Entity;

use OpenEMR\Entity\PatientRelationship;
use PHPUnit\Framework\TestCase;

/**
 * Patient Relationship Entity Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PatientRelationshipTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $patientId = 1;
        $relatedPatientId = 2;
        $relationshipType = 'lives_with';
        $createdBy = 3;
        $notes = 'Test notes';

        $relationship = new PatientRelationship(
            $patientId,
            $relatedPatientId,
            $relationshipType,
            $createdBy,
            $notes
        );

        $this->assertEquals($patientId, $relationship->getPatientId());
        $this->assertEquals($relatedPatientId, $relationship->getRelatedPatientId());
        $this->assertEquals($relationshipType, $relationship->getRelationshipType());
        $this->assertEquals($createdBy, $relationship->getCreatedBy());
        $this->assertEquals($notes, $relationship->getNotes());
        $this->assertTrue($relationship->isActive());
        $this->assertInstanceOf(\DateTime::class, $relationship->getCreatedDate());
        $this->assertNull($relationship->getId());
        $this->assertNull($relationship->getUuid());
    }

    public function testValidateReturnsNoErrorsForValidData(): void
    {
        $relationship = new PatientRelationship(1, 2, 'lives_with', 3);
        $errors = $relationship->validate();

        $this->assertEmpty($errors);
    }

    public function testValidateReturnsErrorsForInvalidPatientId(): void
    {
        $relationship = new PatientRelationship(0, 2, 'lives_with', 3);
        $errors = $relationship->validate();

        $this->assertContains('Patient ID must be a positive integer', $errors);
    }

    public function testValidateReturnsErrorsForInvalidRelatedPatientId(): void
    {
        $relationship = new PatientRelationship(1, -1, 'lives_with', 3);
        $errors = $relationship->validate();

        $this->assertContains('Related patient ID must be a positive integer', $errors);
    }

    public function testValidateReturnsErrorsForSamePatientIds(): void
    {
        $relationship = new PatientRelationship(1, 1, 'lives_with', 3);
        $errors = $relationship->validate();

        $this->assertContains('Cannot create relationship with self', $errors);
    }

    public function testValidateReturnsErrorsForEmptyRelationshipType(): void
    {
        $relationship = new PatientRelationship(1, 2, '', 3);
        $errors = $relationship->validate();

        $this->assertContains('Relationship type is required', $errors);
    }

    public function testValidateReturnsErrorsForInvalidCreatedBy(): void
    {
        $relationship = new PatientRelationship(1, 2, 'lives_with', 0);
        $errors = $relationship->validate();

        $this->assertContains('Created by user ID must be a positive integer', $errors);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $relationship = new PatientRelationship(1, 2, 'lives_with', 3, 'Test notes');
        $relationship->setId(10);
        $relationship->setUuid('test-uuid');

        $array = $relationship->toArray();

        $this->assertEquals(10, $array['id']);
        $this->assertEquals('test-uuid', $array['uuid']);
        $this->assertEquals(1, $array['patient_id']);
        $this->assertEquals(2, $array['related_patient_id']);
        $this->assertEquals('lives_with', $array['relationship_type']);
        $this->assertEquals('Test notes', $array['notes']);
        $this->assertEquals(3, $array['created_by']);
        $this->assertEquals(1, $array['active']);
        $this->assertNotNull($array['created_date']);
    }

    public function testFromArrayCreatesCorrectObject(): void
    {
        $data = [
            'id' => 10,
            'uuid' => 'test-uuid',
            'patient_id' => 1,
            'related_patient_id' => 2,
            'relationship_type' => 'lives_with',
            'notes' => 'Test notes',
            'created_by' => 3,
            'created_date' => '2024-01-01 10:00:00',
            'active' => 1
        ];

        $relationship = PatientRelationship::fromArray($data);

        $this->assertEquals(10, $relationship->getId());
        $this->assertEquals('test-uuid', $relationship->getUuid());
        $this->assertEquals(1, $relationship->getPatientId());
        $this->assertEquals(2, $relationship->getRelatedPatientId());
        $this->assertEquals('lives_with', $relationship->getRelationshipType());
        $this->assertEquals('Test notes', $relationship->getNotes());
        $this->assertEquals(3, $relationship->getCreatedBy());
        $this->assertTrue($relationship->isActive());
        $this->assertInstanceOf(\DateTime::class, $relationship->getCreatedDate());
    }

    public function testSettersWork(): void
    {
        $relationship = new PatientRelationship(1, 2, 'lives_with', 3);

        $relationship->setId(99);
        $relationship->setUuid('new-uuid');
        $relationship->setRelationshipType('family_member');
        $relationship->setNotes('Updated notes');
        $relationship->setActive(false);

        $newDate = new \DateTime('2024-12-31 23:59:59');
        $relationship->setCreatedDate($newDate);

        $this->assertEquals(99, $relationship->getId());
        $this->assertEquals('new-uuid', $relationship->getUuid());
        $this->assertEquals('family_member', $relationship->getRelationshipType());
        $this->assertEquals('Updated notes', $relationship->getNotes());
        $this->assertFalse($relationship->isActive());
        $this->assertEquals($newDate, $relationship->getCreatedDate());
    }
}
