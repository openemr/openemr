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

class ContactRelation extends ORDataObject implements \JsonSerializable
{
    // Status constants
    private const ACTIVE_YES = 1;
    private const ACTIVE_NO = 0;
    private const IS_EMERGENCY_YES = 1;
    private const IS_EMERGENCY_NO = 0;
    private const IS_PRIMARY_YES = 1;
    private const IS_PRIMARY_NO = 0;
    
    // List option IDs for validation
    public const LIST_RELATIONSHIP_TYPES = 'related_person-relationship';
    public const LIST_ROLE_TYPES = 'related_person-role';
    
    // Field length constants
    private const MAX_RELATIONSHIP_LENGTH = 63;
    private const MAX_ROLE_LENGTH = 63;
    private const MAX_TABLE_NAME_LENGTH = 63;
    
    // Default values
    public const DEFAULT_PRIORITY = 1;
    
    // Properties - MATCHING DATABASE SCHEMA
    private $id;
    private $contact_id;
    private $related_foreign_table_name;
    private $related_foreign_table_id;
    private $relationship;
    private $role;
    private $contact_priority;
    private $is_primary_contact;
    private $is_emergency_contact;
    private $can_make_medical_decisions;
    private $can_receive_medical_info;
    private $active;
    private $start_date;
    private $end_date;
    private $notes;
    private $metadata;
    private $created_at;
    private $updated_at;
    private $created_by;
    private $updated_by;
    
    // Object references
    private $_contact;
    
    /**
     * Constructor - initializes with default values
     *
     * @param string|int $id Optional relation ID to load
     */
    public function __construct($id = "")
    {
        parent::__construct("contact_relation");
        $this->setThrowExceptionOnError(true);
        
        // Set defaults
        $this->id = $id;
        $this->is_emergency_contact = self::IS_EMERGENCY_NO;
        $this->is_primary_contact = self::IS_PRIMARY_NO;
        $this->contact_priority = self::DEFAULT_PRIORITY;
        $this->can_make_medical_decisions = 0;
        $this->can_receive_medical_info = 0;
        $this->active = self::ACTIVE_YES;
        $this->relationship = null;
        $this->role = null;
        $this->notes = null;
        $this->metadata = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->created_at = new DateTime();
        $this->created_by = !empty($_SESSION['authUser']) ? (int)$_SESSION['authUser'] : null;
        $this->updated_at = new DateTime();
        $this->updated_by = !empty($_SESSION['authUser']) ? (int)$_SESSION['authUser'] : null;
        
        if (!empty($id)) {
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
    
    public function get_related_foreign_table_name(): ?string
    {
        return $this->related_foreign_table_name;
    }
    
    public function get_related_foreign_table_id(): ?int
    {
        return $this->related_foreign_table_id;
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
    
    public function get_metadata(): ?string
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
    }
    
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
    
    public function set_related_foreign_table_name(string $table_name): self
    {
        if (strlen($table_name) > self::MAX_TABLE_NAME_LENGTH) {
            throw new \InvalidArgumentException(
                "Table name cannot exceed " . self::MAX_TABLE_NAME_LENGTH . " characters"
            );
        }
        $this->related_foreign_table_name = $table_name;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_related_foreign_table_id(int $id): self
    {
        $this->related_foreign_table_id = $id;
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
    
    public function set_is_emergency_contact($value): self
    {
        $this->is_emergency_contact = $value ? self::IS_EMERGENCY_YES : self::IS_EMERGENCY_NO;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_is_primary_contact($value): self
    {
        $this->is_primary_contact = $value ? self::IS_PRIMARY_YES : self::IS_PRIMARY_NO;
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
    
    public function set_can_make_medical_decisions($value): self
    {
        $this->can_make_medical_decisions = $value ? 1 : 0;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_can_receive_medical_info($value): self
    {
        $this->can_receive_medical_info = $value ? 1 : 0;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_active($value): self
    {
        $this->active = $value ? self::ACTIVE_YES : self::ACTIVE_NO;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_start_date($date): self
    {
        if ($date instanceof DateTime) {
            $this->start_date = $date;
        } elseif (is_string($date) && !empty($date)) {
            $this->start_date = DateFormatterUtils::dateStringToDateTime($date);
        } else {
            $this->start_date = null;
        }
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_end_date($date): self
    {
        if ($date instanceof DateTime) {
            $this->end_date = $date;
        } elseif (is_string($date) && !empty($date)) {
            $this->end_date = DateFormatterUtils::dateStringToDateTime($date);
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
    
    public function set_metadata(?string $metadata): self
    {
        $this->metadata = $metadata;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_created_at($datetime): self
    {
        if ($datetime instanceof DateTime) {
            $this->created_at = $datetime;
        } elseif (is_string($datetime) && !empty($datetime)) {
            $this->created_at = new DateTime($datetime);
        }
        $this->setIsObjectModified(true);
        return $this;
    }
    
    public function set_updated_at($datetime): self
    {
        if ($datetime instanceof DateTime) {
            $this->updated_at = $datetime;
        } elseif (is_string($datetime) && !empty($datetime)) {
            $this->updated_at = new DateTime($datetime);
        }
        $this->setIsObjectModified(true);
        return $this;
    }
    
    /**
     * Set created_by with proper type casting
     * CRITICAL: Must cast to int to match return type of get_created_by()
     */
    public function set_created_by($userId): self
    {
        // Cast to int if not null, otherwise keep as null
        $this->created_by = ($userId !== null && $userId !== '') ? (int)$userId : null;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    /**
     * Set updated_by with proper type casting
     * CRITICAL: Must cast to int to match return type of get_updated_by()
     */
    public function set_updated_by($userId): self
    {
        // Cast to int if not null, otherwise keep as null
        $this->updated_by = ($userId !== null && $userId !== '') ? (int)$userId : null;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    // ==================== Helper Methods ====================
    
    /**
     * Get the related Contact object
     *
     * @return Contact|null
     */
    public function getContact(): ?Contact
    {
        if (!$this->_contact && $this->contact_id) {
            $this->_contact = new Contact($this->contact_id);
        }
        return $this->_contact;
    }
    
    /**
     * Set the related Contact object
     *
     * @param Contact $contact
     * @return self
     */
    public function setContact(Contact $contact): self
    {
        $this->_contact = $contact;
        $this->contact_id = $contact->get_id();
        $this->setIsObjectModified(true);
        return $this;
    }
    
    /**
     * Check if this is an emergency contact
     *
     * @return bool
     */
    public function isEmergencyContact(): bool
    {
        return $this->is_emergency_contact === self::IS_EMERGENCY_YES;
    }
    
    /**
     * Check if this is the primary contact
     *
     * @return bool
     */
    public function isPrimaryContact(): bool
    {
        return $this->is_primary_contact === self::IS_PRIMARY_YES;
    }
    
    /**
     * Check if relationship is currently active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->active !== self::ACTIVE_YES) {
            return false;
        }
        
        $now = new DateTime();
        
        // Check start date
        if ($this->start_date instanceof DateTime && $this->start_date > $now) {
            return false;
        }
        
        // Check end date
        if ($this->end_date instanceof DateTime && $this->end_date < $now) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Deactivate this relationship
     *
     * @param string|null $endDate Optional end date (defaults to now)
     * @return self
     */
    public function deactivate(?string $endDate = null): self
    {
        $this->active = self::ACTIVE_NO;
        $this->end_date = $endDate ? DateFormatterUtils::dateStringToDateTime($endDate) : new DateTime();
        $this->setIsObjectModified(true);
        return $this;
    }
    
    /**
     * Reactivate this relationship
     *
     * @return self
     */
    public function reactivate(): self
    {
        $this->active = self::ACTIVE_YES;
        $this->end_date = null;
        $this->setIsObjectModified(true);
        return $this;
    }
    
    /**
     * Convert to array representation
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->get_id(),
            'contact_id' => $this->get_contact_id(),
            'related_foreign_table_name' => $this->get_related_foreign_table_name(),
            'related_foreign_table_id' => $this->get_related_foreign_table_id(),
            'relationship' => $this->get_relationship(),
            'role' => $this->get_role(),
            'contact_priority' => $this->get_contact_priority(),
            'is_primary_contact' => $this->get_is_primary_contact(),
            'is_emergency_contact' => $this->get_is_emergency_contact(),
            'can_make_medical_decisions' => $this->get_can_make_medical_decisions(),
            'can_receive_medical_info' => $this->get_can_receive_medical_info(),
            'active' => $this->get_active(),
            'start_date' => $this->get_start_date(),
            'end_date' => $this->get_end_date(),
            'notes' => $this->get_notes(),
            'metadata' => $this->get_metadata(),
            'created_at' => $this->get_created_at(),
            'updated_at' => $this->get_updated_at(),
            'created_by' => $this->get_created_by(),
            'updated_by' => $this->get_updated_by()
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
        
        if (empty($this->related_foreign_table_name)) {
            $errors[] = "Related foreign table name is required";
        }
        
        if (empty($this->related_foreign_table_id)) {
            $errors[] = "Related foreign table ID is required";
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
            $this->related_foreign_table_name,
            $this->related_foreign_table_id,
            $this->relationship
        );
    }
}
