<?php
/** **************************************************************************
 *	DASHBOARD.CLASS.PHP
 *	This file contains the standard classes for interacting with the          
 *	'Dashboard' which is a Williams Medical Technologies option for OpenEMR.  
 *	This class must be included for dashboard integration.
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the record ID to retrieve data from the database
 *  2) GET - uses alternate selectors to find and return associated object
 *  3) FIND - returns only the object ID without data using alternate selectors
 *  4) LIST - returns an array of IDs meeting specific selector criteria
 *  5) FETCH - returns an array of data meeting specific criteria
 *   
 * 
 *  @package dashboard
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 *
 * @package WMT
 * @subpackage Standard
 * @category Patient
 * 
 */
include_once($GLOBALS['srcdir'].'/patient.inc');

if(!class_exists('wmtDashboard')) {

class wmtDashboard {
	public $id;
	public $pid;
  public $db_form_dt;
	public $db_hpi;
	public $db_cc;
	public $db_last_colon;
	public $db_last_bone;
	public $db_last_chol;
	public $db_last_lipid;
	public $db_last_lipo;
	public $db_last_tri;
	public $db_last_hepc;
	public $db_last_pap;
	public $db_last_mamm;
	public $db_mam_law;
	public $db_last_ww;
	public $db_last_mp;
	public $db_age_men;
	public $db_last_rectal;
	public $db_last_psa;
	public $db_last_db_foot;
	public $db_last_db_eye;
	public $db_last_db_screen;
	public $db_last_db_dbsmt;
	public $db_last_pft;
	public $db_bc;
	public $db_bc_chc;
	public $db_sex_active;
	public $db_sex_act_nt;
	public $db_sex_nt;
	public $db_pflow;
	public $db_pflow_dur;
	public $db_pfreq;
	public $db_pfreq_days;
	public $db_pregnancies;
	public $db_deliveries;
	public $db_live_births;
	public $db_hpv;
	public $db_last_hpv;
	public $db_group_b_strep;
	public $db_latex_allergy;
	public $db_drug_allergy;
	public $db_HCG;
	public $db_hcg;
	public $db_pmh_blood;
	public $db_last_urine_alb;
	public $db_last_sigmoid;
	public $db_last_col_scrn;
	public $db_last_fecal;
	public $db_last_barium;
	public $db_last_glaucoma;
	public $db_last_hgba1c;
	public $db_last_hgba1c_val;
	public $db_last_ekg;
	public $db_contact_email;
	public $db_emergency_contact;
	public $db_contact_cell;
	public $db_education;
	public $db_fh_nt;
	public $db_fh_non_contrib;
	public $db_fh_adopted;
	public $db_fh_extra_yes;
	public $db_fh_extra_no;
	public $db_birth_nt;
	public $db_place_of_birth;
	public $db_adopted;
	public $db_brothers;
	public $db_sisters;
	public $db_birth_order;
	public $db_birth_of;
	public $db_sex_orient;
	public $db_sex_orient_nt;
	public $db_sex_sti;
	public $db_sex_sti_nt;
	public $db_num_marriages;
	public $db_num_divorces;
	public $db_num_children;
	public $db_pap_nt;
	public $db_pap_hist_nt;

	public $db_smoking;
	public $db_smoking_desc;
	public $db_smoking_dt;
	public $db_smoking_status;
	public $db_alcohol;
	public $db_alcohol_note;
	public $db_alcohol_dt;
	public $db_drug_use;
	public $db_drug_note;
	public $db_drug_dt;
	public $db_coffee_use;
	public $db_coffee_note;
	public $db_coffee_dt;
	public $db_other_nt;
	public $db_wellness_nt;

	public $db_ped_diet_f;
	public $db_ped_diet_b;
	public $db_ped_diet_o;
	public $db_ped_diet_nt;
	public $db_ped_diet_tube;
	public $db_ped_diet_ttype;
	public $db_ped_diet_tsize;
	public $db_ped_diet_chc;
	public $db_ped_diet_amt;
	public $db_ped_diet_rate;
	public $db_ped_diet_special;
	public $db_ped_diet_other;

	public $db_DOB;
	public $db_race;
	public $db_status;
	public $db_occupation;
	public $db_wmt_education;
	public $db_language;
	public $db_ethnicity;
	public $db_wmt_partner_name;
	public $db_wmt_partner_ph;
	public $db_wmt_father_name;
	public $db_wmt_father_ph;
	public $db_street;
	public $db_city;
	public $db_state;
	public $db_postal_code;
	public $db_phone_home;
	public $db_phone_biz;
	public $db_phone_cell;
	public $db_email;
	public $db_contact_relationship;
	public $db_phone_contact;

  private $dates = array('db_last_chol', 'db_last_lipid', 'db_last_hepc', 
		'db_last_lipo', 'db_last_tri', 'db_last_urine_alb', 'db_last_hgba1c');

	// generated values - none in use currently
	
	/**
	 * Constructor for the 'dashboard' class which retrieves the requested 
	 * dashboard information from the database or creates an empty object.
	 * 
	 * @param int $id dashboard record identifier
	 * @return object instance of dashboard class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM form_dashboard WHERE id =?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			foreach($data as $key => $val) {
				$this->$key = $val;
			}
		} else {
			throw new Exception("wmtDashboard::_construct - no dashboard with id ($id).");
		}

		// NEED TO ELIMINATE THIS ALSO, NO NEED TO DUPLICATE
		$pat = sqlQuery("SELECT * FROM patient_data WHERE pid=?",array($this->pid));
		foreach($pat as $key => $val) {
				$key = 'db_'.$key;
				if(isset($this->$key)) $this->$key = $val;
		}
		// EVENTUALLY JUST ELIMINATE THIS
    $exists = getHistoryData($this->pid);
    if($exists) {
			if($exists['tobacco'] == '') $exists['tobacco'] = '|||';
     	list($this->db_smoking, $this->db_smoking_desc, $this->db_smoking_dt, $this->db_smoking_status) = explode('|', $exists['tobacco']);
			if($exists['alcohol'] == '') $exists['alcohol'] = '||';
      list($this->db_alcohol_note, $this->db_alcohol, $this->db_alcohol_dt) = explode('|', $exists['alcohol']);
			if($exists['recreational_drugs'] == '') $exists['recreational_drugs'] = '||';
      list($this->db_drug_note, $this->db_drug_use, $this->db_drug_dt) = explode('|', $exists['recreational_drugs']);
			if($exists['coffee'] == '') $exists['coffee'] = '||';
      list($this->db_coffee_note, $this->db_coffee_use, $this->db_coffee_dt) = explode('|', $exists['coffee']);
    }
		
		$sql = "SELECT * FROM list_options WHERE list_id='Rules_To_DB'";
		$fres = sqlStatement($sql);
		while($frow = sqlFetchArray($fres)) {
			// PROCESS EACH RULE AND LOOK FOR A MORE RECENT DATE
			$sql = "SELECT * FROM rule_patient_data WHERE pid=? AND item=? ".
				"AND complete=? ORDER BY date DESC LIMIT 1";
			$rrow = sqlQuery($sql,array($this->pid,$frow{'title'},$frow{'codes'}));
			if(!isset($rrow{'date'})) $rrow{'date'} = '';
			if(!isset($rrow{'result'})) $rrow{'result'} = '';
			$date = substr($rrow{'date'},0,10);
			$target = $frow{'option_id'};
			$target_val = $frow{'option_id'} . '_val';
			if($date > $this->$target || !is_int(substr($this->$target,0,1))) {
				$this->$target = $date;
				if(isset($this->$target_val)) $this->$target_val = $rrow{'result'};
				// PUSH THIS BACK IMMEDIATELY JUST TO KEEP THE DASHBOARD UPDATED
				$binds = array($date);
				$update = "UPDATE form_dashboard SET $target = ?";
				if(isset($this->$target_val)) {
					$update .= ", $target_val = ?"; 
					$binds[] = $rrow{'result'};
				}
				$update .= ' WHERE id = ?';
				$binds[] = $this->id;
				sqlStatement($update, $binds);
			}
		}
	}

	/**
	 * Retrieve a dashboard object by PID value. Uses the base constructor 
   * for the 'dashboard' class to create and return the object.  Since only 
   * one dashboard is allowed per patient we will create a blank one if 
   * nothing is found.	 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of patient class
	 */
	public static function getPidDashboard($pid) {
		if(!$pid) {
			throw new Exception('wmtDashboard::getPidDashboard - no PID provided.');
		}
		
		$results = sqlStatementNoLog("SELECT id FROM form_dashboard WHERE pid =?",
			 array($pid));
		$data = sqlFetchArray($results);
		$binds=array($pid, $_SESSION['authUser'], $_SESSION['authProvider']);
		if(!$data['id']) {
			$data['id'] = sqlInsert("INSERT INTO form_dashboard SET " .
			"date = NOW(), pid = ?, user = ?, groupname = ?", $binds);
    }
		return new wmtDashboard($data['id']);
	}
	
	/**
	 * Retrieve a shortened dashboard object just containing data/display 
   * fields by PID value. Uses the base constructor 
   * for the 'dashboard' class to create and return the object.  Since only 
   * one dashboard is allowed per patient we will create a blank one if 
   * nothing is found.	 
	 * 
	 * @static
	 * @param int $pid patient record pid
	 * @return object instance of patient class
	 */
	public static function getPidDisplayDashboard($pid) {
		if(!$pid) {
			throw new Exception('wmtDashboard::getPidDisplayDashboard - no PID provided.');
		}
		
		$results = sqlQuery("SELECT `id` FROM form_dashboard WHERE `pid` = ?",
			 array($pid));
		if(!isset($results['id'])) $results['id'] = '';
		$binds=array($pid, $_SESSION['authUser'], $_SESSION['authProvider']);
		if(!$results['id']) {
			$results['id'] = sqlInsert("INSERT INTO form_dashboard SET " .
			"`date` = NOW(), `pid` = ?, `user` = ?, `groupname` = ?", $binds);
    }
		$db = new wmtDashboard($results['id']);
		unset($db->id);
		unset($db->pid);
		return $db;
	}
	
  /**
 * Updates the dashboard information in the database.
 * 
 * @static
 * @param Errors $iderror_object
 * @return null
 */
	public function update_rules() {
		// CREATE NEW CLINICAL RULE DATA AS COMPLETE FOR NEW ENTRIES AS MAPPED
		$sql = "SELECT * FROM list_options WHERE list_id='Rules_To_DB'";
		$fres = sqlStatementNoLog($sql);
		while($frow = sqlFetchArray($fres)) {
			// IS THERE A DATE FOR THIS RULE IN THE DASHBOARD?
			$target_field = $frow{'option_id'};
			$target_value = $frow{'option_id'} . '_val';
			if($this->$target_field == '' || $this->$target_field == '0000-00-00' ||
						$this->$target_field == 0) continue;
			// if (in_array($target_field, $this->dates)) 
					// $this->$target_field = dateToYYYYMMDD($value);
			// echo "Evaluating ($target_field)<br>\n";

			// IS THERE A VALUE TO GO WITH THE DATE?
			$val = 'See Clinical Record';
			if(isset($this->$target_value)) $val = $this->$target_value;
			
			// GET THE MOST RECENT RULE FOR THIS ACTION ITEM FROM THE PATIENT EVENTS
			$sql = "SELECT * FROM rule_patient_data WHERE pid=? AND item=? ".
				"AND complete=? ORDER BY date DESC LIMIT 1";
			$rrow = sqlQuery($sql,array($this->pid,$frow{'title'},$frow{'codes'}));
			if(!isset($rrow{'date'})) $rrow{'date'} = '';
			$recent_date = substr($rrow{'date'},0,10);
			// echo "This is My Recent Date: [$recent_date]<br>\n";

			// IF THE EXISTING EVENT DATE IS OLDER, INSERT THE NEW ONE
			if($recent_date < $this->$target_field) {
				// echo "The Rule Date is Less<br>\n";
				$sql = "SELECT * FROM rule_action_item WHERE item=? ".
							"AND custom_flag=?";
				$rres = sqlStatementNoLog($sql,array($frow{'title'},'1'));
				while($rrow = sqlFetchArray($rres)) {
					$new_date = $this->$target_field.' 00:00:00';
					$sql = 'INSERT INTO `rule_patient_data` (`date`, `pid`, ' .
						'`category`, `item`, `complete`, `result`) VALUES ' .
						'(?, ?, ?, ?, ?, ?)';
					$binds = array($new_date, $this->pid, $rrow{'category'}, 
							$rrow{'item'}, $frow{'codes'}, $val);
					// THIS IS TO REMOVE ITEMS CREATED FROM BAD DATES, WHICH
					// END UP AS ALL ZEROES IN THE TABLE
					$rule_id = sqlInsert($sql, $binds);
					$fres = sqlQuery('SELECT * FROM `rule_patient_data` WHERE `id` = ?',
						array($rule_id));
					if($fres{'date'} == '0000-00-00 00:00:00') {
						sqlStatement('DELETE FROM `rule_patient_data` WHERE `id` = ?',
							array($rule_id));
					}
				}
			}
		}		
		return;
	}
	
	
  /**
 * Updates the dashboard information in the database.
 * 
 * @static
 * @param Errors $iderror_object
 * @return null
 */
	public function update() {
		$binds = array($_SESSION['authUser'], $_SESSION['userauthorized']);
		$query = "`date` = NOW(), `user`= ?, `authorized` = ?, `activity` = 1";
		$fields = sqlListFields('form_dashboard');
		$fields = array_slice($fields,7);
		$hist_values = false;
		if($this->db_smoking_status) {
  		if($this->db_smoking_status == 1) $this->db_smoking_desc = 'currenttobacco';
  		if($this->db_smoking_status == 2) $this->db_smoking_desc = 'currenttobacco';
  		if($this->db_smoking_status == 3) $this->db_smoking_desc = 'quittobacco';
  		if($this->db_smoking_status == 4) $this->db_smoking_desc = 'nevertobacco';
  		if($this->db_smoking_status == 5) $this->db_smoking_desc = 'not_applicabletobacco';
  		if($this->db_smoking_status == 9) $this->db_smoking_desc = 'not_applicabletobacco';
		} else $this->db_smoking_desc = '';
		foreach ($this as $key => $value) {
			if($value != '') {
				if((substr($key,0,6) == 'db_smo') || (substr($key,0,6) == 'db_alc') ||
						(substr($key,0,6) == 'db_dru') || (substr($key,0,7) == 'db_coff')) 
								$hist_values = true;
			}
			if (!in_array($key, $fields)) continue;
			if (in_array($key, $this->dates)) {
				// $value = dateToYYYYMMDD($value);
				$this->$key = $value;
			}
			if ($query) $query .= ", ";
			$query .= "`$key` = ?";
			$binds[] = $value;
		}
		$binds[] = $this->id;	
		sqlInsert("UPDATE form_dashboard SET $query WHERE `id` = ?", $binds);

		// update the social drug info in history always
    $exists=getHistoryData($this->pid);
		$hist = array();
		$hist['tobacco'] = $this->db_smoking.'|'.$this->db_smoking_desc.'|'.
				$this->db_smoking_dt.'|'.$this->db_smoking_status;
		$hist['alcohol'] = $this->db_alcohol_note.'|'.$this->db_alcohol.'|'.
				$this->db_alcohol_dt;
		$hist['recreational_drugs'] = $this->db_drug_note.'|'.
				$this->db_drug_use.'|'.$this->db_drug_dt;
		$hist['coffee'] = $this->db_coffee_note.'|'.
				$this->db_coffee_use.'|'.$this->db_coffee_dt;
    if($exists) {
			$change = false;
			if($exists['tobacco'] != $hist['tobacco']) $change = true;
			if($exists['alcohol'] != $hist['alcohol']) $change = true;
			if($exists['recreational_drugs'] != $hist['recreational_drugs']) $change = true;
			if($exists['coffee'] != $hist['coffee']) $change = true;
			// echo "Change: $change<br>\n";
			if($change) updateHistoryData($this->pid, $hist);
		} else {
			if($hist_values) newHistoryData($this->pid, $hist);
		}

		$this->update_rules();
		// echo "This is saved with the new dashboard<br>\n";
		// exit;
		return true;
	}
	

	/**
	 * Inserts data from a dashboard object into the database.
	 * 
	 * @static
	 * @param Errors $iderror_object
	 * @return null
	 */
	public static function insert(wmtDashboard $object) {
		if($object->id) {
			throw new Exception("wmtDashboard::insert - object contains ID");
		}

		$binds = array($object->pid, $_SESSION['authUser'], 
				$_SESSION['authProvider']);
		$object->id = sqlInsert("INSERT INTO `form_dashboard` SET " .
			"`date` = NOW(), `pid` = ?, `user` = ?, `groupname` = ?", $binds);
		
		return $object->id;
	}

	/**
	 * Checks to see if we will update any dashboard fields, if so we will 
	 * also set the form date.
	 *
	 * @static
	 * @param - an array of fields indexed by dashboard field names
	 * @return - true if we need to update
	 */
	public static function change($values=array(),$object) {
		if(count($values) < 1) return false;
		$change = false;
		$flds = sqlListFields('form_dashboard');
		$flds = array_slice($flds,7);
		foreach($values as $key => $val) {
			if(in_array($key, $flds)) {
				if($val && ($val != 'YYYY-MM-DD') && ($val != $object->$key)) {
					// if (in_array($key, $this->dates)) $val = dateToYYYYMMDD($val);
					$change = true;
					$object->$key = $val;
				}
			}
		}	
	}
}

}

?>
