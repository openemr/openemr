<?php

/************************************************************************
            aptient.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

/**
 * class Patient
 */
use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ORDataObject\ORDataObject\Traits\IdFieldTrait;

/**
 * class Patient
 */
class Patient extends ORDataObject
{
    protected string $_table = 'patient_data';

    var $pubpid;
    var $lname;
    var $mname;
    var $fname;
    var $date_of_birth;
    var $dob;
    var $provider;

    /**
     * Set Prescription attributes to their default value
     *
     * @return void
     */
    protected function init(): void
    {
        $this->pubpid = "";
        $this->lname = "";
        $this->mname = "";
        $this->fname = "";
        $this->dob   = "";
        $this->provider = new Provider();
    }

    /**
     * @return void
     */
    protected function populate(): void
    {
        parent::populate();
        $id = $this->get_id();
        if (empty($id)) {
            return;
        }
        $res = sqlQuery("SELECT providerID , fname , lname , mname, " .
            "DATE_FORMAT(DOB,'%m/%d/%Y') as date_of_birth, " .
            "pubpid " .
            "FROM " . escape_table_name($this->_table) . " " .
            "WHERE pid = ?", [$id]);
        if (is_array($res)) {
            $this->pubpid = $res['pubpid'];
            $this->lname = $res['lname'];
            $this->mname = $res['mname'];
            $this->fname = $res['fname'];
            $this->provider = new Provider($res['providerID']);
            $this->date_of_birth = $res['date_of_birth'];
        }
    }

    function get_pubpid()
    {
        return $this->pubpid;
    }
    function get_lname()
    {
        return $this->lname;
    }
    function get_name_display()
    {
        return $this->fname . " " . $this->lname;
    }
    function get_provider_id()
    {
        return $this->provider->id;
    }
    function get_provider()
    {
        return $this->provider;
    }
    function get_dob()
    {
        return $this->date_of_birth;
    }
} // end of Patient
