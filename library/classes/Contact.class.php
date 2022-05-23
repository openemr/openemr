<?php

use OpenEMR\Common\ORDataObject\ORDataObject;

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
        $this->_table = "contact";
        $this->_id = $id;

        if (!empty($id)) {
            $this->populate();
        }
    }

    public function setPatientPid($pid) {
        // we set our type to be patient_id and our table type here.
        $this->type_table_name = 'patient_data';
        $this->type_table_id = $pid;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Contact
     */
    public function setId(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeTableName(): string
    {
        return $this->type_table_name;
    }

    /**
     * @param string $type_table_name
     * @return Contact
     */
    public function setTypeTableName(string $type_table_name): Contact
    {
        $this->type_table_name = $type_table_name;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeTableId(): int
    {
        return $this->type_table_id;
    }

    /**
     * @param int $type_table_id
     * @return Contact
     */
    public function setTypeTableId(int $type_table_id): Contact
    {
        $this->type_table_id = $type_table_id;
        return $this;
    }
}