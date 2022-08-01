<?php

/**
 * class InsuranceNumbers
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class InsuranceNumbers extends ORDataObject
{
        var $id;
        var $provider_id;
        var $insurance_company_name;
        var $insurance_company_id;
        var $provider_number;
        var $rendering_provider_number;
        var $group_number;
        var $provider_number_type;
        var $provider_number_type_array = array ("" => "Unspecified", "0B" => "State License Number", "1A" => "Blue Cross Provider Number","1B" => "Blue Shield Provider Number","1C" => "Medicare Provider Number",
                                                        "1D" => "Medicaid Provider Number","1G" => "Provider UPIN Number","1H" => "Champus Identification Number","1J" => "Facility ID Number",
                                                        "B3" => "Preferred Provider Organization Number","BQ" => "Health Maintenance Organization Code Number","E1" => "Employer's Identification Number",
                                                        "FH" => "Clinic Number", "G2" => "Provider Commercial Number", "G5" => "Provider Site Number", "LU" => "Location Number", "SY" => "Social Security Number",
                                                        "U3" => "Unique Supplier Identification Number (USIN)", "X5" => "State Industrial Accident Provider Number");
        var $rendering_provider_number_type;
        var $rendering_provider_number_type_array = array ("" => "Unspecified", "0B" => "State License Number", "1A" => "Blue Cross Provider Number","1B" => "Blue Shield Provider Number","1C" => "Medicare Provider Number",
                                                        "1D" => "Medicaid Provider Number","1G" => "Provider UPIN Number","1H" => "Champus Identification Number","G2" => "Provider Commercial Number",
                                                        "LU" => "Location Number", "N5" => "Provider Plan Network Identification Number",
                                                        "TJ" => "Federal Taxpayer's Identification Number", "X4" => "Clinical Laboratory Improvement Amendment Number", "X5" => "State Industrial Accident Provider Number");

        /**
         * Constructor sets all Insurance attributes to their default value
         */

        function __construct($id = "", $prefix = "")
        {
            $this->id = $id;
            $this->_table = "insurance_numbers";
            if ($id != "") {
                $this->populate();
            }
        }

        function populate()
        {
            parent::populate();
            $ic = new InsuranceCompany($this->insurance_company_id);
            $this->insurance_company_name = $ic->get_name();
            $ic = null;
        }

        function insurance_numbers_factory($provider_id)
        {
            $ins = array();
            $sql = "SELECT id FROM "  . escape_table_name($this->_table) . " WHERE provider_id = ? ORDER BY insurance_company_id";
            $results = sqlQ($sql, array($provider_id));

            while ($row = sqlFetchArray($results)) {
                    $ins[] = new InsuranceNumbers($row['id']);
            }

            return $ins;
        }

        function get_id()
        {
            return $this->id;
        }

        function set_id($id)
        {
            if (is_numeric($id)) {
                $this->id = $id;
            }
        }

        function get_provider_id()
        {
            return $this->provider_id;
        }

        function set_provider_id($num)
        {
            $this->provider_id = $num;
        }

        function get_insurance_company_id()
        {
            return $this->insurance_company_id;
        }

        function set_insurance_company_id($num)
        {
            $this->insurance_company_id = $num;
        }

        function get_insurance_company_name()
        {
            if (empty($this->insurance_company_name)) {
                return "Default";
            }

            return $this->insurance_company_name;
        }

        function get_provider_number()
        {
            return $this->provider_number;
        }

        function set_provider_number($num)
        {
            $this->provider_number = $num;
        }

        function get_rendering_provider_number()
        {
            return $this->rendering_provider_number;
        }

        function set_rendering_provider_number($num)
        {
            $this->rendering_provider_number = $num;
        }

        function get_group_number()
        {
            return $this->group_number;
        }

        function set_group_number($num)
        {
            $this->group_number = $num;
        }

        function get_provider_number_type()
        {
            return $this->provider_number_type;
        }

        function set_provider_number_type($string)
        {
            $this->provider_number_type = $string;
        }

        function get_rendering_provider_number_type()
        {
            return $this->rendering_provider_number_type;
        }

        function set_rendering_provider_number_type($string)
        {
            $this->rendering_provider_number_type = $string;
        }
}
