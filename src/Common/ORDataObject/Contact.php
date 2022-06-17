<?php

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
        $this->_id = $id;

        if (!empty($id)) {
            $this->populate();
        }
    }

    public function setPatientPid($pid)
    {
        // we set our type to be patient_id and our table type here.
        $this->foreign_table_name = 'patient_data';
        $this->foreign_id = $pid;
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
