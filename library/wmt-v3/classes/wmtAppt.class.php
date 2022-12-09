<?php
/** **************************************************************************
 *	wmtAppt.class.php
 *
 *	Copyright (c)2018 - Medical Technology Services <MDTechSvcs.com>
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
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Provides standardized processing for appointment records.
 *
 * @package wmt
 * @subpackage appt
 * 
 */
class Appt {
	public $pc_eid;
	public $pc_catid;
	public $pc_multiple;
	public $pc_aid;
	public $pc_pid;
	public $pc_title;
	public $pc_time;
	public $pc_hometext;
	public $pc_comments;
	public $pc_counter;
	public $pc_topic;
	public $pc_informant;
	public $pc_eventDate;
	public $pc_endDate;
	public $pc_duration;
	public $pc_recurrtype;
	public $pc_recurrspec;
	public $pc_recurrfreq;
	public $pc_startTime;
	public $pc_endTime;
	public $pc_alldayevent;
	public $pc_location;
	public $pc_conttel;
	public $pc_contname;
	public $pc_contemail;
	public $pc_website;
	public $pc_fee;
	public $pc_evenstatus;
	public $pc_sharing;
	public $pc_language;
	public $pc_apptstatus;
	public $pc_prefcatid;
	public $pc_facility;
	public $pc_sendalertsms;
	public $pc_sendalertemail;
	public $pc_billing_location;
	public $pc_insurance;
	public $pc_notetext;
	public $pc_room;
	public $pc_telemed;
	public $pc_telemed_location;
	public $pc_telemed_linked_eid;
	public $pc_daysendalertsms;
	public $pc_recare;
	public $pc_recare_id;
	public $pc_recare_user;
	public $cda_guid;

	public $zm_id;
	public $zm_start_url;
	public $zm_join_url;
	public $zm_password;
	
	// generated values
	public $category;
	public $facility;
	
	/**
	 * Constructor for the 'wmtAppt' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of wmtAppt class
	 * 
	 */
	public function __construct($id = false) {
		// create empty record or retrieve
		if (!$id) return false;

		// retrieve data
		$query = "SELECT pe.*, za.`m_id` as `zm_id`, za.`start_url` as `zm_start_url`, za.`join_url` as `zm_join_url`, za.`password` as `zm_password` FROM `openemr_postcalendar_events` as pe LEFT JOIN `zoom_appointment_events` as za ON za.`pc_eid` = pe.`pc_eid` WHERE pe.`pc_eid` = ?";
		$binds = array($id);
		$data = sqlQueryNoLog($query, $binds);

		if ($data && $data['pc_eid']) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		} else {
			throw new \Exception('wmtAppt::_construct - no record with appointment id ('.$id.').');
		}

		// preformat commonly used data elements
		$this->pc_time = (strtotime($this->pc_time) !== false)? date('Y-m-d H:i:s',strtotime($this->pc_time)) : null;
		$this->pc_eventDate = (strtotime($this->pc_eventDate) !== false)? date('Y-m-d H:i:s',strtotime($this->pc_eventDate)) : null;
		$this->pc_endDate = (strtotime($this->pc_endDate) !== false)? date('Y-m-d H:i:s',strtotime($this->pc_endDate)) : null;
		
		$this->category = 'Unspecified';
		if (isset($this->pc_catid)) {
			$cat_record = sqlQueryNoLog("SELECT `pc_catname` FROM `openemr_postcalendar_categories` WHERE `pc_catid` = ?", array($this->pc_catid));
			if (isset($cat_record['pc_catname'])) $this->category = $cat_record['pc_catname'];
		}
			
		$this->facility = '';
		if (isset($this->pc_facility)) {
			$fac_record = sqlQueryNoLog("SELECT `name` FROM `facility` WHERE `id` = ?", array($this->pc_facility));
			if (isset($fac_record['name'])) $this->facility = $fac_record['name'];
		}
			
		$this->provider = '';
		if (isset($this->pc_aid)) {
			$prv_record = sqlQueryNoLog("SELECT `fname`, `mname`, `lname` FROM `users` WHERE `id` = ?", array($this->pc_aid));
			if (isset($prv_record['lname'])) $this->provider = substr($prv_record['fname'],0,1) .'. '. $prv_record['lname'];
		}
			
		return;
	}

	public function loadFutureAppointment($pid = false, $appt_date = false, $eid = false) {
		if (!$pid || !$appt_date) return false;

		$query = "SELECT * FROM `openemr_postcalendar_events` WHERE `pc_pid` = ? AND cast(concat(pc_eventDate, ' ', pc_startTime) as datetime) > ? ";
		$binds = array($pid, $appt_date);

		if($eid) {
			$query .= " AND `pc_eid` != ? ";
			$binds[] = $eid;
		}

		$data = array();
		$result = sqlStatementNoLog($query, $binds);
		while ($result_data = sqlFetchArray($result)) {
			$facility_name = '';
			if (isset($result_data['pc_facility'])) {
				$fac_record = sqlQueryNoLog("SELECT `name` FROM `facility` WHERE `id` = ?", array($result_data['pc_facility']));
				if (isset($fac_record['name'])) $facility_name = $fac_record['name'];
			}

			$provider_name = '';
			if (isset($result_data['pc_aid'])) {
				$prv_record = sqlQueryNoLog("SELECT `fname`, `mname`, `lname` FROM `users` WHERE `id` = ?", array($result_data['pc_aid']));
				if (isset($prv_record['lname'])) $provider_name = substr($prv_record['fname'],0,1) .'. '. $prv_record['lname'];
			}

			$result_data['facility_name'] = $facility_name;
			$result_data['provider_name'] = $provider_name;
			$data[] = $result_data;
		}

		return $data;
	}

}

?>