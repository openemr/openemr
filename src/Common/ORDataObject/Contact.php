<?php

/**
 * Represents a contact object in OpenEMR and in the database.  Follows the Active Record design pattern for
 * loading and persisting data to the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ORDataObject;

class Contact extends ORDataObject
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $foreign_table_name;

    /**
     * @var int
     */
    private $foreign_id;


    const CONTACT_TYPE_PATIENT = 'Patient';
    const CONTACT_TYPES = [self::CONTACT_TYPE_PATIENT];

    public function __construct($id)
    {
        parent::__construct("contact");
        $this->id = $id;

        if (!empty($id)) {
            $this->populate();
        }
    }

    public function setContactRecord(string $foreign_table_name, int $foreign_id): void
    {
        // we set our type to be patient_id and our table type here.
        $this->foreign_table_name = $foreign_table_name;
        $this->foreign_id = $foreign_id;

        $this->setContactIdIfExist();
    }

    public function persist()
    {
        if (empty($this->id)) {
            $this->setContactIdIfExist();
        }
        return parent::persist();
    }

    private function setContactIdIfExist(): void
    {
        $id = sqlQuery("SELECT `id` FROM `contact` WHERE `foreign_table_name` = ? AND `foreign_id` = ?", [$this->foreign_table_name, $this->foreign_id])['id'] ?? null;
        if (!empty($id)) {
            // the contact entry already exists for this foreign table name and foreign id, so set it
            $this->id = $id;
        }
    }

    /**
     * @return int
     */
    public function get_id(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Contact
     */
    public function set_id(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function get_foreign_table_name(): ?string
    {
        return $this->foreign_table_name;
    }

    /**
     * @param string $foreign_table_name
     * @return Contact
     */
    public function set_foreign_table_name(string $foreign_table_name): Contact
    {
        $this->foreign_table_name = $foreign_table_name;
        return $this;
    }

    /**
     * @return int
     */
    public function get_foreign_id(): ?int
    {
        return $this->foreign_id;
    }

    /**
     * @param int $foreign_id
     * @return Contact
     */
    public function set_foreign_id(int $foreign_id): Contact
    {
        $this->foreign_id = $foreign_id;
        return $this;
    }
}
