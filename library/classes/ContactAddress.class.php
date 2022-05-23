<?php

use \Address;
use \Contact;
use OpenEMR\Common\ORDataObject\ORDataObject;

class ContactAddress extends ORDataObject
{

    /**
     * `id` BIGINT(20) NOT NULL auto_increment,
    `contact_id` BIGINT(20) NOT NULL,
    `address_id` BIGINT(20) NOT NULL,
    `priority` INT(11) NULL,
    `type` VARCHAR(255) NULL COMMENT '[Values: Home, Work, Physical, Mailing, Shipping]',
    `notes` TEXT(255) NULL,
    `status` BINARY(1) NULL COMMENT '[Values: Active, Inactive, etc]',
    `is_primary` BINARY(1) NULL,
    `created_date` DATETIME NULL,
    `period_start` DATETIME NULL COMMENT 'Date the address became active',
    `period_end` DATETIME NULL COMMENT 'Date the address became deactivated',
    `inactivated_reason` VARCHAR(45) NULL DEFAULT NULL COMMENT '[Values: Moved, Mail Returned, etc]',
    PRIMARY KEY (`contact_id`),
    KEY (`contact_id`),
    KEY (`address_id`)
     */

    private $id;
    /**
     * @var int The foreign key to the contact table
     */
    private $contact_id;

    /**
     * @var int The foreign key id to the address table
     */
    private $address_id;

    /**
     * @var int The priority of this address for the given contact
     */
    private $priority;

    /**
     * @var string The type of address this is
     */
    private $type;

    /**
     * @var Note information about the address
     */
    private $notes;

    /**
     * @var Active|Inactive Whether the address is active or not
     */
    private $status;

    /**
     * @var string Whether this is the primary / default address for the contact
     */
    private $isPrimary;

    /**
     * @var Datetime The date this address was created at
     */
    private $createdDate;

    /**
     * @var Datetime The start date for this address
     */
    private $periodStart;

    /**
     * @var Datetime The end date for this address
     */
    private $periodEnd;

    /**
     * @var string The username of the user that created this address
     */
    private $author;

    /**
     * @var string The explanation for why the address was inactivated.
     */
    private $inactivated_reason;

    /**
     * @var Contact The object pointer to the contact object.  Only kept in memory
     */
    private $_contact;

    /**
     * @var Address The object pointer to the address object.  Only kept in memory.
     */
    private $_address;

    /**
     * Constructor sets all Address attributes to their default value
     */
    public function __construct($id = "", $foreign_table_name = "", $foreign_id = "")
    {
        $this->id = $id;
        $this->type_table_id = $foreign_id;
        $this->type_table_name = $foreign_table_name;

        $this->_table = "contact";

        if ($id != "") {
            $this->populate();
        } else {
            // set our default values
            $this->createdDate = $this->periodStart = new DateTime();
            $this->author = $_SESSION['authUser'];
            $this->set_status("Active");
        }
    }

    public function setAddress(Address $address) {
        $this->_address = $address;
        $this->address_id = $address->get_id();
    }

    public function setContact(Contact $contact) {
        $this->_contact = $contact;
        $this->contact_id = $contact->get_id();
    }

    public function getContact() : Contact {
        if (!empty($this->_contact)) {
            $this->_contact = $this->loadContact($this->contact_id);
        }
        return $this->_contact;
    }

    public function getAddress() : Address {
        if (!empty($this->_address)) {
            $this->_address = $this->loadAddress($this->address_id);
        }
        return $this->_address;
    }

    public function deactivate() {
        $this->periodEnd = new DateTime();
        $this->set_status("Inactive");
    }

    private function loadAddress($id) {
        return new \Address($id);
    }

    private function loadContact($id) {
        return new \Contact($id);
    }

    /**
     * @return mixed
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ContactAddress
     */
    public function set_id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function get_contact_id(): int
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     * @return ContactAddress
     */
    public function set_contact_id(int $contact_id): ContactAddress
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    /**
     * @return int
     */
    public function get_address_id(): int
    {
        return $this->address_id;
    }

    /**
     * @param int $address_id
     * @return ContactAddress
     */
    public function set_address_id(int $address_id): ContactAddress
    {
        $this->address_id = $address_id;
        return $this;
    }

    /**
     * @return int
     */
    public function get_priority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return ContactAddress
     */
    public function set_priority(int $priority): ContactAddress
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return string
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ContactAddress
     */
    public function set_type(string $type): ContactAddress
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Note
     */
    public function get_notes(): Note
    {
        return $this->notes;
    }

    /**
     * @param Note $notes
     * @return ContactAddress
     */
    public function set_notes(Note $notes): ContactAddress
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return Active|Inactive
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * @param Active|Inactive $status
     * @return ContactAddress
     */
    public function set_status($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return boolean
     */
    public function get_is_primary(): boolean
    {
        return $this->isPrimary;
    }

    /**
     * @param boolean $isPrimary
     * @return ContactAddress
     */
    public function set_is_primary(boolean $isPrimary): ContactAddress
    {
        $this->isPrimary = $isPrimary;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function get_created_date(): Datetime
    {
        return $this->createdDate;
    }

    /**
     * @param Datetime $createdDate
     * @return ContactAddress
     */
    public function set_created_date(Datetime $createdDate): ContactAddress
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function get_period_start(): Datetime
    {
        return $this->periodStart;
    }

    /**
     * @param Datetime $periodStart
     * @return ContactAddress
     */
    public function set_period_start(Datetime $periodStart): ContactAddress
    {
        $this->periodStart = $periodStart;
        return $this;
    }

    /**
     * @return Datetime
     */
    public function get_period_end(): Datetime
    {
        return $this->periodEnd;
    }

    /**
     * @param Datetime $periodEnd
     * @return ContactAddress
     */
    public function set_period_end(Datetime $periodEnd): ContactAddress
    {
        $this->periodEnd = $periodEnd;
        return $this;
    }

    /**
     * @return string
     */
    public function get_author(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return ContactAddress
     */
    public function set_author(string $author): ContactAddress
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string
     */
    public function get_inactivated_reason(): string
    {
        return $this->inactivated_reason;
    }

    /**
     * @param string $inactivated_reason
     * @return ContactAddress
     */
    public function set_inactivated_reason(string $inactivated_reason): ContactAddress
    {
        $this->inactivated_reason = $inactivated_reason;
        return $this;
    }
}