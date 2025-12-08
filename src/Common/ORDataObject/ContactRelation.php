<?php

/**
 * ContactRelation Data Object
 *
 * Represents a generic relationship between a contact and any other entity in the system.
 * Enables flexible many-to-many relationships between contacts and various entity types
 * (patients, persons, companies, providers, etc.)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ORDataObject;

use OpenEMR\Common\ORDataObject\Contact;
use DateTime;
use OpenEMR\Services\Utils\DateFormatterUtils;

class ContactRelation extends ORDataObject implements \JsonSerializable, \Stringable
{
    // Status constants
    private const ACTIVE_YES = 1;
    private const ACTIVE_NO = 0;
    private const IS_EMERGENCY_YES = 1;
    private const IS_EMERGENCY_NO = 0;
    private const IS_PRIMARY_YES = 1;
    private const IS_PRIMARY_NO = 0;

    // List option IDs for validation
    public const LIST_RELATIONSHIP_TYPES = 'related_person_relationship';
    public const LIST_ROLE_TYPES = 'related_person_role';

    // Field length constants
    private const MAX_RELATIONSHIP_LENGTH = 63;
    private const MAX_ROLE_LENGTH = 63;
    private const MAX_TABLE_NAME_LENGTH = 63;

    // Default values
    public const DEFAULT_PRIORITY = 1;
    private $contact_id;
    private $target_table;
    private $target_id;
    private $relationship;
    private $role;
    private $contact_priority;  // Changed from emergency_contact_priority
    private $is_primary_contact;  // NEW - added to match schema
    private $is_emergency_contact;
    private $can_make_medical_decisions;  // NEW - added to match schema
    private $can_receive_medical_info;  // NEW - added to match schema
    private $active;
    private $start_date;
    private $end_date;
    private $notes;
    //private $metadata;
    //private $created_at;
    //private $updated_at;
    //private $created_by;
    //private $updated_by;

    // Object references
    private $_contact;

    /**
     * Constructor - initializes with default values
     *
     * @param string|int $id Optional relation ID to load
     */
    public function __construct(private $id = "")
    {
        parent::__construct("contact_relation");
        $this->setThrowExceptionOnError(true);
        $this->is_emergency_contact = self::IS_EMERGENCY_NO;
        $this->is_primary_contact = self::IS_PRIMARY_NO;
        $this->contact_priority = self::DEFAULT_PRIORITY;
        $this->can_make_medical_decisions = 0;
        $this->can_receive_medical_info = 0;
        $this->active = self::ACTIVE_YES;
        $this->relationship = null;
        $this->role = null;
        $this->notes = null;
        //$this->metadata = null;
        $this->start_date = null;
        $this->end_date = null;
        //$this->created_at = new DateTime();
        //$this->created_by = $_SESSION['authUser'] ?? null;
        //$this->updated_at = new DateTime();
        //$this->updated_by = $_SESSION['authUser'] ?? null;

        if (!empty($this->id)) {
            $this->populate();
        }
    }

    // ==================== Getters ====================

    public function get_id()
    {
        return $this->id;
    }

    public function get_contact_id(): ?int
    {
        return $this->contact_id;
    }

    public function get_target_table(): ?string
    {
        return $this->target_table;
    }

    public function get_target_id(): ?int
    {
        return $this->target_id;
    }

    public function get_relationship(): ?string
    {
        return $this->relationship;
    }

    public function get_role(): ?string
    {
        return $this->role;
    }

    public function get_is_emergency_contact(): int
    {
        return $this->is_emergency_contact ?? self::IS_EMERGENCY_NO;
    }

    public function get_is_primary_contact(): int
    {
        return $this->is_primary_contact ?? self::IS_PRIMARY_NO;
    }

    public function get_contact_priority(): int
    {
        return $this->contact_priority ?? self::DEFAULT_PRIORITY;
    }

    public function get_can_make_medical_decisions(): int
    {
        return $this->can_make_medical_decisions ?? 0;
    }

    public function get_can_receive_medical_info(): int
    {
        return $this->can_receive_medical_info ?? 0;
    }

    public function get_active(): int
    {
        return $this->active ?? self::ACTIVE_YES;
    }

    public function get_start_date(): ?string
    {
        if ($this->start_date instanceof DateTime) {
            return $this->start_date->format('Y-m-d');
        }
        return $this->start_date;
    }

    public function get_end_date(): ?string
    {
        if ($this->end_date instanceof DateTime) {
            return $this->end_date->format('Y-m-d');
        }
        return $this->end_date;
    }

    public function get_notes(): ?string
    {
        return $this->notes;
    }

    /*public function get_metadata(): ?string
    {
        return $this->metadata;
    }

    public function get_created_at(): ?string
    {
        if ($this->created_at instanceof DateTime) {
            return $this->created_at->format('Y-m-d H:i:s');
        }
        return $this->created_at;
    }

    public function get_updated_at(): ?string
    {
        if ($this->updated_at instanceof DateTime) {
            return $this->updated_at->format('Y-m-d H:i:s');
        }
        return $this->updated_at;
    }

    public function get_created_by(): ?int
    {
        return $this->created_by;
    }

    public function get_updated_by(): ?int
    {
        return $this->updated_by;
    }*/

    // ==================== Setters ====================

    public function set_id($id): self
    {
        $this->id = $id;
        return $this;
    }

    public function set_contact_id(int $contact_id): self
    {
        $this->contact_id = $contact_id;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_target_table(string $table_name): self
    {
        if (strlen($table_name) > self::MAX_TABLE_NAME_LENGTH) {
            throw new \InvalidArgumentException(
                "Table name cannot exceed " . self::MAX_TABLE_NAME_LENGTH . " characters"
            );
        }
        $this->target_table = $table_name;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_target_id(int $foreign_id): self
    {
        $this->target_id = $foreign_id;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_relationship(?string $relationship): self
    {
        if ($relationship !== null && strlen($relationship) > self::MAX_RELATIONSHIP_LENGTH) {
            throw new \InvalidArgumentException(
                "Relationship cannot exceed " . self::MAX_RELATIONSHIP_LENGTH . " characters"
            );
        }
        $this->relationship = $relationship;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_role(?string $role): self
    {
        if ($role !== null && strlen($role) > self::MAX_ROLE_LENGTH) {
            throw new \InvalidArgumentException(
                "Role cannot exceed " . self::MAX_ROLE_LENGTH . " characters"
            );
        }
        $this->role = $role;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_is_emergency_contact($is_emergency): self
    {
        $this->is_emergency_contact = $is_emergency ? self::IS_EMERGENCY_YES : self::IS_EMERGENCY_NO;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_is_primary_contact($is_primary): self
    {
        $this->is_primary_contact = $is_primary ? self::IS_PRIMARY_YES : self::IS_PRIMARY_NO;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_contact_priority(int $priority): self
    {
        if ($priority < 1 || $priority > 99) {
            throw new \InvalidArgumentException("Priority must be between 1 and 99");
        }
        $this->contact_priority = $priority;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_can_make_medical_decisions($can_decide): self
    {
        $this->can_make_medical_decisions = $can_decide ? 1 : 0;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_can_receive_medical_info($can_receive): self
    {
        $this->can_receive_medical_info = $can_receive ? 1 : 0;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_active(int $active): self
    {
        $this->active = $active ? self::ACTIVE_YES : self::ACTIVE_NO;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_start_date($start_date): self
    {
        if ($start_date !== null) {
            if (is_string($start_date)) {
                $date = DateFormatterUtils::dateStringToDateTime($start_date);
                if ($date === false) {
                    throw new \InvalidArgumentException("Invalid start_date format: " . $start_date);
                }
                $this->start_date = $date;
            } elseif ($start_date instanceof DateTime) {
                $this->start_date = $start_date;
            } else {
                throw new \InvalidArgumentException("Invalid start_date format");
            }
        } else {
            $this->start_date = null;
        }
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_end_date($end_date): self
    {
        if ($end_date !== null) {
            if (is_string($end_date)) {
                $date = DateFormatterUtils::dateStringToDateTime($end_date);
                if ($date === false) {
                    throw new \InvalidArgumentException("Invalid end_date format: " . $end_date);
                }
                $this->end_date = $date;
            } elseif ($end_date instanceof DateTime) {
                $this->end_date = $end_date;
            } else {
                throw new \InvalidArgumentException("Invalid end_date format");
            }
        } else {
            $this->end_date = null;
        }
        $this->setIsObjectModified(true);
        return $this;
    }

    public function set_notes(?string $notes): self
    {
        $this->notes = $notes;
        $this->setIsObjectModified(true);
        return $this;
    }

    /*public function set_metadata(?string $metadata): self
    {
        // Validate JSON if provided
        if ($metadata !== null && !empty($metadata)) {
            $decoded = json_decode($metadata);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException("Invalid JSON in metadata");
            }
        }
        $this->metadata = $metadata;
        $this->setIsObjectModified(true);
        return $this;
    }*/

    // ==================== Object References ====================

    /**
     * Set the Contact object reference
     */
    public function setContact(Contact $contact): void
    {
        $this->_contact = $contact;
        $this->contact_id = $contact->get_id();
        $this->setIsObjectModified(true);
    }

    /**
     * Get the Contact object (lazy load if needed)
     */
    public function getContact(): Contact
    {
        if (empty($this->_contact)) {
            $this->_contact = $this->loadContact($this->contact_id);
        }
        return $this->_contact;
    }

    /**
     * Load Contact object
     */
    private function loadContact($id): Contact
    {
        $contact = new Contact($id);
        $contact->setThrowExceptionOnError(true);
        return $contact;
    }

    // ==================== Business Logic Methods ====================

    /**
     * Check if this is an emergency contact
     */
    public function isEmergencyContact(): bool
    {
        return $this->is_emergency_contact === self::IS_EMERGENCY_YES;
    }

    /**
     * Check if this is a primary contact
     */
    public function isPrimaryContact(): bool
    {
        return $this->is_primary_contact === self::IS_PRIMARY_YES;
    }

    /**
     * Check if can make medical decisions
     */
    public function canMakeMedicalDecisions(): bool
    {
        return $this->can_make_medical_decisions === 1;
    }

    /**
     * Check if can receive medical info
     */
    public function canReceiveMedicalInfo(): bool
    {
        return $this->can_receive_medical_info === 1;
    }

    /**
     * Check if this relationship is currently active
     */
    public function isActive(): bool
    {
        return $this->active === self::ACTIVE_YES;
    }

    /**
     * Deactivate this relationship
     */
    public function deactivate(): void
    {
        $this->end_date = new DateTime();
        $this->set_active(self::ACTIVE_NO);
        $this->setIsObjectModified(true);
    }

    /**
     * Activate this relationship
     */
    public function activate(): void
    {
        $this->end_date = null;
        $this->set_active(self::ACTIVE_YES);
        $this->setIsObjectModified(true);
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->get_id(),
            'contact_id' => $this->get_contact_id(),
            'target_table' => $this->get_target_table(),
            'target_id' => $this->get_target_id(),
            'relationship' => $this->get_relationship(),
            'role' => $this->get_role(),
            'is_emergency_contact' => $this->get_is_emergency_contact(),
            'is_primary_contact' => $this->get_is_primary_contact(),
            'contact_priority' => $this->get_contact_priority(),
            'can_make_medical_decisions' => $this->get_can_make_medical_decisions(),
            'can_receive_medical_info' => $this->get_can_receive_medical_info(),
            'active' => $this->get_active(),
            'start_date' => $this->get_start_date(),
            'end_date' => $this->get_end_date(),
            'notes' => $this->get_notes(),
            //'metadata' => $this->get_metadata(),
            //'created_at' => $this->get_created_at(),
            //'updated_at' => $this->get_updated_at(),
            //'created_by' => $this->get_created_by(),
            //'updated_by' => $this->get_updated_by()
        ];
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validate the relationship data
     *
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->contact_id)) {
            $errors[] = "Contact ID is required";
        }

        if (empty($this->target_table)) {
            $errors[] = "Target table name is required";
        }

        if (empty($this->target_id)) {
            $errors[] = "Target table ID is required";
        }

        if ($this->contact_priority < 1 || $this->contact_priority > 99) {
            $errors[] = "Contact priority must be between 1 and 99";
        }

        return $errors;
    }

    /**
     * String representation for debugging
     */
    public function __toString(): string
    {
        return sprintf(
            "ContactRelation(id=%s, contact=%s, relates_to=%s:%s, relationship=%s)",
            $this->id,
            $this->contact_id,
            $this->target_table,
            $this->target_id,
            $this->relationship
        );
    }
}
