<?php

/**
 * PatientRelationship Entity - represents a relationship between two patients.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entity;

class PatientRelationship
{
    private ?int $id = null;
    private ?string $uuid = null;
    private ?\DateTime $createdDate = null;
    private bool $active = true;

    public function __construct(
        private readonly int $patientId,
        private readonly int $relatedPatientId,
        private string $relationshipType,
        private readonly int $createdBy,
        private ?string $notes = null
    ) {
        $this->createdDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getRelatedPatientId(): int
    {
        return $this->relatedPatientId;
    }

    public function getRelationshipType(): string
    {
        return $this->relationshipType;
    }

    public function setRelationshipType(string $relationshipType): self
    {
        $this->relationshipType = $relationshipType;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getCreatedDate(): ?\DateTime
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTime $createdDate): self
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Validate the relationship
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->patientId <= 0) {
            $errors[] = 'Patient ID must be a positive integer';
        }

        if ($this->relatedPatientId <= 0) {
            $errors[] = 'Related patient ID must be a positive integer';
        }

        if ($this->patientId === $this->relatedPatientId) {
            $errors[] = 'Cannot create relationship with self';
        }

        if (empty($this->relationshipType)) {
            $errors[] = 'Relationship type is required';
        }

        if ($this->createdBy <= 0) {
            $errors[] = 'Created by user ID must be a positive integer';
        }

        return $errors;
    }

    /**
     * Convert to array for database operations
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'patient_id' => $this->patientId,
            'related_patient_id' => $this->relatedPatientId,
            'relationship_type' => $this->relationshipType,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_date' => $this->createdDate?->format('Y-m-d H:i:s'),
            'active' => $this->active ? 1 : 0
        ];
    }

    /**
     * Create from array (e.g., from database result)
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $relationship = new self(
            (int)$data['patient_id'],
            (int)$data['related_patient_id'],
            $data['relationship_type'],
            (int)$data['created_by'],
            $data['notes'] ?? null
        );

        if (isset($data['id'])) {
            $relationship->setId((int)$data['id']);
        }

        if (isset($data['uuid'])) {
            $relationship->setUuid($data['uuid']);
        }

        if (isset($data['created_date'])) {
            $relationship->setCreatedDate(new \DateTime($data['created_date']));
        }

        if (isset($data['active'])) {
            $relationship->setActive((bool)$data['active']);
        }

        return $relationship;
    }
}
