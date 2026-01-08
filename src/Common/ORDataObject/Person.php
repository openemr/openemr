<?php

/**
 * Person Entity - Represents a person in OpenEMR
 * Follows Active Record pattern for database persistence
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ORDataObject;

use DateTime;
use OpenEMR\Common\Uuid\UuidRegistry;

class Person extends ORDataObject implements \JsonSerializable, \Stringable
{
    /**
     * @var null
     */
    private $uuid;
    private $title;
    private $first_name;
    private $middle_name;
    private $last_name;
    private $preferred_name;
    private $gender;
    private $birth_date;
    private $death_date;
    private $marital_status;
    private $race;
    private $ethnicity;
    private $preferred_language;
    private $communication;
    private $ssn;
    private $active;
    private $inactive_reason;
    private $inactive_date;
    private $notes;
    private $created_date;
    private $created_by;
    private $updated_date;
    private $updated_by;

    /**
     * Constructor - initializes with default values
     *
     * @param string|int $id Optional person ID to load
     */
    public function __construct(private $id = "")
    {
        parent::__construct("person");
        $this->setThrowExceptionOnError(true);
        $this->uuid = null;
        $this->title = "";
        $this->first_name = "";
        $this->middle_name = "";
        $this->last_name = "";
        $this->preferred_name = "";
        $this->gender = "";
        $this->birth_date = null;
        $this->death_date = null;
        $this->marital_status = "";
        $this->race = "";
        $this->ethnicity = "";
        $this->preferred_language = "";
        $this->communication = "";
        $this->ssn = "";
        $this->active = 1;
        $this->inactive_reason = null;
        $this->inactive_date = null;
        $this->notes = "";
        $this->created_date = new DateTime();
        $this->created_by = $_SESSION['authUserID'] ?? null;
        $this->updated_date = null;
        $this->updated_by = null;

        // Load from database if ID provided
        if ($this->id != "") {
            $this->populate();
            $this->setIsObjectModified(false);
        }
    }

    /**
     * Get fields that should be treated as dates
     *
     * @return array
     */
    protected function get_date_fields()
    {
        return ['birth_date', 'death_date', 'inactive_date', 'created_date', 'updated_date'];
    }

    /**
     * Populate object from database results array
     *
     * @param array $results
     */
    public function populate_array($results)
    {
        if (is_array($results)) {
            // Convert date fields to DateTime objects
            foreach ($this->get_date_fields() as $field) {
                if (isset($results[$field]) && !empty($results[$field])) {
                    if ($results[$field] instanceof DateTime) {
                        // Already a DateTime
                        continue;
                    }
                    $date = DateTime::createFromFormat("Y-m-d H:i:s", $results[$field]);
                    if ($date === false) {
                        // Try date only format
                        $date = DateTime::createFromFormat("Y-m-d", $results[$field]);
                    }
                    $results[$field] = $date ?: null;
                }
            }
        }
        parent::populate_array($results);
    }

    /**
     * Persist object to database
     *
     * @return bool|int
     */
    public function persist()
    {
        // Generate UUID if creating new record
        if (empty($this->id) && empty($this->uuid)) {
            try {
                // createUuid() returns bytes directly - no uuidToBytes() needed!
                $this->uuid = (new UuidRegistry(['table_name' => 'person']))->createUuid();
            } catch (\Exception $e) {
                // Log but don't fail - UUID is optional
                error_log("Failed to generate UUID for person: " . $e->getMessage());
            }
        }

        // Set updated timestamp and user
        $this->updated_date = new DateTime();
        $this->updated_by = $_SESSION['authUserID'] ?? $this->created_by;

        return parent::persist();
    }

    // ==================== GETTERS AND SETTERS ====================

    public function get_id(): int
    {
        return (int)$this->id;
    }

    public function set_id($id): self
    {
        $this->id = $id;
        $this->setIsObjectModified(true);
        return $this;
    }

    /**
     * Get UUID - returns BINARY for database operations, STRING for API/display
     *
     * CRITICAL FIX: This method must return the raw binary value so that
     * ORDataObject::persist() can save it correctly to the BINARY(16) column.
     * The conversion to string happens in get_uuid_string() or toArray().
     *
     * @return string|null Binary UUID (16 bytes) or null
     */
    public function get_uuid(): ?string
    {
        // Return the raw binary value for database storage
        // This is what ORDataObject::persist() expects
        return $this->uuid;
    }

    /**
     * Get UUID as human-readable string (36 characters with hyphens)
     * Use this for API responses, logging, and display purposes
     *
     * @return string|null UUID string like "550e8400-e29b-41d4-a716-446655440000"
     */
    public function get_uuid_string(): ?string
    {
        return $this->uuid ? UuidRegistry::uuidToString($this->uuid) : null;
    }

    /**
     * Set UUID - accepts either binary (16 bytes) or string (36 chars)
     *
     * @param string|null $uuid Either binary (16 bytes) or string format
     * @return self
     */
    public function set_uuid(?string $uuid): self
    {
        if ($uuid === null) {
            $this->uuid = null;
        } elseif (strlen($uuid) === 16) {
            // Already in binary format
            $this->uuid = $uuid;
        } elseif (strlen($uuid) === 36 && str_contains($uuid, '-')) {
            // String format with hyphens - convert to binary
            $this->uuid = UuidRegistry::uuidToBytes($uuid);
        } else {
            // Invalid format
            error_log("Person::set_uuid() - Invalid UUID format: length=" . strlen($uuid));
            $this->uuid = null;
        }

        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_title(): string
    {
        return $this->title ?? "";
    }

    public function set_title(string $title): self
    {
        $this->title = $title;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_first_name(): string
    {
        return $this->first_name ?? "";
    }

    public function set_first_name(string $first_name): self
    {
        $this->first_name = $first_name;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_middle_name(): string
    {
        return $this->middle_name ?? "";
    }

    public function set_middle_name(string $middle_name): self
    {
        $this->middle_name = $middle_name;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_last_name(): string
    {
        return $this->last_name ?? "";
    }

    public function set_last_name(string $last_name): self
    {
        $this->last_name = $last_name;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_preferred_name(): string
    {
        return $this->preferred_name ?? "";
    }

    public function set_preferred_name(string $preferred_name): self
    {
        $this->preferred_name = $preferred_name;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_gender(): string
    {
        return $this->gender ?? "";
    }

    public function set_gender(string $gender): self
    {
        $this->gender = $gender;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_birth_date(): ?DateTime
    {
        return $this->birth_date;
    }

    public function set_birth_date(?DateTime $birth_date): self
    {
        $this->birth_date = $birth_date;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_death_date(): ?DateTime
    {
        return $this->death_date;
    }

    public function set_death_date(?DateTime $death_date): self
    {
        $this->death_date = $death_date;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_marital_status(): string
    {
        return $this->marital_status ?? "";
    }

    public function set_marital_status(string $marital_status): self
    {
        $this->marital_status = $marital_status;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_race(): string
    {
        return $this->race ?? "";
    }

    public function set_race(string $race): self
    {
        $this->race = $race;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_ethnicity(): string
    {
        return $this->ethnicity ?? "";
    }

    public function set_ethnicity(string $ethnicity): self
    {
        $this->ethnicity = $ethnicity;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_preferred_language(): string
    {
        return $this->preferred_language ?? "";
    }

    public function set_preferred_language(string $preferred_language): self
    {
        $this->preferred_language = $preferred_language;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_communication(): string
    {
        return $this->communication ?? "";
    }

    public function set_communication(string $communication): self
    {
        $this->communication = $communication;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_ssn(): string
    {
        return $this->ssn ?? "";
    }

    public function set_ssn(string $ssn): self
    {
        $this->ssn = $ssn;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_active(): int
    {
        return (int)$this->active;
    }

    public function set_active(int $active): self
    {
        $this->active = $active;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_inactive_reason(): ?string
    {
        return $this->inactive_reason;
    }

    public function set_inactive_reason(?string $inactive_reason): self
    {
        $this->inactive_reason = $inactive_reason;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_inactive_date(): ?DateTime
    {
        return $this->inactive_date;
    }

    public function set_inactive_date(?DateTime $inactive_date): self
    {
        $this->inactive_date = $inactive_date;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_notes(): string
    {
        return $this->notes ?? "";
    }

    public function set_notes(string $notes): self
    {
        $this->notes = $notes;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_created_date(): ?DateTime
    {
        return $this->created_date;
    }

    public function set_created_date(?DateTime $created_date): self
    {
        $this->created_date = $created_date;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_created_by(): ?int
    {
        return $this->created_by;
    }

    public function set_created_by(?int $created_by): self
    {
        $this->created_by = $created_by;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_updated_date(): ?DateTime
    {
        return $this->updated_date;
    }

    public function set_updated_date(?DateTime $updated_date): self
    {
        $this->updated_date = $updated_date;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_updated_by(): ?int
    {
        return $this->updated_by;
    }

    public function set_updated_by(?int $updated_by): self
    {
        $this->updated_by = $updated_by;
        $this->setIsObjectModified(true);
        return $this;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full name (first_name + middle_name + last_name)
     *
     * @return string
     */
    public function get_full_name(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        return implode(" ", $parts);
    }

    /**
     * Get display name (preferred_name or first_name + last_name)
     *
     * @return string
     */
    public function get_display_name(): string
    {
        if (!empty($this->preferred_name)) {
            return $this->preferred_name . " " . $this->last_name;
        }
        return $this->first_name . " " . $this->last_name;
    }

    /**
     * Check if person is active
     *
     * @return bool
     */
    public function is_active(): bool
    {
        return $this->active === 1;
    }

    /**
     * Check if person is deceased
     *
     * @return bool
     */
    public function is_deceased(): bool
    {
        return $this->death_date !== null;
    }

    /**
     * Get age in years (or age at death)
     *
     * @return int|null
     */
    public function get_age(): ?int
    {
        if (empty($this->birth_date)) {
            return null;
        }

        $endDate = $this->death_date ?? new DateTime();
        $age = $this->birth_date->diff($endDate);
        return $age->y;
    }

    /**
     * Deactivate the person record
     *
     * @param string $reason
     * @return bool
     */
    public function deactivate(string $reason = ""): bool
    {
        $this->active = 0;
        $this->inactive_reason = $reason;
        $this->inactive_date = new DateTime();
        return $this->persist();
    }

    /**
     * Reactivate the person record
     *
     * @return bool
     */
    public function reactivate(): bool
    {
        $this->active = 1;
        $this->inactive_reason = null;
        $this->inactive_date = null;
        return $this->persist();
    }

    // ==================== SERIALIZATION ====================

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * JSON serialization
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->get_uuid_string(), // USE STRING VERSION FOR API/DISPLAY
            'title' => $this->title,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'preferred_name' => $this->preferred_name,
            'full_name' => $this->get_full_name(),
            'display_name' => $this->get_display_name(),
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
            'death_date' => $this->death_date ? $this->death_date->format('Y-m-d') : null,
            'age' => $this->get_age(),
            'is_deceased' => $this->is_deceased(),
            'marital_status' => $this->marital_status,
            'race' => $this->race,
            'ethnicity' => $this->ethnicity,
            'preferred_language' => $this->preferred_language,
            'communication' => $this->communication,
            'ssn' => $this->ssn,
            'active' => $this->active,
            'is_active' => $this->is_active(),
            'inactive_reason' => $this->inactive_reason,
            'inactive_date' => $this->inactive_date ? $this->inactive_date->format('Y-m-d H:i:s') : null,
            'notes' => $this->notes,
            'created_date' => $this->created_date ? $this->created_date->format('Y-m-d H:i:s') : null,
            'created_by' => $this->created_by,
            'updated_date' => $this->updated_date ? $this->updated_date->format('Y-m-d H:i:s') : null,
            'updated_by' => $this->updated_by
        ];
    }

    /**
     * String representation
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get_display_name();
    }
}
