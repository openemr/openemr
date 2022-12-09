<?php
/** **************************************************************************
 *	WMTVITALS.CLASS.PHP
 *	This file contains a vitals class for use with any form
 *
 *  NOTES:
 *  1) __CONSTRUCT - uses the encounter id to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the patient encouner record This object
 * does NOT include all of the fields associated with the core encounter data
 * record and should NOT be used for database updates.  It is intended only
 * for retrieval of partial patient information primarily for display 
 * purposes (reports for example).
 *
 */

include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
if(!class_exists('wmtVitals')) {

class wmtVitals{
	public $vid;
	public $vital_id;
	public $date;
	public $timestamp;
	public $height;
	public $height_metric;
	public $weight;
	public $weight_metric;
	public $bps;
	public $bpd;
	public $BMI;
	public $BMI_status;
	public $pulse;
	public $respiration;
	public $arm;
	public $o2;
	public $oxygen_saturation;
	public $temp;
	public $temperature;
	public $temperature_metric;
	public $temp_method;
	public $accucheck;
	public $diabetes_accucheck;
	public $note;
	public $waist_circ;
	public $head_circ;
	public $prone_bpd;
	public $prone_bps;
	public $standing_bpd;
	public $standing_bps;

	public $weight_plan;
	public $weight_counseling;
	public $h_pylori;
	public $hemoccult;
	public $mono;
	public $strep_a;
	public $UTP;
	public $ldl;

	public $specific_gravity;
	public $ph;
	public $leukocytes;
	public $nitrite;
	public $nitrates;
	public $protein;
	public $glucose;
	public $ketones;
	public $urobilinogen;
	public $bilirubin;
	public $blood;
	public $hemoglobin;
	public $HCG;
	public $LMP;

	public $HgbA1c;
	public $TC;
	public $LDL;
	public $HDL;
	public $trig;
	public $microalbumin;
	public $BUN;
	public $cr;
	public $use_metric;
	public $show_metric;
	public $pedi;
	public $wtage;
	public $statage;
	public $bmiage;


	/**
	 * Constructor for the 'vital' class which retrieves the requested 
	 * vital information from the database.
	 * 
	 * @param int $id form_vital id number 
	 * @return object instance of the vitals class
	 */
	public function __construct($id = FALSE, $suppress = TRUE) {
		$query = "SELECT * FROM form_vitals WHERE id =?";
		$vitals = array();
		if($id) $vitals = sqlQuery($query, array($id));
		if(!isset($vitals{'id'})) $vitals{'id'} = '';

		$this->use_metric = 0;
		$this->show_metric = 0;
		if($GLOBALS['units_of_measurement'] == 2 || 
							$GLOBALS['units_of_measurement'] == 4) $this->use_metric = 1;
		if($GLOBALS['units_of_measurement'] == 1) $this->show_metric = 1;

		$this->pedi = 0;
		$wtinf = $wtage = $htinf = $htage = $bmiper = null;
		$cdc_table = sqlQuery("SHOW TABLES LIKE 'cdc_data'");
		if ($GLOBALS['wmt::vitals_pedi_percent'] && $cdc_table !== false) {
			$this->pedi = 1;
		}

		if($vitals{'id'}) {
			foreach($vitals as $key => $val) {
				if(is_string($val)) $val = trim($val);
				$this->$key = $val;
				if($key == 'id') {
   				$this->vid = $val;
   				$this->vital_id = $val;
				}
				if($key == 'date') $this->timestamp = $val;
				if($key == 'height') $this->height_metric = convInToCm($val);
				if($key == 'weight') $this->weight_metric = convLbToKg($val);
				if($key == 'temperature') {
					$this->temp = $val;
					$this->temperature_metric = convFrToCl($val);
				}
			}

			if($suppress) {
     		$this->height = intval($vitals{'height'});
    		$this->weight = intval($vitals{'weight'});
     		$this->pulse = intval($vitals{'pulse'});
     		$this->respiration = intval($vitals{'respiration'});
     		$this->o2 = intval($vitals{'oxygen_saturation'});
     		$this->oxygen_saturation = intval($vitals{'oxygen_saturation'});
			}
		}
	}

	/**
	 * Retrieve a vitals object by encounter value. Uses the base constructor 
   * for the 'vitals' class to create and return the object.
	 * 
	 * @static
	 * @param int $enc encounter record 
	 * @param int $pid encounter patient id
	 * @return object instance of the vitals class
	 */
	public static function getVitalsByEncounter($enc='', $pid='', $suppress= FALSE) {
		if(!$pid) {
			throw new Exception('wmtVitals::getVitalsByEncounter - no pid provided.');
		}
		if($enc != '') {
   		// Check for vitals already taken for this encounter
   		$sql = "SELECT * FROM forms WHERE encounter=? AND form_name=? AND ".
					"pid=? AND deleted=? ORDER BY date DESC LIMIT 1";
			$binds= array($enc, 'Vitals', $pid, 0);
   		$vrec = sqlQueryNoLog($sql, $binds);
   		if($vrec{'form_id'}) return new wmtVitals($vrec{'form_id'}, $suppress);
		}

		return new wmtVitals();
	}

	/**
	 * Retrieve the most recent vitals object. Uses the base constructor 
   * for the 'vital' class to create and return the object.
	 * 
	 * @static
	 * @param int $pid patient identifier 
	 * @param date $date optional date to search prior to 
	 * @return object instance of vitals class
	 */
	public static function getVitalsByPatient($pid, $dt = '') {
		if(!$pid) {
			throw new Exception('wmtVitals::getVitalsByEncounter - no pid provided.');
		}
		
   	// Check for vitals already taken for this encounter
   	$sql = "SELECT * FROM forms WHERE form_name=? AND pid=? AND deleted=? ";
		$binds= array('Vitals', $pid, 0);
		if($dt) {
			$sql .= "AND date < ? ";
			$binds[] = $dt;
		}
		$sql .= "ORDER BY date DESC LIMIT 1";
   	$vrec = sqlQueryNoLog($sql, $binds);
   	if($vrec{'form_id'}) return new wmtVitals($vrec{'form_id'});
		
		return new wmtVitals('');
	}

	/**
	 * Check to see if we need to add a new vitas record
	 * 
	 * @static
	 * @param int $pid patient identifier 
	 * @param int $vid vital record that was loaded with the form
	 * @param array $vdata field references & data to be compared
	 * @return boolean indicator to add or not
	 */
	public static function vitalsChanged($vid, $vdata = array(), $suppress = TRUE) {
		if(!$vid) return '';

		$fres = sqlStatement('SHOW COLUMNS FROM form_vitals');
		$flds = array();
		while($fld = sqlFetchArray($fres)) {
			$flds[$fld{'Field'}] = $fld{'Type'};
		} 
		$comp = new wmtVitals($vid, $suppress);
		$changed = FALSE;
		foreach($vdata as $key => $val) {
			if($key == 'vid' || $key == 'date' || 
						substr($key,-7) == '_metric') continue;
			if(strtolower(substr($flds[$key],0,5)) == 'float') {
				if(!intval($comp->$key)) $comp->$key = '';
				if(!intval($val)) $val = '';
			}
			if(strtolower(substr($flds[$key],0,7)) == 'varchar') {
				if(strtolower(trim($val)) != strtolower(trim($comp->$key))) {
					if($changed) $changed .= ':';
					$changed .= $key . '(' . $val . '|' . $comp->$key . ')';
				}
			} else {
				if(trim($val) != trim($comp->$key)) {
					if($changed) $changed .= ':';
					$changed .= $key . '(' . $val . '|' . $comp->$key . ')';
				}
			}
		}
		return $changed;
	}

	/**
	 * Adds a new vitals record
	 * 
	 * @static
	 * @param int $pid patient identifier 
	 * @return object instance of the created vitals class
	 */
	public static function addVitals($pid, $enc='', $vdata = array(), $nowYMD = null) {
		if(!$pid) throw new Exception('wmtVitals::addVitals - No PID provided.');
		if(!$enc) $enc = $_SESSION['encounter'];
		if(!$enc) throw new Exception('wmtVitals::addVitals - ' .
			'No encounter provided, no session encounter.');

		if(!count($vdata)) return new wmtVitals('');
		$keep = FALSE;
		$vdata['wtage'] = '';
		$vdata['statage'] = '';
		$vdata['bmiage'] = '';
		foreach($vdata as $key => $val) {
			if(is_string($val)) $val = trim($val);
			if($val) $keep = TRUE;
		}
		if(!$keep) return new wmtVitals('');

		$use_metric = FALSE;
		$show_metric = FALSE;
		if($GLOBALS['units_of_measurement'] == 2 || 
								$GLOBALS['units_of_measurement'] == 4) $use_metric = TRUE;
		if($GLOBALS['units_of_measurement'] == 1) $show_metric = 1;
		
		$ageInMonths = '';
		$pedi = FALSE;
		$cdc_table = sqlQuery("SHOW TABLES LIKE 'cdc_data'");
		if($GLOBALS['wmt::vitals_pedi_percent'] && 
				$cdc_table !== false) $pedi = TRUE;
		if($pedi) {
    	$patient_data = getPatientData($pid);
    	$sex = (strtolower(substr($patient_data['sex'],0,1)) == 'm')? 1 : 2;
    		
		  // strip any dashes from the DOB
		  $dobYMD = preg_replace("/-/", "", $patient_data['DOB']);
		  $dobDay = substr($dobYMD,6,2); $dobMonth = substr($dobYMD,4,2); $dobYear = substr($dobYMD,0,4);
    
		  if ($nowYMD == null) {
		    $nowDay = date("d");
		    $nowMonth = date("m");
		    $nowYear = date("Y");
		  } else {
		    $nowDay = substr($nowYMD,6,2);
		    $nowMonth = substr($nowYMD,4,2);
		    $nowYear = substr($nowYMD,0,4);
		  }

		  $dayDiff = $nowDay - $dobDay;
		  $monthDiff = $nowMonth - $dobMonth;
		  $yearDiff = $nowYear - $dobYear;

		  $ageInMonths = (($nowYear * 12) + $nowMonth) - (($dobYear * 12) + $dobMonth);

			// DETERMINE MID RANGE AGE
			$age = round( $ageInMonths ) + .5;
		}
		
		$data = array();
		$data['wtage'] = '';
		$data['statage'] = '';
		$data['bmiage'] = '';
		$flds = sqlListFields('form_vitals');
		foreach($vdata as $key => $val) {
			if($key == 'id' || $key == 'pid' || $key == 'groupname' ||
				$key == 'user' || $key == 'authorized' || $key == 'vid') continue;
			if(in_array($key, $flds) === FALSE) continue;
			if(is_string($val)) $val = trim($val);

			if($key == 'height_metric' && $use_metric) {
				$data['height'] = convCmToIn($vdata[$key]);
				continue;
			}

			if($key == 'weight_metric' && $use_metric) {
				$data['weight'] = convKgToLb($vdata[$key]);
				continue;
			}

			if($key == 'temperature_metric' && $use_metric) {
				$data['temperature'] = convClToFr($vdata[$key]);
				continue;
			}
			if(substr($key, -7) == '_metric') continue;

			$data[$key] = $val;
		}

		if(isset($data['weight']) && $pedi) {
			$wt = floatval($data['weight']) * .45359237;
			$type = ( $age < 25 )? "WTAGEINF" : "WTAGE";
			
			$sql = "SELECT * FROM `cdc_data` WHERE `type` = ? AND `months` = ? AND `sex` = ?";
			$record = sqlQuery( $sql, array($type, $age, $sex) );
			
			if ($record) {
				$l = floatval($record['L']);
				$m = floatval($record['M']);
				$s = floatval($record['S']);

				// calculate Z value
				$z = ( pow( ($wt / $m), $l ) -1 ) / ( $l * $s );
				
				// determine percentile
				$data['wtage'] = round( self::cdf($z) * 100 );
			}
		}
			
		if (isset($data['height']) && $pedi) {
			$ht = floatval($data['height']) * 2.54;
			$type = ( $age < 25 )? "LENAGEINF" : "STATAGE";
			
			$sql = "SELECT * FROM `cdc_data` WHERE `type` = ? AND `months` = ? AND `sex` = ?";
			$record = sqlQuery( $sql, array($type, $age, $sex) );
			
			if ($record) {
				$l = floatval($record['L']);
				$m = floatval($record['M']);
				$s = floatval($record['S']);

				// calculate Z value
				$z = ( pow( ($ht / $m), $l ) -1 ) / ( $l * $s );
				
				// determine percentile
				$data['statage'] = round( self::cdf($z) * 100 );
			}
		}

		if (isset($data['BMI']) && $age > 24 && $pedi) {
			$bmi = floatval($data['BMI']);
			$type = "BMIAGE";
			
			$sql = "SELECT * FROM `cdc_data` WHERE `type` = ? AND `months` = ? AND `sex` = ?";
			$record = sqlQuery( $sql, array($type, $age, $sex) );
			
			if ($record) {
				$l = floatval($record['L']);
				$m = floatval($record['M']);
				$s = floatval($record['S']);

				// calculate Z value
				$z = ( pow( ($bmi / $m), $l ) -1 )  / ( $l * $s );
					
				// determine percentile
				$data['bmiage'] = round( self::cdf($z) * 100 );
			}
		}

		$conn = $GLOBALS['adodb']['db'];
		$vid = $conn->GenID('sequences');
		$data['id'] = $vid;
		$new_vid = wmtFormSubmit('form_vitals', $data, '', 0, $pid);
		addForm($enc,"Vitals",$vid,'vitals',$pid,$_SESSION['userauthorized']);
		return new wmtVitals($vid);
	}

	// calculates error function for standard distribution
    function erf($x) {
        $pi = pi();
        
        $a = ( 8 * ($pi - 3) ) / ( 3 * $pi * (4 - $pi) );
        $x2 = $x * $x;

        $ax2 = $a * $x2;
        $num = ( 4 / $pi ) + $ax2;
        $denom = 1 + $ax2;

        $inner = ( -$x2 ) * $num / $denom;
        $erf2 = 1 - exp($inner);

        return sqrt($erf2);
	}

	// calculate approximate cumulative standard distribution
	function cdf($n) {
		$result = '';
		
        if( $n < 0 ) {
			$result = ( 1 - self::erf( $n / sqrt(2) ) ) / 2;
        } else {
			$result = ( 1 + self::erf( $n / sqrt(2) ) ) / 2;
        }
        
        return $result;
	} 

	/**
	 * Outputs the vitals in the table format for the patient instruction /
   * patient clinical summary reports.
	 * 
	 * @static
	 * @param is the number of columns for formatted output
	 *   ha ha, that's possible for future use, for now we'll just go to 4
	 */
	public function wmtVitalsReport($cols = 4) {
		$use_metric = FALSE;
		$show_metric = FALSE;
		if($GLOBALS['units_of_measurement'] == 2 || 
						$GLOBALS['units_of_measurement'] == 4) $use_metric = TRUE;
		if($GLOBALS['units_of_measurement'] == 1) $show_metric = TRUE;
		
		echo "<fieldset style='border: solid 1px black;'><legend class='bkkPrnHeader'>&nbsp;Vitals&nbsp;</legend>\n";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		echo "	<tr>\n";
		echo "		<td colspan='4'><span class='bkkPrnLabel'>Vitals Taken:&nbsp;</span><span class='bkkPrnBody'>$this->timestamp</span></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td><span class='bkkPrnLabel'>Height:&nbsp;</span><span class='bkkPrnBody'>";
		echo $use_metric ? $this->height_metric : $this->height;
		echo "</span></td>\n";
		echo "		<td><span class='bkkPrnLabel'>Weight:&nbsp;</span><span class='bkkPrnBody'>";
		echo $use_metric ? $this->weight_metric : $this->weight;
		echo "</span></td>\n";
		echo "		<td><span class='bkkPrnLabel'>BMI:&nbsp;</span><span class='bkkPrnBody'>$this->BMI</span></td>\n";
		echo "		<td><span class='bkkPrnBody'>$this->BMI_status</span></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td><span class='bkkPrnLabel'>Blood Pressure:&nbsp;</span><span class='bkkPrnBody'>$this->bps&nbsp;/&nbsp;$this->bpd</span></td>\n";
		echo "		<td><span class='bkkPrnLabel'>Temperature:&nbsp;</span><span class='bkkPrnBody'>$this->temp</span></td>\n";
		echo "		<td><span class='bkkPrnLabel'>Pulse:&nbsp;</span><span class='bkkPrnBody'>$this->pulse</span></td>\n";
		echo "		<td><span class='bkkPrnLabel'>Respiration:&nbsp;</span><span class='bkkPrnBody'>$this->respiration</span></td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		echo " </fieldset>\n";
	}
}

}

?>
