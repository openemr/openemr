<?php

namespace OpenEMR\OemrAd;

include_once("../interface/globals.php");
include_once("$srcdir/patient.inc");


class Utility {

	/*Constructor*/
	public function __construct() {
		
	}

	public static function getSectionValues($uid, $id = '') {
		$sql = "SELECT * FROM `preserve_section_values` WHERE uid = ? ";

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($uid));
		while ($result_data = sqlFetchArray($result)) {
			$isjson = self::isJson($result_data['value']);
			$value = $isjson === true ? json_decode($result_data['value'], true) : $result_data['value']; 

			$resultItems[$result_data['fl_name']] = $value;
		}

		return $resultItems;
	}

	public static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public static function isSectionValuesExist($uid, $id = '') {
		$row = sqlQuery("SELECT * FROM `preserve_section_values` WHERE uid = ? AND fl_name = ? ", array($uid, $id));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function saveSectionValues($uid, $id, $value) {

		//Check is Value exists or not
		$isSectionValuesExist = self::isSectionValuesExist($uid, $id);
		$newValue = is_array($value) ? json_encode($value) : $value;

		if($isSectionValuesExist === false) {
			//Write new record
			$sql = "INSERT INTO `preserve_section_values` ( ";
			$sql .= "uid, fl_name, value ) VALUES (?, ?, ?) ";
				
			sqlInsert($sql, array($uid, $id, $newValue));

			return true;
		} else {

			//Update Record
			sqlStatementNoLog("UPDATE `preserve_section_values` SET value = ? WHERE fl_name = ? AND uid = ? ", array($newValue, $id, $uid));

			return true;
		}
		
		return false;
	}

	public static function saveFilterValueOfPatientTracker($uid, $data) {
		$fieldList = array('form_apptcat', 'form_apptstatus', 'form_facility', 'form_provider');
		
		foreach ($fieldList as $key => $item) {
			if(isset($data[$item])) {
				self::saveSectionValues($uid, 'flow_board_'.$item, $data[$item]);
			}
		}
	}

	public static function getPatientAlertInfo($pid = '') {
		$row = sqlQuery("SELECT * FROM `patient_data` WHERE id = ? ", array($pid));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function getAlertSVG() {
		return '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 512 512" width="12pt" height="12pt"  style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g>
		<path d="M507.494,426.066L282.864,53.537c-5.677-9.415-15.87-15.172-26.865-15.172c-10.995,0-21.188,5.756-26.865,15.172 L4.506,426.066c-5.842,9.689-6.015,21.774-0.451,31.625c5.564,9.852,16.001,15.944,27.315,15.944h449.259 c11.314,0,21.751-6.093,27.315-15.944C513.508,447.839,513.336,435.755,507.494,426.066z M256.167,167.227 c12.901,0,23.817,7.278,23.817,20.178c0,39.363-4.631,95.929-4.631,135.292c0,10.255-11.247,14.554-19.186,14.554 c-10.584,0-19.516-4.3-19.516-14.554c0-39.363-4.63-95.929-4.63-135.292C232.021,174.505,242.605,167.227,256.167,167.227z M256.498,411.018c-14.554,0-25.471-11.908-25.471-25.47c0-13.893,10.916-25.47,25.471-25.47c13.562,0,25.14,11.577,25.14,25.47 C281.638,399.11,270.06,411.018,256.498,411.018z"/>
	</g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>';
	}

	public static function handleScript($eid, $form_pid, $pid) {
		if($form_pid == $pid) {

			$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    			" left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));

			?>
			<script type="text/javascript">
				EncounterDateArray=new Array;
			    CalendarCategoryArray=new Array;
			    EncounterIdArray=new Array;
			    Count=0;
		        <?php
		        if (sqlNumRows($result4)>0) {
		            while ($rowresult4 = sqlFetchArray($result4)) {
		        ?>
		        EncounterIdArray[Count]='<?php echo attr($rowresult4['encounter']); ?>';
			    EncounterDateArray[Count]='<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>';
			    CalendarCategoryArray[Count]='<?php echo attr(xl_appt_category($rowresult4['pc_catname'])); ?>';
			            Count++;
			    <?php
			        }
			    }
			    ?>

			    // Get the left_nav window, and the name of its sibling (top or bottom) frame that this form is in.
			    // This works no matter how deeply we are nested.
			    var my_left_nav = top.left_nav;
			    var w = window;
			    for (; w.parent != top; w = w.parent);
			    var my_win_name = w.name;
			    my_left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
			</script>
			<?php
		}
	}

	public static function prepareEncounterReportListData($res) {
	    $tmpPreparedData = array();
	    while ($result1 = sqlFetchArray($res)) {
	        if(isset($result1['encounter'])) {
	            if(!isset($tmpPreparedData['e'.$result1['encounter']])) {
	                $tmpPreparedData['e'.$result1['encounter']] = array();
	            }

	            if ($result1['form_name'] == "New Patient Encounter") {
	                $tmpPreparedData['e'.$result1['encounter']]['main'][] = $result1;
	            } else {
	                $tmpPreparedData['e'.$result1['encounter']]['other'][] = $result1;
	            }
	        }
	    }

	    $preparedResultData = array();
	    foreach ($tmpPreparedData as $tmpkey => $tmpItem) {
	        $mainItem = isset($tmpItem['main']) ? $tmpItem['main'] : array();
	        $otherItem = isset($tmpItem['other']) ? $tmpItem['other'] : array();

	        $mergeItem = array_merge($mainItem,$otherItem);

	        if(is_array($mergeItem)) {
	            $preparedResultData = array_merge($preparedResultData, $mergeItem);
	        }
	    }

	    return $preparedResultData;
	}

	/* find patient data by email */
	public static function getPatientEmail($email = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0") {
	    $col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
	    $sqlBindArray = array();
	    $where = "$col LIKE ? OR CONCAT(',',secondary_email,',') LIKE '%,$email%,'";
	    array_push($sqlBindArray, $email."%");
	    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
	    if ($limit != "all") {
	        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
	    }

	    $rez = sqlStatement($sql, $sqlBindArray);
	    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
	        $returnval[$iter]=$row;
	    }

	    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
	    return $returnval;
	}

	//(CHEMED) Search by phone number
	public static function getPatientPhones($phone = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0") {
	    $phone = preg_replace("/[[:punct:]]/", "", $phone);
	    $phone_number = preg_replace('/^\+?1|\|1|\D/', '', ($phone));


	    $sqlBindArray = array();
	    $where = "REPLACE(REPLACE(phone_home, '-', ''), ' ', '') REGEXP ? ";
	    array_push($sqlBindArray, $phone);
	    
	    $where .= "OR TRIM(LEADING '1' FROM replace(replace(replace(replace(replace(replace(phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+','')) LIKE ? ";
	    array_push($sqlBindArray, $phone_number."%");

	    $where .= "OR CONCAT(',',replace(replace(replace(replace(replace(replace(secondary_phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+',''),',') LIKE ? ";
	    array_push($sqlBindArray, "%,1".$phone_number."%,");

	    $where .= "OR CONCAT(',',replace(replace(replace(replace(replace(replace(secondary_phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+',''),',') LIKE ? ";
	    array_push($sqlBindArray, "%,".$phone_number."%,");
	    

	    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
	    if ($limit != "all") {
	        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
	    }

	    $rez = sqlStatement($sql, $sqlBindArray);
	    for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
	        $returnval[$iter]=$row;
	    }

	    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
	    return $returnval;
	}

	public static function getMultiTextInputElement($frow, $field_value = '', $rmBtn = false) {
	    global $edit_options, $lbfchange;

	    $field_id = $frow['field_id'];
	    $list_id  = $frow['list_id'];
	    $field_id_esc = text($field_id);
	    $smallform = isset($frow['smallform']) ? $frow['smallform'] : "";
	    $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');
	    // Support using the description as a placeholder
	    $placeholder = (isOption($edit_options, 'DAP') === true) ? " placeholder='{$description}' " : '';
	    $btnSize = ($smallform) ? "btn-sm" : "";

	    $tmp = $lbfchange;
	    if (isOption($edit_options, 'C') !== false) {
	        $tmp .= "capitalizeMe(this);";
	    } elseif (isOption($edit_options, 'U') !== false) {
	        $tmp .= "this.value = this.value.toUpperCase();";
	    }

	    /*
	    Author: Hardik Khatri
	    Description: Email Verification changes
	    */
	    /*if (isOption($edit_options, 'EMV') !== false) {
	        global $pid;
	        $pid = ($frow['blank_form'] ?? null) ? 0 : $pid;
	        $emvStatus = EmailVerificationLib::emailVerificationData($pid, $field_id_esc, $field_value);
	        $smallform .= " emv-form-control";
	        echo "<div class='emv-input-group-container' data-initemail='{$currescaped}' data-initstatus='{$emvStatus}' data-id='form_{$field_id_esc}'><div class='input-group'>";
	    }*/

	    /*
	    Author: Hardik Khatri
	    Description: Mask phone number value.
	    */
	    if (isOption($frow['edit_options'], 'MP') !== false) {
	        $smallform .= " maskPhone";
	    }

	    /*
	    Author: Hardik Khatri
	    Description: Validate phone number.
	    */
	    if (isOption($frow['edit_options'], 'MPV') !== false) {
	        $mpValidation = " data-validate='validatePhoneNumber;'";
	    }

	    $tmpOnChange = !empty($tmp) ? " onchange='$tmp'" : "";

	    $mIRemoveBtn = "<button type='button' data-id='$field_Id' class='btn btn-secondary $btnSize mb-1 ' onclick='removeMoreInput(this)'><i class='fa fa-times' aria-hidden='true'></i></button>";

	    $mIInputEle = "<input type='text'" . 
	                " class='form-control mti-form-control {$smallform}' " .
	                " data-id='$field_id_esc'" . 
	                " data-title='".$frow['title']."'" .  
	                " size='{$fldlength}'" . 
	                " {$string_maxlength}" . 
	                " {$placeholder}" .
	                " title='{$description}'" .
	                " " . $mpValidation . 
	                " " . $tmpOnChange . 
	                " value='" . trim($field_value) . "'" .
	                "/>";

	    /*
	    Author: Hardik Khatri
	    Description: Email Verification changes
	    */
	    /*if (isOption($edit_options, 'EMV') !== false) {
	        if($emvStatus === 1) {
	            $statusElement = "<i class='fa fa-check-circle email-verification-icon-successful' aria-hidden='true'></i>";
	        } else {
	            $statusElement = "<i class='fa fa-times-circle email-verification-icon-failed' aria-hidden='true'></i>";
	        }

	        echo "<div class='input-group-append'><input type='hidden' name='form_{$field_id_esc}_hidden_verification_status' value='{$vStatusFlag}'' id='form_{$field_id_esc}_hidden_verification_status' class='hidden_verification_status' /><button type='button' id='form_{$field_id_esc}_btn_verify_email' class='btn btn-primary btn-sm btn_verify_email mb-1'>Verify</button></div>";
	        echo "</div><div class='status-icon-container'>{$statusElement}</div></div>";
	    }*/


	    $mICloneEle = "<div class='input-group'>" . $mIInputEle . "<div class='input-group-append'>" . $mIRemoveBtn . "</div></div>";
	    $mIElement = "<div class='mti-itemcontainer'>" . $mIInputEle . "</div>";

	    if($rmBtn === true) { $mIElement = $mICloneEle; }

	    return $mIElement;
	}
}
