<?php
/**
* Patient.php
*
* File encapsulating a class entity for the representation of a patient
* 
* Copyright (C) 2015 Robert Down <robertdown@live.com>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

namespace OpenEMR\Patient;

use DateTime;

/**
* Class representation of a single patient
*/
class Entity
{

    public $pid;
    public $title;
    public $firstName = null;
    public $middleName = null;
    public $lastName = null;
    public $dateOfBirth = null;

    public $street = null;
    public $city = null;
    public $state = null;
    public $postalCode = null;
    public $countryCode = null;

    public $driversLicense = null;
    public $socialSecurity = null;
    public $occupation = null;

    public $phone = array();
    public $email = null;
    public $emailDirect = null;

    public $pharmacy = null;
    public $status = null;
    public $contactRelationship = null;
    public $date = null;
    public $sex = null;
    public $referrer = null;
    public $provider = null;
    public $refProvider = null;
    public $financial = null;
    public $financialReviewDate = null;

    public $ethnoracial = null;
    public $ethnicity = null;
    public $race = null;
    public $interpretter = null;
    public $migrantSeasonal = null;

    public $familySize = null;
    public $monthlyIncome = null;
    public $homeless = null;

    public $portal = null;

    public $isDeceased = false;
    public $daysDeceased = null;

    public $balance = null;
    
    public function __construct($pid)
    {
        $this->setPatientById($pid);
    }

    public function setPatientById($pid) {
        $sql = 'SELECT * FROM patient_data 
                WHERE pid=? 
                ORDER BY date DESC 
                LIMIT 0,1';
        $result = sqlQuery($sql, array($pid));
        
        $this->mapper($result);
        $this->_determineIfDeceased($result['deceased_date']);
    }

    /**
     * Map database column names to class properties
     *
     * This better standardizes the field names. For instance, DOB from the DB
     * becomes dateOfBirth in the class.
     * 
     * @param  array $dbResults Results of a patient query
     * @return void             
     */
    public function mapper($dbResults)
    {
        $mapperArray = array(
            'fname' => 'firstName',
            'lname' => 'lastName',
            'mname' => 'middleName',
            'DOB'   => 'dateOfBirth',
            'postal_code' => 'postalCode',
            'country_code' => 'countryCode',
            'allow_patient_portal' => 'portal',
        );

        foreach($dbResults as $key => $value) {
            $property = (array_key_exists($key, $mapperArray)) 
                ? $mapperArray[$key] : $key;
            $this->$property = htmlspecialchars($value, ENT_NOQUOTES);
        }
    }

    /**
     * Helper function to return full patient name
     * @return string Patient's full name
     */
    public function getPatientName($format = 'f m l')
    {
        $middleName = ($this->middleName) ? " " . $this->middleName . " " : " ";
        $return = strtr($format, array(
            'f' => $this->firstName,
            'm' => $middleName,
            'l' => $this->lastName));
        return $return;
    }

    private function _determineIfDeceased($dbDate)
    {
        // Default state is alive, if the db doesn't have a date we can stop
        if ($dbDate == NULL || $dbDate == '' || $dbDate == '0000-00-00 00:00:00')
        {
            return;
        }

        $deceasedDate = new DateTime($dbDate);
        $now = new DateTime();
        $interval = $now->diff($deceasedDate);
        $daysDead = $interval->format('%a');
        $this->isDeceased = true;
        $this->daysDeceased = $daysDead;
    }

    public function patientBalance($with_insurance = false)
    {
        if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
            $sql = 'SELECT date, encounter, last_level_billed, last_level_closed, stmt_count
                    FROM form_encounter
                    WHERE pid = ?';
            $feres = sqlStatement($sql, array($this->pid));
            while ($ferow = sqlFetchArray($feres)) {
                $encounter = $ferow['encounter'];
                $dos = substr($ferow['date'], 0, 10);
                // @TODO
                // $insarr = getEffectiveInsurances($pid, $dos);
                // $inscount = count($insarr);
            }
        }
        // @TODO continue moving get_patient_balance to this class
      if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
        while ($ferow = sqlFetchArray($feres)) {
          if (!$with_insurance && $ferow['last_level_closed'] < $inscount && $ferow['stmt_count'] == 0) {
            // It's out to insurance so only the co-pay might be due.
            $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
              "pid = ? AND encounter = ? AND " .
              "code_type = 'copay' AND activity = 1",
              array($pid, $encounter));
            $drow = sqlQuery("SELECT SUM(pay_amount) AS payments " .
              "FROM ar_activity WHERE " .
              "pid = ? AND encounter = ? AND payer_type = 0",
              array($pid, $encounter));
            $ptbal = $insarr[0]['copay'] + $brow['amount'] - $drow['payments'];
            if ($ptbal > 0) $balance += $ptbal;
          }
          else {
            // Including insurance or not out to insurance, everything is due.
            $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
              "pid = ? AND encounter = ? AND " .
              "activity = 1", array($pid, $encounter));
            $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
              "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
              "pid = ? AND encounter = ?", array($pid, $encounter));
            $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
              "pid = ? AND encounter = ?", array($pid, $encounter));
            $balance += $brow['amount'] + $srow['amount']
              - $drow['payments'] - $drow['adjustments'];
          }
        }
        return sprintf('%01.2f', $balance);
      }
      else if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
        // require_once($GLOBALS['fileroot'] . "/library/classes/WSWrapper.class.php");
        $conn = $GLOBALS['adodb']['db'];
        $customer_info['id'] = 0;
        $sql = "SELECT foreign_id FROM integration_mapping AS im " .
          "LEFT JOIN patient_data AS pd ON im.local_id = pd.id WHERE " .
          "pd.pid = '" . $pid . "' AND im.local_table = 'patient_data' AND " .
          "im.foreign_table = 'customer'";
        $result = $conn->Execute($sql);
        if($result && !$result->EOF) {
          $customer_info['id'] = $result->fields['foreign_id'];
        }
        $function['ezybiz.customer_balance'] = array(new xmlrpcval($customer_info,"struct"));
        $ws = new WSWrapper($function);
        if(is_numeric($ws->value)) {
          return sprintf('%01.2f', $ws->value);
        }
      }
      return '';
    }
}
