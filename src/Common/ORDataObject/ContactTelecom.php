<?php

/**
 * ContactTelecom Data Object
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ORDataObject;

use OpenEMR\Common\ORDataObject\Contact;
use DateTime;
use OpenEMR\Services\Utils\DateFormatterUtils;

class ContactTelecom extends ORDataObject implements \JsonSerializable
{
    // Status constants
    private const STATUS_ACTIVE = 'A';
    private const STATUS_INACTIVE = 'I';
    private const IS_PRIMARY_YES = "Y";
    private const IS_PRIMARY_NO = "N";

    // Default values
    public const DEFAULT_SYSTEM = "phone";
    public const DEFAULT_USE = "home";
    public const USE_OLD = "old";
    private $contact_id;
    private $rank;
    private $system;
    private $use;
    private $value;
    private $notes;
    private $status;
    private $isPrimary;
    private $createdDate;
    private $periodStart;
    private $periodEnd;
    private $author;
    private $inactivated_reason;
    private $_contact;

    // Constructor sets all ContactTelecom attributes to their default value
    public function __construct(private $id = "")
    {
        parent::__construct("contact_telecom");
        $this->setThrowExceptionOnError(true);
        $this->rank = 1;
        $this->author = $_SESSION['authUser'];
        $this->status = self::STATUS_ACTIVE;
        $this->use = self::DEFAULT_USE;
        $this->system = self::DEFAULT_SYSTEM;
        $this->isPrimary = self::IS_PRIMARY_NO;
        $this->value = "";
        $this->notes = "";
        $this->createdDate = new DateTime();
        $this->periodStart = $this->createdDate;

        if ($this->id != "") {
            $this->populate();
            $this->setIsObjectModified(false);
        }
    }

    protected function get_date_fields()
    {
        return ['created_date', 'period_start', 'period_end'];
    }

    public function populate_array($results)
    {
        if (is_array($results)) {
            foreach ($this->get_date_fields() as $field) {
                if (isset($results[$field])) {
                    $results[$field] = \DateTime::createFromFormat("Y-m-d H:i:s", $results[$field]);
                }
            }
        }
        parent::populate_array($results);
    }

    public function persist()
    {
        if ($this->getContact()->isObjectModified()) {
            $this->getContact()->persist();
            $this->set_contact_id($this->getContact()->get_id());
        }

        return parent::persist();
    }

    public function setContact(Contact $contact)
    {
        $this->_contact = $contact;
        $this->contact_id = $contact->get_id();
        $this->setIsObjectModified(true);
    }

    public function getContact(): Contact
    {
        if (empty($this->_contact)) {
            $this->_contact = $this->loadContact($this->contact_id);
        }
        return $this->_contact;
    }

    public function deactivate()
    {
        $this->periodEnd = new DateTime();
        $this->set_status(self::STATUS_INACTIVE);
        $this->setIsObjectModified(true);
    }

    private function loadContact($id)
    {
        $contact = new Contact($id);
        $contact->setThrowExceptionOnError(true);
        return $contact;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function set_id($id): ContactTelecom
    {
        $this->id = $id;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_contact_id(): int
    {
        return $this->contact_id;
    }

    public function set_contact_id(int $contact_id): ContactTelecom
    {
        $this->contact_id = $contact_id;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_rank(): int
    {
        return $this->rank;
    }

    public function set_rank(int $rank): ContactTelecom
    {
        $this->rank = $rank;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_system(): string
    {
        return $this->system;
    }

    public function set_system(string $system): ContactTelecom
    {
        $this->system = $system;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_use(): ?string
    {
        return $this->use;
    }

    public function set_use(?string $use): ContactTelecom
    {
        $this->use = $use;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_value(): string
    {
        return $this->value;
    }

    public function set_value(string $value): ContactTelecom
    {
        $this->value = $value;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_notes(): ?string
    {
        return $this->notes;
    }

    public function set_notes(string $notes): ContactTelecom
    {
        $this->notes = $notes;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_status()
    {
        return $this->status;
    }

    public function set_status($status): ContactTelecom
    {
        $this->status = $status == self::STATUS_ACTIVE ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_is_primary(): string
    {
        return $this->isPrimary;
    }

    public function set_is_primary($isPrimary): ContactTelecom
    {
        $this->isPrimary = $isPrimary == self::IS_PRIMARY_YES ? self::IS_PRIMARY_YES : self::IS_PRIMARY_NO;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_created_date(): Datetime
    {
        return $this->createdDate;
    }

    public function set_created_date(Datetime $createdDate): ContactTelecom
    {
        $this->createdDate = $createdDate;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_period_start(): Datetime
    {
        return $this->periodStart;
    }

    public function set_period_start(Datetime $periodStart): ContactTelecom
    {
        $this->periodStart = $periodStart;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_period_end(): ?Datetime
    {
        return $this->periodEnd;
    }

    public function set_period_end(?Datetime $periodEnd): ContactTelecom
    {
        $this->periodEnd = $periodEnd;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_author(): string
    {
        return $this->author;
    }

    public function set_author(string $author): ContactTelecom
    {
        $this->author = $author;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function get_inactivated_reason(): ?string
    {
        return $this->inactivated_reason;
    }

    public function set_inactivated_reason(string $inactivated_reason): ContactTelecom
    {
        $this->inactivated_reason = $inactivated_reason;
        $this->setIsObjectModified(true);
        return $this;
    }

    public function toArray()
    {
        return $this->jsonSerialize();
    }

    // Specify data which should be serialized to JSON
    public function jsonSerialize(): mixed
    {
        $result = [
            "id" => $this->get_id(),
            'contact_id' => $this->get_contact_id(),
            'rank' => $this->get_rank(),
            'system' => $this->get_system(),
            'use' => $this->get_use(),
            'value' => $this->get_value(),
            'notes' => $this->get_notes(),
            'status' => $this->get_status(),
            'is_primary' => $this->get_is_primary(),
            'author' => $this->get_author(),
            'inactivated_reason' => $this->get_inactivated_reason(),
            'period_end' => null,
            'period_start' => null,
            'created_date' => null
        ];

        if (!empty($this->get_period_end())) {
            $result['period_end'] = DateFormatterUtils::oeFormatShortDate($this->get_period_end()->format("Y-m-d"));
        }
        if (!empty($this->get_period_start())) {
            $result['period_start'] = DateFormatterUtils::oeFormatShortDate($this->get_period_start()->format("Y-m-d"));
        }
        if (!empty($this->get_created_date())) {
            $result['created_date'] = DateFormatterUtils::oeFormatShortDate($this->get_created_date()->format("Y-m-d"));
        }

        return $result;
    }
}
