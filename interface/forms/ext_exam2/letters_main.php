<?php 
/** **************************************************************************
 *	EXT_EXAM1/REFERAL.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package mdts
 *  @subpackage letters
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *  @uses generate.inc.php
 * 
 *************************************************************************** */

if (!function_exists('letters_main')) {
	
	// this function is only called when the form is 'approved'
	function letters_main($id, $create=false) {
		if (!$id) die("FATAL ERROR: no form identifier provided for letter generation...");
	
		require_once ($GLOBALS['srcdir'].'/wmt/wmt.class.php');
		require_once ($GLOBALS['srcdir'].'/wmt/wmt.include.php');
		require_once($GLOBALS['srcdir'].'/classes/Document.class.php');
		require_once ($GLOBALS['srcdir'].'/wmt/letters/LetterWriter.php');
		require_once ($GLOBALS['srcdir'].'/wmt/letters/ProgressWriter.php');
		
		$form_data = new wmtForm('ext_exam2',$id);
		$form_id = $form_data->id;
		if (!$form_id) die("<br/>FATAL ERROR: failed to retrieve form record for letter generation...");
		
		$pat_data = wmtPatient::getPidPatient($form_data->pid);
		$pid = $pat_data->pid;
		if (!$pid) die("<br/>FATAL ERROR: failed to retrieve patient record for letter generation...");
		
		$enc_data = wmtEncounter::getEncounter($form_data->encounter);
		$encounter = $enc_data->encounter;
		if (!$encounter) die("<br/>FATAL ERROR: failed to retrieve encounter record for letter generation...");
		$appt_type = $enc_data->pc_catid;
		
		$required = false;
		$item = sqlQuery("SELECT * FROM list_options WHERE list_id = 'Referral_Letters' AND option_id = ?",array($appt_type));
		if ($item['title'] == 'HistoryPhysical' || $item['title'] == 'ProgressNotes') $required = $item['title']; // needed for this form
		if (! $required) return; // no letter required for this appt type
				
		$category = sqlQuery("SELECT * FROM list_options WHERE list_id = 'Referral_Categories' AND title = ?",array($item['title']));
		if (!$category['title']) die("FATAL ERROR: No 'Referral Category' associated with letter type '".$item['title']."'!!");
		define('CATEGORY_ID', $category['option_id']); // repository
		
		// -------------------------------------------------------------------------------------------
		// storage for letter data
		// -------------------------------------------------------------------------------------------
		$merge_data = new stdClass();
		$merge_data->pid = $pat_data->pid;
	
		// -------------------------------------------------------------------------------------------
		// retrieve practice physician list
		// -------------------------------------------------------------------------------------------
		$merge_data->physicians = '';
		$result = sqlStatement("SELECT * FROM list_options WHERE list_id = 'Referral_Physicians' ORDER BY seq");
		while ($item = sqlFetchArray($result)) $merge_data->physicians .= $item['title']."<br/>";
		
		// -------------------------------------------------------------------------------------------
		// collect the patient data
		// -------------------------------------------------------------------------------------------
		$merge_data->pat_lname = $pat_data->lname;
		$merge_data->pat_fname = $pat_data->fname;
		$merge_data->pat_mname = $pat_data->mname;
		$merge_data->pat_dob = $pat_data->DOB;
		$merge_data->pat_sex = $pat_data->sex;
		$merge_data->pat_id = $pat_data->pid;
		
		// -------------------------------------------------------------------------------------------
		// retrieve encounter
		// -------------------------------------------------------------------------------------------
		$merge_data->encounter = $encounter;
		$merge_data->date = $enc_data->date;
		$merge_data->reason = $enc_data->reason;
		$merge_data->facility = $enc_data->facility_id;
		$merge_data->category = $enc_data->pc_catid;
		$merge_data->provider = DocIdLook($enc_data->provider_id);
		$merge_data->referrer = DocIdLook($pat_data->ref_providerID);
		
		// -------------------------------------------------------------------------------------------
		// retrieve diagnoses (narrative)
		// -------------------------------------------------------------------------------------------
		$merge_data->approved_by = $form_data->approved_by;
		$merge_data->approved_date = $form_data->approved_dt;
		$merge_data->diagnoses = $form_data->ee1_cc; // special 
		$merge_data->hpi = $form_data->ee1_hpi;
		
		// -------------------------------------------------------------------------------------------
		// retrieve allergies (list)
		// -------------------------------------------------------------------------------------------
		$merge_data->allergy = false;
		$allergies = GetList($pid, 'allergy');
		if (is_array($allergies)) {
			foreach ($allergies AS $allergy) {
				$merge_data->allergy[] = $allergy['title'];
			}
		}
		$merge_data->allergy_note = $form_data->ee1_allergy_nt;
		
		// -------------------------------------------------------------------------------------------
		// retrieve medical history (list)
		// -------------------------------------------------------------------------------------------
		$merge_data->history = false; 
		$histories = GetList($pid, 'wmt_med_history');
		if (is_array($histories)) {
			foreach ($histories AS $history) {
				$merge_data->history[] = $history['title'];
			}
		}
		$merge_data->history_note = $form_data->ee1_pmh_nt;
		
		// -------------------------------------------------------------------------------------------
		// retrieve medications (list)
		// -------------------------------------------------------------------------------------------
		$merge_data->meds = false;
		$meds = getActiveRxByPatient($pid);
		if (is_array($meds)) {
			foreach($meds AS $drug) {
				$dose = $drug['dosage'];
		
				$size = trim($drug['size']);
				$size .= trim(ListLook($drug['unit'],'drug_units'));
				
				$sig = '';
				$sig1 = trim(ListLook($drug['route'],'drug_route'));
				if (!empty($sig1)) $sig .= ' by ' . $sig1;
				$sig2 = trim(ListLook($drug['interval'],'drug_interval'));
				if (!empty($sig2)) $sig .= ' ' . $sig2;
				
				if ($size || $sig) {
					if ($dose) $dose .= " ";
					if ($size) $dose .= $size;
					if ($sig) $dose .= $sig;
				}
				if ($dose) $dose = " (".$dose.")";
				$merge_data->meds[] = $drug['drug'] . $dose;
			}
		}	
		$merge_data->meds_note = $form_data->ee1_med_nt;
		
		// -------------------------------------------------------------------------------------------
		// retrieve social history (narrative)
		// -------------------------------------------------------------------------------------------
		$text = '';
		/* THEY DO NOT WANT THIS DATA
		if ($form_data->ee1_smk_desc) {
			$text .= ListLook($form_data->ee1_smk_desc, 'smoking_status');
			if ($form_data->ee1_smk_desc != '9') $text .= " reported by patient";
			$text .= ". ";
		}
		if ($form_data->ee1_alcohol_long) {
			switch($form_data->ee1_alcohol_long) {
				case 'neveralcohol':
					$text .= "Patient has never consumed alcohol. ";
					break;
				case 'currentalcohol':
					$text .= "Patient currently drinks alcohol";
					if ($form_data->ee1_alcohol_much) $text .= " (".strtolower($form_data->ee1_alcohol_much).")";
					$text .= ". ";
					break;
				case 'quitalcohol':
					$text .= "Patient reports quiting consumption of alcohol";
					if ($form_data->ee1_alcohol_dt) $text .= " (".strtolower($form_data->ee1_alcohol_dt).")";
					$text .= ". ";
					break;
			}
		}
		if ($form_data->ee1_drug_use) {
			switch($form_data->ee1_drug_use) {
				case 'neverrecreational_drugs':
					$text .= "Patient has never used recreational drugs. ";
					break;
				case 'currentrecreational_drugs':
					$text .= "Patient currently uses recreational drugs. ";
					break;
				case 'quitrecreational_drugs':
					$text .= "Patient reports quiting recreational drug use";
					if ($form_data->ee1_drug_dt) $text .= " (".strtolower($form_data->ee1_drug_dt).")";
					$text .= ". ";
					break;
			}
		}
		*/
		
		if ($form_data->ee1_social_nt) $text = ($text)? $text." ".$form_data->ee1_social_nt : $form_data->ee1_social_nt;
		$merge_data->social = $text; 
		
		// -------------------------------------------------------------------------------------------
		// retrieve family history (narrative)
		// -------------------------------------------------------------------------------------------
		$text = '';
		
		$fh = false;
		$sql = "SELECT li.id, injury_part as fh_who, injury_grade as fh_deceased, ".
					"reaction as fh_age, destination as fh_age_dead, ".
					"injury_type as fh_type, comments as fh_nt, lo.title as fh_title FROM lists li ".
					"LEFT JOIN list_options lo ON li.injury_type = lo.option_id AND lo.list_id = 'Family_History_Problems' ".
					"WHERE pid = ? AND type='wmt_family_history' ORDER BY fh_who";
		$result = sqlStatementNoLog($sql,array($pid));
		while ($record = sqlFetchArray($result)) $fh_list[] = $record;
		
		$fh_text = '';
		/* THEY DO NOT WANT THIS DATA
		if (count($fh_list) > 0) {
			$i = 0;
			do {
				$family = $fh_list[$i];
				if ($fh_text) $fh_text .= " ";
				$fh_text .= ListLook($family['fh_who'],'Family_Relationships');
				if ($family['fh_deceased'] == 'YES') {
					$fh_text .= " died";
					if ($family['fh_age_dead']) $fh_text .= " at age ".$family['fh_age_dead'];
					if ($family['fh_title']) $fh_text .= " with history of ".$family['fh_title'];
		
					// grab any additional factors
					$i++; // check next record
					while ($fh_list[$i]['fh_who'] == $family['fh_who']) {
						$family = $fh_list[$i++];
						if ($family['fh_title']) $fh_text .= " and ".$family['fh_title'];
					}
					$fh_text .= ".";
				}
				else {
					if ($family['fh_age']) $fh_text .= " is ".$family['fh_age']." years old";
					if ($family['fh_title']) $fh_text .= " has history of ".$family['fh_title'];
		
					// grab any additional factors
					$i++; // check next record
					while ($i < count($fh_list) && $fh_list[$i]['fh_who'] == $family['fh_who']) {
						$family = $fh_list[$i++];
						if ($family['fh_title']) $fh_text .= " and ".$family['fh_title'];
					}
					$fh_text .= ".";
				}
			} while ($i < count($fh_list));
		}
		if ($fh_text) $text = $fh_text." ";
		
		$yes_text = '';
		$fh_yes = explode('|', $form_data->ee1_fh_extra_yes);
		if (is_array($fh_yes)) {
			foreach($fh_yes AS $opt) {
				$opt = str_ireplace('tmp_fh_rs_', '', $opt);
				$opt_text = ListLook($opt, 'EE1_Family_Options');
				if ($opt_text) {
					if ($yes_text) $yes_text .= ", ";
					$yes_text .= $opt_text;
				}
			}
		}
		if ($yes_text) $text .= "Family history of ".$yes_text.". ";
		
		$no_text = '';
		$fh_no = explode('|', $form_data->ee1_fh_extra_no);
		if (is_array($fh_no)) {
			foreach($fh_no AS $opt) {
				$opt = str_ireplace('tmp_fh_rs_', '', $opt);
				$opt_text = ListLook($opt, 'EE1_Family_Options');
				if ($opt_text) {
					if ($no_text) $no_text .= ", ";
					$no_text .= $opt_text;
				}
			}
		}
		if ($no_text) $text .= "No known family history of ".$no_text.". ";
		*/ 

		if ($form_data->ee1_fh_notes) $text .= $form_data->ee1_fh_notes;
		$merge_data->family = $text;
		
		
		// -------------------------------------------------------------------------------------------
		// retrieve ROS (narrative)
		// -------------------------------------------------------------------------------------------
		$text = '';
		
		$opt = array(); // item definitions
		$res = sqlStatement("SELECT * FROM list_options WHERE list_id = 'EE1_ROS_Options' ORDER BY seq");
		while ($record = sqlFetchArray($res)) $opt[] = $record;
		
		$rs= sqlQuery("SELECT * FROM form_ext_ros WHERE ee1_link_id='$form_id' AND ee1_link_name='form_ext_exam2'");
		
		$hpi = '';
		$no_text = '';
		$yes_text = '';
		$other_text = '';
		$section = '';
		foreach ($opt AS $option) {
			if ($option['notes'] == 'Section') {
		
				// section break
				if ($no_text || $yes_text || $other_text || $hpi) {
					$text .= '<span style="font-weight:bold">'.$section.$hpi.'</span><ul type="none">';
					if ($no_text) $text .= '<li>Negative for '.$no_text.'.</li>';
					if ($yes_text) $text .= '<li>Patient confirms '.$yes_text.'.</li>';
					if ($other_text) $text .= '<li>'.$other_text.'</li>';
					$text .= "</ul>\n";
				}
		
				// new section
				$hpi = '';
				$no_text = '';
				$yes_text = '';
				$other_text = '';
				$section = $option['title'];
				if ($rs['ee1_rs_'.$option['option_id']] == 1) $hpi = ':&nbsp;&nbsp;Refer to HPI for Details';
			}
		
			// process information
			$chk = strtolower($rs['ee1_rs_'.$option['option_id']]);
			if ($chk == 'n') {
				if ($no_text) $no_text .= ", ";
				$no_text .= $option['title'];
				if (!empty($rs['ee1_rs_'.$option['option_id'].'_nt'])) $no_text .= " (".$rs['ee1_rs_'.$option['option_id'].'_nt'].")";
			}
			elseif ($chk == 'y') {
				if ($yes_text) $yes_text .= ", ";
				$yes_text .= $option['title'];
				if (!empty($rs['ee1_rs_'.$option['option_id'].'_nt'])) $yes_text .= " (".$rs['ee1_rs_'.$option['option_id'].'_nt'].")";
			}
			elseif (!empty($rs['ee1_rs_'.$option['option_id'].'_nt'])) {
				if ($other_text) $other_text .= " ";
				$other_text .= ucfirst($rs['ee1_rs_'.$option['option_id'].'_nt']).".";
			}
		}
		
		$nt = trim($rs['ee1_rs_nt']);
		if (!empty($nt)) {
			if ($text) $text .= "<br/>";
			$text .= nl2br($nt);
		}
		$merge_data->ros = $text;
		
		// -------------------------------------------------------------------------------------------
		// diagnostic studies (narrative) -- NEEDS UPDATING WITH NEW FIELD
		// -------------------------------------------------------------------------------------------
		$text = '';
		$merge_data->studies = false;
		
		$nt = trim($form_data->ee1_diagnostics_nt);
		if (!empty($nt)) {
			if ($text) $text .= "<br/>";
			$text .= $nt;
		}
		$merge_data->studies = $text;
		
		// -------------------------------------------------------------------------------------------
		// retrieve exam (narrative) -- use Rich's original generation
		// -------------------------------------------------------------------------------------------
		$text = '';
		$merge_data->exam = false;
		$dt = sqlQuery("SELECT * FROM form_ext_exam2 WHERE id = '$form_id'");
		
		ob_start(); // buffer all output
		require("letters_exam.php");
		$text = ob_get_clean();
		
		$nt = trim($form_data->ee1_ge_dictate);
		if (!empty($nt)) {
			if ($text) $text .= "<br/>";
			$text .= nl2br($nt);
		}
	
		$merge_data->exam = $text;
		
		// -------------------------------------------------------------------------------------------
		// retrieve diagnoses and assessment (narrative)
		// -------------------------------------------------------------------------------------------
		$merge_data->assessment = false;
		if ($form_data->ee1_assess) $merge_data->assessment = $form_data->ee1_assess;
		
		// -------------------------------------------------------------------------------------------
		//   Generate and store the result pdf document
		// -------------------------------------------------------------------------------------------
	
		// validate the respository directory
		$repository = $GLOBALS['oer_config']['documents']['repository'];
		$file_path = $repository . preg_replace("/[^A-Za-z0-9]/","_",$pid) . "/";
		if (!file_exists($file_path)) {
			if (!mkdir($file_path,0700)) {
				throw new Exception("The system was unable to create the directory for this result, '" . $file_path . "'.\n");
			}
		}
		
		// generate observation results documents (if new then LETTER else PROGRESS)
		if ($required == 'HistoryPhysical') $document = makeLetter($merge_data);
		if ($required == 'ProgressNotes') $document = makeProgress($merge_data);
		
		// create file name
		$unique = date('y').str_pad(date('z'),3,0,STR_PAD_LEFT); // 13031 (year + day of year)
		$doc_name = $pid."_".$required;
		
		$docnum = 1;
		$doc_file = $doc_name."_".$unique.".pdf";
		while (file_exists($file_path.$doc_file)) { // don't overlay duplicate file names
			$doc_name = $pid."_".$required."_".$docnum;
			$doc_file = $pid."_".$required."_".$unique."_".$docnum++.".pdf";
		}
		
		// write the new file to the repository
		if (($fp = fopen($file_path.$doc_file, "w")) == false) {
			throw new Exception('Could not create local file ('.$file_path.$doc_file.')');
		}
		fwrite($fp, $document);
		fclose($fp);
		
		// register the new document
		$d = new Document();
		$d->name = $doc_name;
		$d->storagemethod = 0; // only hard disk sorage supported
		$d->url = "file://" .$file_path.$doc_file;
		$d->mimetype = "application/pdf";
		$d->size = filesize($file_path.$doc_file);
		$d->owner = '';
		$d->hash = sha1_file( $file_path.$doc_file );
		$d->type = $d->type_array['file_url'];
		$d->set_foreign_id($pid);
		$d->persist();
		$d->populate();
		
		// save document id
		$doc_id = $d->get_id();
		
		// update cross reference
		if (CATEGORY_ID && $doc_id) {
			sqlInsert("REPLACE INTO categories_to_documents SET category_id = ?, document_id = ?",array(CATEGORY_ID, $doc_id) );
		}
		
		// all done so store the doc id in the form record
		sqlStatement("UPDATE form_ext_exam2 SET referral_printed = 0, referral_docid = ? WHERE id = ?",array($doc_id,$form_data->id));
		
		// we are finished
		return $file_path.$doc_file;
	}
}
