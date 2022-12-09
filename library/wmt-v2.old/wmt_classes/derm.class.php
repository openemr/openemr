<?php
/** **************************************************************************
 *	DERMATOLOGY.INC.PHP
 *	This file contains the standard classes for the dermatology implementation
 *	of OpenEMR. The file must be included in each dermatology form file or the
 *	implementation will not function correctly.
 * 
 *  @package dermatology
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */

/** 
 * Provides standardized error reporting helper functions for the 'errors'
 * database table.
 *
 * @package Standard
 * @subpackage Lists
 */
class Issue {
	public $id;
	public $date;
	public $type;
	public $title;
	public $begdate;
	public $enddate;
	public $returndate;
	public $occurrence;
	public $classification;
	public $referredby;
	public $extrainfo;
	public $diagnosis;
	public $activity;
	public $comments;
	public $pid;
	public $user;
	public $groupname;
	public $outcome;
	public $destination;
	public $reinjury_id;
	public $injury_part;
	public $injury_type;
	public $injury_grade;
	public $reaction;
	
	/**
	 * Constructor for the 'error' class which retrieves the requested 
	 * error record from the database of creates an empty object.
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public function __construct($id = FALSE) {
		if(!$id) return false;

		$query = "SELECT * FROM lists WHERE id = $id";

		$results = sqlStatement($query);
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['id'];
			$this->date = $data['date'];
			$this->type = $data['type'];
			$this->title = $data['title'];
			$this->begdate = ($data['begdate'])? date('Y-m-d', strtotime($data['begdate'])) : '';
			$this->enddate = ($data['enddate'])? date('Y-m-d', strtotime($data['enddate'])) : '';
			$this->returndate = ($data['returndate'])? date('Y-m-d', strtotime($data['enddate'])) : '';
			$this->occurrence = $data['occurrence'];
			$this->classification = $data['classification'];
			$this->referredby = $data['refferredby'];
			$this->extrainfo = $data['extrainfo'];
			$this->diagnosis = $data['diagnosis'];
			$this->activity = $data['activity'];
			$this->comments = $data['comments'];
			$this->pid = $data['pid'];
			$this->user = $data['user'];
			$this->groupname = $data['groupname'];
			$this->outcome = $data['outcome'];
		}
		else {
			throw new Exception('Issue::_construct - no issue record with id ('.$id.').');
		}
	}	

	/**
	 * Inserts data from an error object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public static function insert(Issue $object) {
		if($object->id) {
			throw new Exception("Issue::insert - object already contains identifier");
		}

		// add generic treatment record
		$begdate = ($object->begdate) ? "'$object->begdate'" : "NULL";
		$enddate = ($object->enddate) ? "'$object->enddate'" : "NULL";
		$returndate = ($object->returndate) ? "'$object->returndate'" : "NULL";
		$destination = ($object->destination) ? "'$object->destination'" : "NULL";
		
		$object->id = sqlInsert("INSERT INTO lists SET " .
			"date = NOW(), " .
			"type = 'medical_problem', " .
			"title = '$object->title', " .
			"begdate = $begdate, " .
			"enddate = $enddate, " .
			"returndate = $returndate, " .
			"occurrence = '$object->occurrence', " .
			"classification = '$object->classification', " .
			"referredby = '$object->referredby', " .
			"extrainfo = '$object->extrainfo', " .
			"diagnosis = '$object->diagnosis', " .
			"activity = '$object->activity', " . 
			"comments = '$object->comments', " .
			"pid = '$object->pid', " .
			"user = '".$_SESSION['authUser']."', " .
			"groupname = '".$_SESSION['authProvider']."', " .
			"outcome = '$object->outcome', " .
			"destination = $destination, " .
			"injury_part = '$object->injury_part', " .
			"injurt_type = '$object->injury_type', " .
			"injury_grade = '$object->injury_grade', " .
			"reaction = '$object->reaction'");
		
		return $object->id;
	}

	/**
	 * Inserts data from an error object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public function update() {
//		if($this->id) {
//			throw new Exception("Issue::update - object does not contain identifier");
//		}

		// add generic treatment record
		$begdate = ($this->begdate) ? "'$this->begdate'" : "NULL";
		$enddate = ($this->enddate) ? "'$this->enddate'" : "NULL";
		$returndate = ($this->returndate) ? "'$this->returndate'" : "NULL";
		
		sqlInsert("UPDATE lists SET " .
			"title = '$this->title', " .
			"begdate = $begdate, " .
			"enddate = $enddate, " .
			"returndate = $returndate, " .
			"occurrence = '$this->occurrence', " .
			"classification = '$this->classification', " .
			"referredby = '$this->referredby', " .
			"extrainfo = '$this->extrainfo', " .
			"diagnosis = '$this->diagnosis', " .
			"activity = '$this->activity', " . 
			"comments = '$this->comments', " .
			"pid = '$this->pid', " .
			"user = '".$_SESSION['authUser']."', " .
			"groupname = '".$_SESSION['authProvider']."', " .
			"outcome = '$this->outcome' " .
			"WHERE id = $this->id ");
		
		return;
	}
}
	
/** 
 * Provides standardized error reporting helper functions for the 'errors'
 * database table.
 *
 * @package Standard 
 * @subpackage Treatments
 */
class Encounter {
	public $id;
	public $date;
	public $reason;
	public $facility;
	public $facility_id;
	public $pid;
	public $encounter;
	public $onset_date;
	public $sensitivity;
	public $billing_note;
	public $catid;
	public $catname;
	public $provider_id;
	public $supervisor_id;
	public $referral_source;
	public $billing_facility;
	public $problem_history;
	public $followup;
	public $nurse_notes;
	public $doctor_notes;
	public $doctor_approved;
	
	private $derm_id;
	private $derm_encounter;
	
	
	
	/**
	 * Constructor for the 'error' class which retrieves the requested 
	 * error record from the database of creates an empty object.
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT fe.*, de.id as dx_id, de.problem_history, de.nurse_notes, de.doctor_notes, de.doctor_approved, de.followup, pc.pc_catname FROM form_encounter fe ";
		$query .= "LEFT JOIN form_derm_encounter de ON fe.encounter = de.encounter ";
		$query .= "LEFT JOIN openemr_postcalendar_categories pc ON fe.pc_catid = pc.pc_catid ";
		$query .= "WHERE fe.id = $id ";
		$query .= "ORDER BY fe.date, fe.id";
		$results = sqlStatement($query);
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['id'];
			$this->date = ($data['date'])? date('Y-m-d',strtotime($data['date'])) : '';
			$this->reason = $data['reason'];
			$this->facility = $data['facility'];
			$this->facility_id = $data['facility_id'];
			$this->referral_source = $data['referral_source'];
			$this->pid = $data['pid'];
			$this->encounter = $data['encounter'];
			$this->onset_date = ($data['onset_date'])? date('Y-m-d',strtotime($data['onset_date'])) : '';
			$this->sensitivity = $data['sensitivity'];
			$this->billing_note = $data['billing_note'];
			$this->catid = $data['pc_catid'];
			$this->catname = $data['pc_catname'];
			$this->provider_id = $data['provider_id'];
			$this->supervisor_id = $data['supervisor_id'];
			$this->billing_facility = $data['billing_facility'];
			$this->derm_id = $data['dx_id'];
			$this->followup = $data['followup'];
			$this->problem_history = $data['problem_history'];
			$this->nurse_notes = $data['nurse_notes'];
			$this->doctor_notes = $data['doctor_notes'];
			$this->doctor_approved = $data['doctor_approved'];
		}
		else {
			throw new Exception('Encounter::_construct - no encounter record with id ('.$id.').');
		}
	}	
		
	/**
	 * Inserts data from an error object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public static function insert(Encounter $object) {
		if($object->id) {
			throw new Exception ("Encounter::insert - object already contains identifier");
		}

		// get facility name from id
		$fres = sqlQuery("SELECT name FROM facility WHERE id = $object->facility_id");
		$facility = $fres['name'];

		// create basic encounter
		$object->encounter = generate_id(); // in sql.inc
		
		// add base record
		$enc_date = ($object->date) ? "'$object->date'" : "NULL";
		$onset_date = ($object->onset_date) ? "'$object->onset_date'" : "NULL";

		$object->id = sqlInsert("INSERT INTO form_encounter SET " .
			"date = $enc_date, " .
			"onset_date = $onset_date, " .
			"reason = '$object->reason', " .
			"facility = '$facility', " .
			"pc_catid = '$object->catid', " .
			"facility_id = '$object->facility_id', " .
			"billing_facility = '$object->billing_facility', " .
			"sensitivity = '$object->sensitivity', " .
			"referral_source = '$object->referral_source', " .
			"pid = '$object->pid', " .
			"encounter = '$object->encounter', " .
			"provider_id = '$object->provider_id'");

		// add derm specific data
		$dxId = sqlInsert("INSERT INTO form_derm_encounter SET " .
			"date = '$object->date', " .
			"pid = '$object->pid', " .
			"encounter = '$object->encounter', " .
			"followup = '$object->followup', " .
			"problem_history = '$object->problem_history', " .
			"nurse_notes = '$object->nurse_notes', " .
			"doctor_notes = '$object->doctor_notes', " .
			"doctor_approved = '$object->doctor_approved'");
		
		return $object->id;
	}

	/**
	 * Inserts data from an error object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public function update() {
		if(!$this->id) {
			throw new Exception ("Encounter::update - object contains no identifier");
		}
		
		// get facility name from id
		$fres = sqlQuery("SELECT name FROM facility WHERE id = $this->facility_id");
		$facility = $fres['name'];

		// update basic encounter
		$enc_date = ($this->date) ? "'$this->date'" : "NULL";
		$onset_date = ($this->onset_date) ? "'$this->onset_date'" : "NULL";

		sqlInsert("UPDATE form_encounter SET " .
			"date = $enc_date, " .
			"onset_date = $onset_date, " .
			"reason = '$this->reason', " .
			"facility = '$facility', " .
			"pc_catid = '$this->catid', " .
			"facility_id = '$this->facility_id', " .
			"billing_facility = '$this->billing_facility', " .
			"sensitivity = '$this->sensitivity', " .
			"referral_source = '$this->referral_source', " .
			"pid = '$this->pid', " .
			"encounter = '$this->encounter', " .
			"provider_id = '$this->provider_id' " .
			"WHERE id = '$this->id'");

		// update derm specific data
		sqlInsert("UPDATE form_derm_encounter SET " .
			"date = '$this->date', " .
			"pid = '$this->pid', " .
			"encounter = '$this->encounter', " .
			"followup = '$this->followup', " .
			"problem_history = '$this->problem_history', " .
			"nurse_notes = '$this->nurse_notes', " .
			"doctor_notes = '$this->doctor_notes', " .
			"doctor_approved = '$this->doctor_approved' " .
			"WHERE id = '$this->derm_id'");
				
		return;
	}

	/**
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public static function getEncounters($pid) {
		if (!$pid) return FALSE;

		$query = "SELECT fe.encounter, fe.id FROM form_encounter fe ";
		$query .= "LEFT JOIN issue_encounter ie ON fe.id = ie.list_id ";
		$query .= "LEFT JOIN lists l ON ie.list_id = l.id ";
		$query .= "WHERE fe.pid = $pid AND l.enddate IS NULL ";
		$query .= "ORDER BY fe.date, fe.encounter";

		$results = sqlStatement($query);
	
		$txList = array();
		while ($data = sqlFetchArray($results)) {
			$txList[] = array('id' => $data['id'], 'encounter' => $data['encounter']);
		}
		
		return $txList;
	}

	/**
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public static function fetchEncounter($encounter) {
		if (!$encounter) return FALSE;

		$query = "SELECT id FROM form_encounter WHERE encounter = '$encounter' ";
		list($id) = sqlStatement($query);

		return new Encounter($id);
	}
}

/** 
 * Provides standardized error reporting helper functions for the 'errors'
 * database table.
 *
 * @package Dermatology
 * @subpackage Treatments
 */
class Category {
	public $id;
	public $name;
	public $color;
	public $description;
	
	
	/**
	 * Constructor for the 'error' class which retrieves the requested 
	 * error record from the database of creates an empty object.
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM openemr_postcalendar_categories ";
		$query .= "WHERE pc_catid = $id ";
		$results = sqlStatement($query);
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['pc_catid'];
			$this->name = $data['pc_catname'];
			$this->color = $data['pc_catcolor'];
			$this->description = $data['pc_catdesc'];
		}
		else {
			throw new Exception('Category::_construct - no category record with id ('.$this->id.').');
		}
	}	
		
	/**
	 * 
	 * @param int $id lists record identifier
	 * @return object instance of lists class
	 */
	public static function getCategories($display = TRUE) {
		$query = "SELECT pc_catid FROM openemr_postcalendar_categories ";
		if ($display) $query .= "WHERE pc_catid = 5 OR pc_catid > 8 ";
		$query .= "ORDER BY pc_catname";

		$results = sqlStatement($query);
	
		$catList = array();
		while ($data = sqlFetchArray($results)) {
			$catList[] = new Category($data['pc_catid']);
		}
		
		return $catList;
	}
}
	
?>
