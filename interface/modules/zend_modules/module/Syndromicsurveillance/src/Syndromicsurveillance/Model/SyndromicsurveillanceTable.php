<?php

/**
 * interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Syndromicsurveillance\Model;

use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Application\Model\ApplicationTable;

class SyndromicsurveillanceTable extends AbstractTableGateway
{
    /*
    * Fetch the reportable ICD9 codes
    *
    * @return   codes       array       list of replrtable ICD9 codes
    */
    function non_reported_codes()
    {
        $query      = "select id, concat('ICD9:',code) as name from codes where reportable = 1 ORDER BY name";
        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query);

        $codes      = array();
        foreach ($result as $row) {
            $codes[] = $row;
        }

        return $codes;
    }

    /*
    * Get list of providers in EMR
    *
    * @return   rows    Array   List of providers
    */
    function getProviderList()
    {
        global $encounter;
        global $pid;
        $appTable   = new ApplicationTable();

        $sqlSelctProvider       = "SELECT * FROM form_encounter WHERE encounter = ? AND pid = ?";
        $resultSelctProvider    = $appTable->zQuery($sqlSelctProvider, array($encounter, $pid));
        foreach ($resultSelctProvider as $resultSelctProvider_row) {
            $provider = $resultSelctProvider_row['provider_id'];
        }

        $query = "SELECT id, fname, lname, specialty FROM users 
			WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) 
			AND authorized = 1 
			ORDER BY lname, fname";

        $result = $appTable->zQuery($query, array());
        $rows[0] = array (
            'value' => '',
            'label' => 'Unassigned',
            'selected' => true,
            'disabled' => false
        );
        $i = 1;
        foreach ($result as $row) {
            if ($row['id'] == $provider) {
                $select =  true;
            } else {
                $select = false;
            }

            $rows[$i] = array (
                'value' => $row['id'],
                'label' => $row['fname'] . " " . $row['lname'],
                'selected' => $select,
            );
            $i++;
        }

        return $rows;
    }

    /*
    * Fetch the list of patients having the reportable ICD9
    *
    * @param    fromDate        date        encounter date
    * @param    toDate          date        encounter date
    * @param    code_selected   string      selected ICD9 codes from the filter
    * @param    provider_selected       integer     provider id from the filter
    * @param    start           integer     pagination start
    * @param    end             integer     pagination end
    * @param    get_count       integer     flag to identify whether to return the selected rows or the number of rows
    *
    * @return   records         array       return the list of patients having the reportable ICD9 codes
    * @return   count           integer     return the count of patients having the reportable ICD9 codes
    */
    function fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end, $get_count = null)
    {
        $records = array();
        $query_string = array();

        $query = "SELECT   c.code_text,l.pid AS patientid,p.language,l.diagnosis,CONCAT(p.fname, ' ', p.mname, ' ', p.lname) AS patientname,l.date AS issuedate, l.id AS issueid,l.title AS issuetitle 
			FROM
			  lists l, patient_data p, codes c, form_encounter AS fe 
			WHERE c.reportable = 1 ";

        if ($provider_selected) {
            $query .= " AND provider_id = ? ";
            $query_string[] = $provider_selected;
        }

        $query .= " AND l.id NOT IN 
			(SELECT 
				lists_id 
			FROM
				syndromic_surveillance) 
			AND l.date >= ? AND l.date <= ? AND l.pid = p.pid ";
        $query_string[] = $fromDate;
        $query_string[] = $toDate;

        if ($code_selected) {
            $query .= add_escape_custom(" AND c.id IN (" . implode(',', $code_selected) . ") ");
        }

        $query .= " AND l.diagnosis LIKE 'ICD9:%' 
					AND ( SUBSTRING(l.diagnosis, 6) = c.code || SUBSTRING(l.diagnosis, 6) = CONCAT_WS('', c.code, ';') ) 
					AND fe.pid = l.pid 
				UNION DISTINCT 
				SELECT c.code_text, b.pid AS patientid, p.language, b.code, CONCAT(p.fname, ' ', p.mname, ' ', p.lname) AS patientname, b.date AS issuedate,  b.id AS issueid, '' AS issuetitle 
				FROM
					billing b, patient_data p, codes c, form_encounter fe 
				WHERE c.reportable = 1 
					AND b.code_type = 'ICD9' AND b.activity = '1' AND b.pid = p.pid AND fe.encounter = b.encounter ";

        if ($code_selected) {
            $query .= add_escape_custom(" AND c.id IN (" . implode(',', $code_selected) . ") ");
        }

        $query .= " AND c.code = b.code 
			AND fe.date IN 
			(SELECT 
				MAX(fenc.date) 
			FROM
				form_encounter AS fenc 
			WHERE fenc.pid = fe.pid) ";

        if ($provider_selected) {
            $query .= " AND provider_id = ? ";
            $query_string[] = $provider_selected;
        }

        $query      .= " AND fe.date >= ? AND fe.date <= ?";
        $query_string[] = $fromDate;
        $query_string[] = $toDate;

        if ($get_count) {
            $appTable   = new ApplicationTable();
            $result     = $appTable->zQuery($query, $query_string);
            foreach ($result as $row) {
                $records[] = $row;
            }

            return count($records);
        }

        $query      .= " LIMIT " . \Application\Plugin\CommonPlugin::escapeLimit($start) . "," . \Application\Plugin\CommonPlugin::escapeLimit($end);

        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query, $query_string);
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
    * generate the HL7
    *
    * @param    fromDate        date        encounter date
    * @param    toDate          date        encounter date
    * @param    code_selected   string      selected ICD9 codes from the filter
    * @param    provider_selected       integer     provider id from the filter
    * @param    start           integer     pagination start
    * @param    end             integer     pagination end
    *
    * @return   download the generated HL7
    */
    function generate_hl7($fromDate, $toDate, $code_selected, $provider_selected, $start, $end)
    {
        $records = array();
        $query_string = array();

        $query = "SELECT   c.code_text,l.pid AS patientid,p.language,l.diagnosis,
			DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, concat(p.street, '^',p.postal_code,'^', p.city, '^', p.state) as address, 
			p.country_code, p.phone_home, p.phone_biz, p.status, p.sex, p.ethnoracial,p.county, c.code_text, c.code, c.code_type, DATE_FORMAT(l.date,'%Y%m%d') as issuedate, 
			concat(p.fname, '^',p.mname,'^', p.lname) as patientname, l.id AS issueid,l.title AS issuetitle,fac.name,fac.facility_npi,p.race,p.ethnicity,p.postal_code,fe.encounter
			FROM
			  lists l, patient_data p, codes c, form_encounter AS fe
        LEFT JOIN facility AS fac ON fac.id = fe.facility_id
			WHERE c.reportable = 1 ";

        if ($provider_selected) {
            $query .= " AND provider_id = ? ";
            $query_string[] = $provider_selected;
        }

        $query .= " AND l.id NOT IN 
			(SELECT 
			  lists_id 
			FROM
			  syndromic_surveillance) 
			AND l.date >= ? AND l.date <= ? AND l.pid = p.pid ";
        $query_string[] = $fromDate;
        $query_string[] = $toDate;

        if ($code_selected) {
            $query .= " AND c.id IN (?) ";
            $query_string[] = implode(',', $code_selected);
        }

        $query .= " AND l.diagnosis LIKE 'ICD9:%' 
				AND ( SUBSTRING(l.diagnosis, 6) = c.code || SUBSTRING(l.diagnosis, 6) = CONCAT_WS('', c.code, ';') ) 
				AND fe.pid = l.pid 
			  UNION DISTINCT 
			  SELECT c.code_text, b.pid AS patientid, p.language, b.code, DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, 
				  concat(p.street, '^',p.postal_code,'^', p.city, '^', p.state) as address, p.country_code, p.phone_home, p.phone_biz, p.status, 
				  p.sex, p.ethnoracial,p.county, c.code_text, c.code, c.code_type, DATE_FORMAT(fe.date,'%Y%m%d') as issuedate, concat(p.fname, '^',p.mname,'^', p.lname) as patientname,
				  b.id AS issueid, '' AS issuetitle ,fac.name,fac.facility_npi,p.race,p.ethnicity,p.postal_code,fe.encounter
			  FROM
				billing b, patient_data p, codes c, form_encounter fe
        LEFT JOIN facility AS fac ON fac.id = fe.facility_id
			  WHERE c.reportable = 1 
				AND b.code_type = 'ICD9' AND b.activity = '1' AND b.pid = p.pid AND fe.encounter = b.encounter ";

        if ($code_selected) {
            $query .= " AND c.id IN (?) ";
            $query_string[] = implode(',', $code_selected);
        }

        $query .= " AND c.code = b.code 
		  AND fe.date IN 
		  (SELECT 
			MAX(fenc.date) 
		  FROM
			form_encounter AS fenc 
		  WHERE fenc.pid = fe.pid) ";

        if ($provider_selected) {
            $query .= " AND provider_id = ? ";
            $query_string[] = $provider_selected;
        }

        $query      .= " AND fe.date >= ? AND fe.date <= ?";
        $query_string[] = $fromDate;
        $query_string[] = $toDate;

        $content = '';

        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query, $query_string);

        $D = "\r";
        $nowdate    = date('YmdHis');
        $now          = date('YmdGi');
        $now1       = date('Y-m-d G:i');
        $filename   = "syn_sur_" . $now . ".hl7";

        foreach ($result as $r) {
            $fac_name = $race_code = $ethnicity_code = $county_code = '';
            $o_query        = "SELECT * FROM `form_observation` WHERE `encounter` =  ? AND `pid` = ? AND `activity` = ?" ;
            $o_result       = $appTable->zQuery($o_query, array($r['encounter'],$r['patientid'],1));
            $fac_name       = preg_replace('/\s+/', '', $r['name']);
            $race_code      = $this->getCodes($r['race'], 'race');
            $ethnicity_code = $this->getCodes($r['ethnicity'], 'ethnicity');
            $county_code    = $this->getCodes($r['county'], 'county');
            $content .= "MSH|^~\&|OPENEMR|" . $fac_name . "^" . $r['facility_npi'] . "^NPI|||" . $nowdate . "||" .
            "ADT^A04^ADT_A01|NIST-SS-TC-XXX.XX|P^T|2.5.1|||||||||PH_SS-NoAck^SS Sender^2.16.840.1.114222.4.10.3^ISO$D";
            $content .= "EVN|" . // [[ 3.69 ]]
            "|" . // 1.B Event Type Code
            "$nowdate|" . // 2.R Recorded Date/Time
            "|" . // 3. Date/Time Planned Event
            "|" . // 4. Event Reason Cod
            "|" . // 5. Operator ID
            "|" . // 6. Event Occurred
            $fac_name . "^" . $r['facility_npi'] . "^NPI" . // 7. Event Facility
            "$D" ;
            if ($r['sex'] === 'Male') {
                $r['sex'] = 'M';
            }

            if ($r['sex'] === 'Female') {
                $r['sex'] = 'F';
            }

            if ($r['status'] === 'married') {
                $r['status'] = 'M';
            }

            if ($r['status'] === 'single') {
                $r['status'] = 'S';
            }

            if ($r['status'] === 'divorced') {
                $r['status'] = 'D';
            }

            if ($r['status'] === 'widowed') {
                $r['status'] = 'W';
            }

            if ($r['status'] === 'separated') {
                $r['status'] = 'A';
            }

            if ($r['status'] === 'domestic partner') {
                $r['status'] = 'P';
            }

            $content .= "PID|" . // [[ 3.72 ]]
            "1|" . // 1. Set id
            "|" . // 2. (B)Patient id
            $r['patientid'] . "^^^^MR|" . // 3. (R) Patient indentifier list
            "|" . // 4. (B) Alternate PID
            "^^^^^^~^^^^^^S|" . // 5.R. Name
            "|" . // 6. Mather Maiden Name
            $r['DOB'] . "|" . // 7. Date, time of birth
            $r['sex'] . "|" . // 8. Sex
            "|" . // 9.B Patient Alias
            $race_code . "^^CDCREC|" . // 10. Race
            "^^^^" . $r['postal_code'] . "^^^^" . $county_code . "|" . // 11. Address
            "|" . // 12. county code
            $r['phone_home'] . "|" . // 13. Phone Home
            $r['phone_biz'] . "|" . // 14. Phone Bussines
            "|" . // 15. Primary language
            $r['status'] . "|" . // 16. Marital status
            "|" . // 17. Religion
            "|" . // 18. patient Account Number
            "|" . // 19.B SSN Number
            "|" . // 20.B Driver license number
            "|" . // 21. Mathers Identifier
            $ethnicity_code . "^^CDCREC" . // 22. Ethnic Group
            //"|" . // 23. Birth Plase
            //"|" . // 24. Multiple birth indicator
            //"|" . // 25. Birth order
            //"|" . // 26. Citizenship
            //"|" . // 27. Veteran military status
            //"|" . // 28.B Nationality
            //"|" . // 29. Patient Death Date and Time
            //"|" . // 30. Patient Death Indicator
            //"|" . // 31. Identity Unknown Indicator
            //"|" . // 32. Identity Reliability Code
            //"|" . // 33. Last Update Date/Time
            //"|" . // 34. Last Update Facility
            //"|" . // 35. Species Code
            //"|" . // 36. Breed Code
            //"|" . // 37. Breed Code
            //"|" . // 38. Production Class Code
            //""  . // 39. Tribal Citizenship
            "$D" ;
            $content .= "PV1|" . // [[ 3.86 ]]
            "1|" . // 1. Set ID
            "|" . // 2.R Patient Class (U - unknown)
            "|"  . // 3.
            "|"  . // 4.
            "|"  . // 5.
            "|"  . // 6.
            "|"  . // 7.
            "|"  . // 8.
            "|"  . // 9.
            "|"  . // 10.
            "|"  . // 11.
            "|"  . // 12.
            "|"  . // 13.
            "|"  . // 14.
            "|"  . // 15.
            "|"  . // 16.
            "|"  . // 17.
            "|"  . // 18.
            $r['encounter'] . "^^^^VN|"  . // 19.
            "|"  . // 20.
            "|"  . // 21.
            "|"  . // 22.
            "|"  . // 23.
            "|"  . // 24.
            "|"  . // 25.
            "|"  . // 26.
            "|"  . // 27.
            "|"  . // 28.
            "|"  . // 29.
            "|"  . // 30.
            "|"  . // 31.
            "|"  . // 32.
            "|"  . // 33.
            "|"  . // 34.
            "|"  . // 35.
            "|"  . // 36. Discharge Disposition
            "|"  . // 37.
            "|"  . // 38.
            "|"  . // 39.
            "|"  . // 40.
            "|"  . // 41.
            "|"  . // 42.
            "|"  . // 43.
            $nowdate . // 44. Admit Date/Time
            "$D" ;
            $i = 0;
            foreach ($o_result as $row) {
                    $i++;
                if ($row['code'] == 'SS003') {
                    if ($row['ob_value'] == '261QE0002X') {
                        $text = 'Emergency Care';
                    } elseif ($row['ob_value'] == '261QM2500X') {
                        $text = 'Medical Specialty';
                    } elseif ($row['ob_value'] == '261QP2300X') {
                        $text = 'Primary Care';
                    } elseif ($row['ob_value'] == '261QU0200X') {
                        $text = 'Urgent Care';
                    }

                    $content .= "OBX|" .
                    $i . "|" .   //1. Set ID
                    "CWE|" .    //2. Value Type
                    $row['code'] . "^^" . $row['table_code'] . "|" .    //3. Observation Identifier
                    "|" .    //4.
                    $row['ob_value'] . "^" . $text . "^NUCC|" .    //5. Observation Value
                    "|" .    //6. Units
                    "|" .    //7.
                    "|" .    //8.
                    "|" .    //9.
                    "|" .    //10.
                    "F" .     //11. Observation Result Status
                    "$D";
                } elseif ($row['code'] == '21612-7') {
                    $content .= "OBX|" .
                    $i . "|" .   //1. Set ID
                    "NM|" .    //2. Value Type
                    $row['code'] . "^^" . $row['table_code'] . "|" .    //3. Observation Identifier
                    "|" .    //4.
                    $row['ob_value'] . "|" .    //5. Observation Value
                    $row['ob_unit'] . "^^UCUM|" .    //6. Units
                    "|" .    //7.
                    "|" .    //8.
                    "|" .    //9.
                    "|" .    //10.
                    "F" .     //11. Observation Result Status
                    "$D";
                } elseif ($row['code'] == '8661-1') {
                    $content .= "OBX|" .
                    $i . "|" .   //1. Set ID
                    "CWE|" .    //2. Value Type
                    $row['code'] . "^^" . $row['table_code'] . "|" .    //3. Observation Identifier
                    "|" .    //4.
                    "^^^^^^^^" . $row['ob_value'] . "|" .    //5. Observation Value
                    "|" .    //6. Units
                    "|" .    //7.
                    "|" .    //8.
                    "|" .    //9.
                    "|" .    //10.
                    "F" .     //11. Observation Result Status
                    "$D";
                }
            }

            $content .= "DG1|" . // [[ 6.24 ]]
            "1|" . // 1. Set ID
            "|" . // 2.B.R Diagnosis Coding Method
            $r['code'] . "^" . $r['code_text'] . "^I9CDX|" . // 3. Diagnosis Code - DG1
            "|" . // 4.B Diagnosis Description
            $r['issuedate'] . "|" . // 5. Diagnosis Date/Time
            "W" . // 6.R Diagnosis Type  // A - Admiting, W - working
            //"|" . // 7.B Major Diagnostic Category
            //"|" . // 8.B Diagnostic Related Group
            //"|" . // 9.B DRG Approval Indicator
            //"|" . // 10.B DRG Grouper Review Code
            //"|" . // 11.B Outlier Type
            //"|" . // 12.B Outlier Days
            //"|" . // 13.B Outlier Cost
            //"|" . // 14.B Grouper Version And Type
            //"|" . // 15. Diagnosis Priority
            //"|" . // 16. Diagnosing Clinician
            //"|" . // 17. Diagnosis Classification
            //"|" . // 18. Confidential Indicator
            //"|" . // 19. Attestation Date/Time
            //"|" . // 20.C Diagnosis Identifier
            //"" . // 21.C Diagnosis Action Code
            "$D" ;

            //mark if issues generated/sent
            $query_insert = "insert into syndromic_surveillance(lists_id,submission_date,filename) values (?, ?, ?)";
            $appTable->zQuery($query_insert, array($r['issueid'], $now1, $filename));
        }

        //send the header here
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename=' . $filename);

        // put the content in the file
        echo($content);
        exit;
    }

    /*
    * date format conversion
    */
    public function convert_to_yyyymmdd($date)
    {
        $date   = str_replace('/', '-', $date);
        $arr    = explode('-', $date);
        $formatted_date = $arr[2] . "-" . $arr[0] . "-" . $arr[1];
        return $formatted_date;
    }

    /*
    * Convert date from database format to required format
    *
    * @param    String      $date       Date from database (format: YYYY-MM-DD)
    * @param    String      $format     Required date format
    *
    * @return   String      $formatted_date New formatted date
    */
    public function date_format($date, $format)
    {
        if (!$date) {
            return;
        }

        $format = $format ? $format : 'm/d/y';
        $temp   = explode(' ', $date); //split using space and consider the first portion, incase of date with time
        $date   = $temp[0];
        $date   = str_replace('/', '-', $date);
        $arr    = explode('-', $date);

        if ($format == 'm/d/y') {
            $formatted_date = $arr[1] . "/" . $arr[2] . "/" . $arr[0];
        }

        $formatted_date = $temp[1] ? $formatted_date . " " . $temp[1] : $formatted_date; //append the time, if exists, with the new formatted date
        return $formatted_date;
    }

    /*
    * param     string      Content in HL7 format
    * return    string      Formatted HL7 string
    */
    function tr($a)
    {
        return (str_replace(' ', '^', $a));
    }
    public function getCodes($option_id, $list_id)
    {
        $appTable  = new ApplicationTable();
        if ($option_id) {
            $query   = "SELECT notes 
                    FROM list_options 
                    WHERE list_id=? AND option_id=?";
            $result  = $appTable->zQuery($query, array($list_id,$option_id));
            $res_cur = $result->current();
        }

        return $res_cur['notes'];
    }
}
