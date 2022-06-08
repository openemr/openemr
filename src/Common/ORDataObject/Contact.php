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
    private $type_table_name;

    /**
     * @var int
     */
    private $type_table_id;


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
        $this->type_table_name = 'patient_data';
        $this->type_table_id = $pid;
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
    public function get_type_table_name(): ?string
    {
        return $this->type_table_name;
    }

    /**
     * @param string $type_table_name
     * @return Contact
     */
    public function set_type_table_name(string $type_table_name): Contact
    {
        $this->type_table_name = $type_table_name;
        return $this;
    }

    /**
     * @return int
     */
    public function get_type_table_id(): ?int
    {
        return $this->type_table_id;
    }

    /**
     * @param int $type_table_id
     * @return Contact
     */
    public function set_type_table_id(int $type_table_id): Contact
    {
        $this->type_table_id = $type_table_id;
        return $this;
    }
}
