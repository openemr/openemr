<?php

/************************************************************************
                        prescription.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

/**
 * class Provider
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class Provider extends ORDataObject
{
        var $id;
        var $lname;
        var $fname;
        var $federal_drug_id;
        var $insurance_numbers;
        var $specialty;
        var $npi;
        var $state_license_number;

        /**
         * Constructor sets all Prescription attributes to their default value
         */
    function __construct($id = "", $prefix = "")
    {
        $this->id = $id;
        $this->federal_drug_id = "";
        $this->_table = "users";
        $this-> npi = "";
        $this->insurance_numbers = array();
        $this->state_license_number = "";
        if ($id != "") {
            $this->populate();
        }
    }

    function populate()
    {
        $res = sqlQuery("SELECT fname,lname,federaldrugid, specialty, npi, state_license_number FROM users where id ='" . add_escape_custom($this->id) . "'");

        if (is_array($res)) {
            $this->lname = $res['lname'];
            $this->fname = $res['fname'];
            $this->federal_drug_id = $res['federaldrugid'];
            $this->specialty = $res['specialty'];
            $this->npi = $res['npi'];
            $this->state_license_number = $res['state_license_number'];
        }

        $ins = new InsuranceNumbers();
        $this->insurance_numbers = $ins->insurance_numbers_factory($this->id);
    }

    function utility_provider_array()
    {
        $provider_array = array();
        $res = sqlQ("Select id,fname,lname  from users where authorized = 1");
        while ($row = sqlFetchArray($res)) {
                    $provider_array[$row['id']] = $row['fname'] . " " . $row['lname'];
        }

        return $provider_array;
    }

    function providers_factory($sort = "ORDER BY lname,fname")
    {
        $psa = array();
        $sql = "SELECT id FROM "  . $this->_table . " where authorized = 1 " . $sort;
        $results = sqlQ($sql);

        while ($row = sqlFetchArray($results)) {
                    $psa[] = new Provider($row['id']);
        }

        return $psa;
    }

    function get_id()
    {
        return $this->id;
    }

    function get_name_display()
    {
        return $this->fname . " " . $this->lname;
    }

    function get_specialty()
    {
        return $this->specialty;
    }

    function get_provider_number_default()
    {
        if (!empty($this->insurance_numbers)) {
            return $this->insurance_numbers[0]->get_provider_number();
        }
    }

    function get_rendering_provider_number_default()
    {
        if (!empty($this->insurance_numbers)) {
            return $this->insurance_numbers[0]->get_rendering_provider_number();
        }
    }

    function get_insurance_numbers()
    {
        return $this->insurance_numbers;
    }

    function get_insurance_numbers_default()
    {
        return ($this->insurance_numbers[0] ?? null);
    }

    function get_group_number_default()
    {
        if (!empty($this->insurance_numbers)) {
            return $this->insurance_numbers[0]->get_group_number();
        }
    }

    function get_npi()
    {
        return $this->npi;
    }

    function get_state_license_number()
    {
        return $this->state_license_number;
    }
} // end of Provider
