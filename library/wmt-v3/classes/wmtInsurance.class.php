<?php
/** **************************************************************************
 *	INSURANCE.CLASS.PHP
 *
 *	Copyright (c)2017 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage insurance
 *  @version 2.0.0
 *  @category Insurance Data Utilities
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides a representation of the insurance information. Fields are statically defined
 * but are stored in multiple database tables. The information is integrated  
 *
 * @package WMT
 * @subpackage Standard
 * @category Insurance
 * 
 */
class Insurance {
	// generated values
	public $subscriber_format_name;
	public $subscriber_birth_date;
	public $subscriber_age;
	
	/**
	 * Constructor for the 'wmtInsurance' class which retrieves the requested 
	 * patient insurance information from the database or creates an empty object.
	 * 
	 * @param int $id insurance data record identifier
	 * @return object instance of patient insurance class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT a.*, i.*, c.name AS company_name, c.id AS company_id, c.cms_id FROM insurance_data i ";
		$query .= "LEFT JOIN insurance_companies c ON i.provider = c.id ";
		$query .= "LEFT JOIN addresses a ON a.foreign_id = c.id ";
		$query .= "WHERE i.id = ? LIMIT 1 ";
		
		$data = sqlQuery($query,array($id));
		if ($data && $data['provider']) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('wmtInsurance::_construct - no insurance record with id ('.$id.').');
		}
		
		if ($this->subscriber_DOB && strtotime($this->subscriber_DOB)) { // strtotime returns FALSE on date error
			$this->subscriber_age = floor( (strtotime('today') - strtotime($this->subscriber_DOB)) / 31556926);
			$this->subscriber_birth_date = date('Y-m-d', strtotime($this->subscriber_DOB));
		}
		
		return;
	}	

	/**
	 * Retrieve a insurance object by PID value. Uses the base constructor for the 'insurance' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param int $id patient record identifier
	 * @param string $type 'primary', 'secondary', 'tertiary'
	 * @return array object list of insurance objects
	 */
	public static function getPidInsurance($pid, $type = null) {
		if(! $pid)
			throw new \Exception('wmtInsurance::getPidInsurance - no patient identifier provided.');
		
		$query = "SELECT id, type, date FROM insurance_data WHERE pid = ? ";
		if ($type) $query .= "AND type = ? ";
		$query .= "AND provider != '' AND provider IS NOT NULL ";
		$query .= "ORDER BY date DESC ";

		$list = array();
		$params = array();
		$params[] = $pid;
		if ($type) $params[] = strtolower($type);

		$results = sqlStatement($query,$params);
		while ($data = sqlFetchArray($results)) {
			if ($data['type'] == 'primary' && !$list[0]) $list[0] = new Insurance($data['id']);
			if ($data['type'] == 'secondary' && !$list[1]) $list[1] = new Insurance($data['id']);
			if ($data['type'] == 'tertiary' && !$list[2]) $list[2] = new Insurance($data['id']);
		}
		
		return $list;
	}
	
	/**
	 * Retrieve a insurance object by PID value that was active on a given date. 
	 * Uses the base constructor for the 'insurance' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param int $pid patient record identifier
	 * @param date $date insurance as of date
	 * @param string $type 'primary', 'secondary', 'tertiary'
	 * @return array object list of insurance objects
	 */
	public static function getPidInsDate($pid, $date, $type = null) {
		if(! $pid)
			throw new \Exception('wmtInsurance::getPidInsDate - no patient identifier provided.');

		if(!$date || strtotime($date) === false) // strtotime returns FALSE or -1 on invalid date
			throw new \Exception('wmtInsurance::getPidInsDate - invalid date provided.');

		$query = "SELECT id, type, date FROM insurance_data WHERE pid = ? ";
		$query .= "AND provider != '' AND provider IS NOT NULL "; 
		$query .= "AND date <= ? ";
		if ($type) $query .= "AND type = ? ";
		$query .= "ORDER BY date DESC ";

		$list = array();
		$params = array();
		$params[] = $pid;
		$params[] = date('Y-m-d',strtotime($date));
		if ($type) $params[] = strtolower($type);
		
		$results = sqlStatement($query,$params);
		while ($data = sqlFetchArray($results)) {
			if ($data['type'] == 'primary' && !$list[0]) $list[0] = new Insurance($data['id']);
			if ($data['type'] == 'secondary' && !$list[1]) $list[1] = new Insurance($data['id']);
			if ($data['type'] == 'tertiary' && !$list[2]) $list[2] = new Insurance($data['id']);
		}  

		return $list;
	}
	
	/**
	 * Retrieve a single insurance company.
	 * 
	 * @static
	 * @param int $provider insurance provider identifier
	 * @return array insurance company data record
	 */
	public static function getCompany($provider) {
		if(! $provider)
			throw new \Exception('wmtInsurance::getCompany - no insurance company provider identifier.');
		
		$record = array();
		if ($provider == 'self') {
			$record['name'] = "Self Insured";
		}
		else {
			$query = "SELECT ia.*, ip.*, ic.id AS company_id, ic.name AS company_name FROM insurance_companies ic ";
			$query .= "LEFT JOIN addresses ia ON ia.foreign_id = ic.id ";
			$query .= "LEFT JOIN phone_numbers ip ON ip.foreign_id = ic.id ";
			$query .= "WHERE ic.id = ? LIMIT 1 ";
			$record = sqlQuery($query,array($provider));
		}
				
		return $record;
	}
}



?>
