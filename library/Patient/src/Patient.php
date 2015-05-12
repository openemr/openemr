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

require_once '../../sql.inc';

namespace OpenEMR\Patient;

/**
* Class representation of a single patient
*/
class Entity
{

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
    public function getPatientName($format)
    {
        $middleName = ($this->middleName) ? " " . $this->middleName . " " : " ";
        $return = strtr($format, array(
            'f' => $this->firstName,
            'm' => $middleName,
            'l' => $this->lastName));
        return 'leo';
    }
}
