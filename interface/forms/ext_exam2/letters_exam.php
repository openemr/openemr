<?php
/** *********************************************************************************
 *	LETTERS/EXAM.INC.PHP
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
 *  @author Ron Criswell <info@keyfocusmedia.com>
 *  @see interface/forms/ext_exam2/common.php
 *  
 *  This program was extracted from the original ext_exam2 code written by
 *  Rich Genandt for the ext_exam2 form and modified for use with the letter
 *  generation application. The original code output directly to the screen.
 *  This implementation buffers the output so that it can be included in the
 *  letter.
 *
 ************************************************************************************* */
$GLOBALS['chp_printed'] = true;
$GLOBALS['hdr_printed'] = false;
$GLOBALS['sub_printed'] = false;
$GLOBALS['list_printed'] = false;

// -------------------------------------------------------------------------------
//   Modified functions used to generate output
// -------------------------------------------------------------------------------
if (! function_exists('RL1_HeaderGE')) {
	function RL1_HeaderGE($title, $printed, $bar=false) {
	  if($printed) { return true; }
	  echo '<span style="font-weight:bold">'.$title.'</span>';
	  echo "\n";
	  return true;
	}
} // end exists
	
if (! function_exists('RL1_SubSectionGE')) {
	function RL1_SubSectionGE($title, $printed) {
	  if($printed) { return true; }
	  if ($GLOBALS['list_printed']) 
	  	echo "</ul>\n";
	  	else echo "<br/>\n";
	  $GLOBALS['list_printed'] = false;
	  echo '<span style="font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;'.$title.'</span>';
	  echo "\n";
	  return true;
	}
} // end exists
	
if (! function_exists('RL1_ListGE')) {
	function RL1_ListGE($printed) {
	  if($printed) { return true; }
	  echo "<ul type=\"none\">\n";
	  return true;
	}
} // end exists
	
if (! function_exists('RL1_PrintGE_YN')) {
	function RL1_PrintGE_YN($type='',$yn='',$note='',$section='',$subsection='') {
		$note=trim($note);
		if(!empty($yn) || !empty($note) || !empty($type)) {
			$yn=ListLook($yn, 'EE1_YesNo');
			$GLOBALS['hdr_printed']=RL1_HeaderGE($section, $GLOBALS['hdr_printed']);
			if(!empty($subsection)) {
				$GLOBALS['sub_printed']=RL1_SubSectionGE($subsection, $GLOBALS['sub_printed']);
			}
			$GLOBALS['list_printed']=RL1_ListGE($GLOBALS['list_printed']);
			echo '<li>';
			if ($yn == 'No') echo $yn.' ';
			echo $type;
			if ($note) echo ' ('.$note.').';
			echo "</li>\n";
		}
	}
} // end exists
	
if (! function_exists('RL1_PrintGE')) {
	function RL1_PrintGE($type='',$chc='',$note='',$section='',$subsection='') {
	$note=trim($note);
	if(!empty($chc) || !empty($note) || !empty($type)) {
			$GLOBALS['hdr_printed']=RL1_HeaderGE($section, $GLOBALS['hdr_printed']);
			if(!empty($subsection)) {
				$GLOBALS['sub_printed']=RL1_SubSectionGE($subsection, $GLOBALS['sub_printed']);
			}
			$GLOBALS['list_printed']=RL1_ListGE($GLOBALS['list_printed']);
			echo '<li>';
			echo $type;
			if ($chc) echo ' '.$chc;
			if ($note) echo ' ('.$note.').';
			echo "</li>\n";
	  }
	}
} // end exists
	
if (! function_exists('RL1_PrintCompoundGE')) {
	function RL1_PrintCompoundGE($note='', $section='', $subsection='') {
		$note=trim($note);
		if(!empty($note)) {
			$GLOBALS['hdr_printed']=RL1_HeaderGE($section, $GLOBALS['hdr_printed']);
			if(!empty($subsection)) {
				$GLOBALS['sub_printed']=RL1_SubSectionGE($subsection, $GLOBALS['sub_printed']);
			}
			$GLOBALS['list_printed']=RL1_ListGE($GLOBALS['list_printed']);
			echo '<li>'.$note.'</li>';
			echo "\n";
		}
	}
} // end exists
	
if (! function_exists('RL1_PrintNote')) {
	function RL1_PrintNote($note='', $chapter='', $section='', $sub='') {
		$note=trim($note);
		if(!empty($note)) {
		$GLOBALS['hdr_printed']=RL1_HeaderGE($section, $GLOBALS['hdr_printed']);
		if(!empty($sub)) {
			$GLOBALS['sub_printed']=RL1_SubSectionGE($sub, $GLOBALS['sub_printed']);
		}
		$GLOBALS['list_printed']=RL1_ListGE($GLOBALS['list_printed']);
		echo '<li>'.$note.'</li>';
		}
	}
} // end exists
	
if (! function_exists('RL1_AppendItem')) {
	function RL1_AppendItem($existing='',$new='',$prefix='') {
		if($new=='') { return($existing); }
		$existing=trim($existing);
		if(!empty($existing)) {
			$existing.=', ';
		} 
		else {
			$existing = $prefix;
		}
		$existing.=$new;
		return($existing);
	}
} // end exists

// -------------------------------------------------------------------------------
//   Output generation for EE1 Vitals
// -------------------------------------------------------------------------------
$nt='';
if ($dt['ee1_ht'] || $dt['ee1_wt'] || $dt['ee1_hr'] || $dt['ee1_resp'] || $dt['ee1_temp']) {
	echo '<br/><br/><table style="width:100%;border:1px solid black"><tr>';
	if ($dt['ee1_temp'] && $dt['ee1_temp']!='0.00') echo "<td>TEMP: ".sprintf('%0.1f', $dt['ee1_temp'])."</td>\n";
	if ($dt['ee1_hr']) echo "<td>PULSE: ".intval($dt['ee1_hr'])."</td>\n";
	if ($dt['ee1_resp'] && $dt['ee1_resp']!='0.00') echo "<td>RESP: ".intval($dt['ee1_resp'])."</td>\n";
	if ($dt['ee1_bps'] && $dt['ee1_bpd']) echo "<td>BP: ".intval($dt['ee1_bpd'])."/".intval($dt['ee1_bps'])."</td>\n";
	if ($dt['ee1_ht']) echo "<td>HT: ".intval($dt['ee1_ht'])."</td>\n";
	if ($dt['ee1_wt']) echo "<td>WT: ".intval($dt['ee1_wt'])."#</td>\n";
	echo '</tr></table><br/><br/>';
}

// -------------------------------------------------------------------------------
//   Output generation for EE1 General exam
// -------------------------------------------------------------------------------

$nt='';

// First Pass, set to '0' is for anything with comments only.
// Second pass will print no choices, then third pass no choices with comments
// fourth pass is yes choices, then last (fifth) pass is yes with comments.
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc='';
	$nt=trim($dt{'ee1_ge_gen_norm_nt'});
	$chk=$dt{'ee1_ge_gen_norm'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Normal Habitus');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Normal Habitus',$chc,$nt,'General:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','General:');
		}
	}
	$nt=trim($dt{'ee1_ge_gen_dev_nt'});
	$chk=$dt{'ee1_ge_gen_dev'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Well Developed');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Well Developed',$chc,$nt,'General:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','General:');
		}
	}
	$nt=trim($dt{'ee1_ge_gen_groom_nt'});
	$chk=$dt{'ee1_ge_gen_groom'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Well Groomed');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Well Groomed',$chc,$nt,'General:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','General:');
		}
	}
	$nt=trim($dt{'ee1_ge_gen_dis_nt'});
	$chk=$dt{'ee1_ge_gen_dis'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'No Acute Distress');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('No Acute Distress',$chc,$nt,'General:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','General:');
		}
	}
	$nt=trim($dt{'ee1_ge_gen_jaun_nt'});
	$chc=strtolower($dt{'ee1_ge_gen_jaun'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Jaundice'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Jaundice'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Jaundice',$chc,$nt,'General:'); }
	}
	$nt=trim($dt{'ee1_ge_gen_waste_nt'});
	$chc=strtolower($dt{'ee1_ge_gen_waste'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Wasting'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Wasting'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Wasting',$chc,$nt,'General:'); }
	}
	$nt=trim($dt{'ee1_ge_gen_sleep_nt'});
	$chc=strtolower($dt{'ee1_ge_gen_sleep'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=AppendItem($prnt,'Normal Sleep Pattern'); }
		if($cnt == 3 && $chc == 'a') { $prnt=AppendItem($prnt,'Abnormal Sleep Pattern'); }
	} else {
		if($cnt == 0 && $chc == '') {
			RL1_PrintGE('Sleep Pattern:','','','General:');
			RL1_PrintNote($nt,'General Physical Exam','General:');
		}
		if($cnt == 2 && $chc == 'n') {
			$chk=ListLook($dt{'ee1_ge_gen_sleep'},'NormAbnorm');
			RL1_PrintGE('Sleep Pattern',$chk,$nt,'General:');
		}
		if($cnt == 4 && $chc == 'a') {
			$chk=ListLook($dt{'ee1_ge_gen_sleep'},'NormAbnorm');
			RL1_PrintGE('Sleep Pattern',$chk,$nt,'General:');
		}
	}


	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'General:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_gen_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','General:');
	RL1_PrintNote($nt,'General Physical Exam','General:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc='';
	$nt=trim($dt{'ee1_ge_hd_atra_nt'});
	$chk=$dt{'ee1_ge_hd_atra'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Atraumatic');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Atraumatic',$chc,$nt,'Head:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Head:');
		}
	}
	$nt=trim($dt{'ee1_ge_hd_norm_nt'});
	$chk=$dt{'ee1_ge_hd_norm'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Normocephalic');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Normocephalic',$chc,$nt,'Head:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Head:');
		}
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Head:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_hd_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Head:');
	RL1_PrintNote($nt,'General Physical Exam','Head:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk='';
$chc=ListLook($dt{'ee1_ge_eye_pupil'},'EE1_Pupil');
if(!empty($chc) || !empty($dt{'ee1_ge_eye_pupil_nt'})) { $chk='Pupils'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_eye_pupil_nt'},'Eyes:');
// Fundiscopic section can be combined
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_eye_hem_nt'});
	$chc=$dt{'ee1_ge_eye_hem'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Hemorrahge'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Hemorrhage'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Hemorrhage',$chc,$nt,'Eyes:','Fundiscopic'); }
	}
	$nt=trim($dt{'ee1_ge_eye_exu_nt'});
	$chc=$dt{'ee1_ge_eye_exu'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Exudate'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Exudate'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Exudate',$chc,$nt,'Eyes:','Fundiscopic'); }
	}
	$nt=trim($dt{'ee1_ge_eye_av_nt'});
	$chc=$dt{'ee1_ge_eye_av'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No AV Nicking'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'AV Nicking'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('AV Nicking',$chc,$nt,'Eyes:','Fundiscopic'); }
	}
	$nt=trim($dt{'ee1_ge_eye_pap_nt'});
	$chc=$dt{'ee1_ge_eye_pap'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Papilledema'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Papilledema'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Papilledema',$chc,$nt,'Eyes:','Fundiscopic'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Eyes:','Fundiscopic'); }
	$cnt++;
}

$GLOBALS['sub_printed']=false;
$chc=ListLook($dt{'ee1_ge_eyer_norm'},'NormAbnorm');
if(!empty($chc) || !empty($dt{'ee1_ge_eyer_norm'})) { $chk='No Abnormalities'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_eyer_norm_nt'},'Eyes:','Right Eye');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_eyer_exo_nt'});
	$chc=$dt{'ee1_ge_eyer_exo'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Exophthalmos'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Exophthalmos'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Exophthalmos',$chc,$nt,'Eyes:','Right Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyer_stare_nt'});
	$chc=$dt{'ee1_ge_eyer_stare'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Stare'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Stare'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Stare',$chc,$nt,'Eyes:','Right Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyer_lag_nt'});
	$chc=$dt{'ee1_ge_eyer_lag'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Lid Lag'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Lid Lag'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Lid Lag',$chc,$nt,'Eyes:','Right Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyer_scleral_nt'});
	$chk=$dt{'ee1_ge_eyer_scleral'};
	$chc='';
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'No Scleral Injection');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('No Scleral Injection',$chc,$nt,'Eyes:','Right Eye');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Eyes:','Right Eye');
		}
	}
	$nt=trim($dt{'ee1_ge_eyer_eomi_nt'});
	$chc=$dt{'ee1_ge_eyer_eomi'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No EOMI'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'EOMI'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('EOMI',$chc,$nt,'Eyes:','Right Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyer_perrl_nt'});
	$chc=$dt{'ee1_ge_eyer_perrl'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No PERRL'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'PERRL'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('PERRL',$chc,$nt,'Eyes:','Right Eye'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Eyes:','Right Eye'); }
	$cnt++;
}

$GLOBALS['sub_printed']=false;
$chc=ListLook($dt{'ee1_ge_eyel_norm'},'NormAbnorm');
if(!empty($chc) || !empty($dt{'ee1_ge_eyel_norm'})) { $chk='No Abnormalities'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_eyel_norm_nt'},'Eyes:','Left Eye');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_eyel_exo_nt'});
	$chc=$dt{'ee1_ge_eyel_exo'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Exophthalmos'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Exophthalmos'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Exophthalmos',$chc,$nt,'Eyes:','Left Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyel_stare_nt'});
	$chc=$dt{'ee1_ge_eyel_stare'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Stare'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Stare'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Stare',$chc,$nt,'Eyes:','Left Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyel_lag_nt'});
	$chc=$dt{'ee1_ge_eyel_lag'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Lid Lag'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Lid Lag'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Lid Lag',$chc,$nt,'Eyes:','Left Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyel_scleral_nt'});
	$chk=$dt{'ee1_ge_eyel_scleral'};
	$chc='';
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'No Scleral Injection');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('No Scleral Injection',$chc,$nt,'Eyes:','Left Eye');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Eyes:','Left Eye');
		}
	}
	$nt=trim($dt{'ee1_ge_eyel_eomi_nt'});
	$chc=$dt{'ee1_ge_eyel_eomi'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No EOMI'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'EOMI'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('EOMI',$chc,$nt,'Eyes:','Left Eye'); }
	}
	$nt=trim($dt{'ee1_ge_eyel_perrl_nt'});
	$chc=$dt{'ee1_ge_eyel_perrl'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No PERRL'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'PERRL'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('PERRL',$chc,$nt,'Eyes:','Left Eye'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Eyes:','Left Eye'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_eye_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Eyes:');
	RL1_PrintNote($nt,'General Physical Exam','Eyes:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk=$chc='';
if(!empty($dt{'ee1_ge_earr_tym_nt'})) { $chk='Tympanic Membrane'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_earr_tym_nt'},'Ears:','Right Ear');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_earr_clear_nt'});
	$chc=$dt{'ee1_ge_earr_clear'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Clear'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Clear'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Clear',$chc,$nt,'Ears:','Right Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earr_perf_nt'});
	$chc=$dt{'ee1_ge_earr_perf'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Perforation'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Perforation'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Perforation',$chc,$nt,'Ears:','Right Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earr_ret_nt'});
	$chc=$dt{'ee1_ge_earr_ret'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Retraction'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Retraction'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Retraction',$chc,$nt,'Ears:','Right Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earr_pus_nt'});
	$chc=$dt{'ee1_ge_earr_pus'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Pus'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Pus'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Pus',$chc,$nt,'Ears:','Right Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earr_ceru_nt'});
	$chc=$dt{'ee1_ge_earr_ceru'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Cerumen'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Cerumen'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Cerumen',$chc,$nt,'Ears:','Right Ear'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Ears:','Right Ear'); }
	$cnt++;
}

$GLOBALS['sub_printed']=false;
$chk=$chc='';
if(!empty($dt{'ee1_ge_earl_tym_nt'})) { $chk='Tympanic Membrane'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_earl_tym_nt'},'Ears:','Left Ear');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_earl_clear_nt'});
	$chc=$dt{'ee1_ge_earl_clear'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Clear'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Clear'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Clear',$chc,$nt,'Ears:','Left Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earl_perf_nt'});
	$chc=$dt{'ee1_ge_earl_perf'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Perforation'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Perforation'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Perforation',$chc,$nt,'Ears:','Left Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earl_ret_nt'});
	$chc=$dt{'ee1_ge_earl_ret'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Retraction'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Retraction'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Retraction',$chc,$nt,'Ears:','Left Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earl_pus_nt'});
	$chc=$dt{'ee1_ge_earl_pus'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Pus'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Pus'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Pus',$chc,$nt,'Ears:','Left Ear'); }
	}
	$nt=trim($dt{'ee1_ge_earl_ceru_nt'});
	$chc=$dt{'ee1_ge_earl_ceru'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Cerumen'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Cerumen'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Cerumen',$chc,$nt,'Ears:','Left Ear'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Ears:','Left Ear'); }
	$cnt++;
}

if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Ears:');
	RL1_PrintNote($nt,'General Physical Exam','Ears:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt='';
	$nt=trim($dt{'ee1_ge_nose_ery_nt'});
	$chc=$dt{'ee1_ge_nose_ery'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Erythema'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Erythema'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Erythema',$chc,$nt,'Nose:','Nasal Mucosa'); }
	}
	$nt=trim($dt{'ee1_ge_nose_swell_nt'});
	$chc=$dt{'ee1_ge_nose_swell'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Swelling'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Swelling'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Swelling',$chc,$nt,'Nose:','Nasal Mucosa'); }
	}
	$nt=trim($dt{'ee1_ge_nose_pall_nt'});
	$chc=$dt{'ee1_ge_nose_pall'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Pallor'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Pallor'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Pallor',$chc,$nt,'Nose:','Nasal Mucosa'); }
	}
	$nt=trim($dt{'ee1_ge_nose_polps_nt'});
	$chc=$dt{'ee1_ge_nose_polps'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Polyps'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Polyps'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Polpys',$chc,$nt,'Nose:','Nasal Mucosa'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Nose:','Nasal Mucosa'); }
	$cnt++;
}
$chc=ListLook($dt{'ee1_ge_nose_sept'},'EE1_Septum');
if(!empty($chc)) { $chk='Septum'; }
EE1_PrintGE($chk,$chc,$dt{'ee1_ge_nose_sept_nt'},'Nose:');
$nt=trim($dt{'ee1_ge_nose_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Nose:');
	RL1_PrintNote($nt,'General Physical Exam','Nose:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chc=$chk='';
if($client_id == 'cffm') {
	$nt1 = trim($dt{'ee1_ge_mouth_moist_nt'});
	$nt2 = trim($dt{'ee1_ge_mouth_les_nt'});
	$nt3 = trim($dt{'ee1_ge_mouth_dent_nt'});
	if($dt{'ee1_ge_mouth_moist'}=='1') { $chk='Moist Mucus Membranes'; }
	if($nt1 || $nt2 || $nt3) {
		RL1_PrintGE_YN($chk,$chc,$nt1,'Mouth:');
		if($dt{'ee1_ge_mouth_les'}=='1') { $chk='Clear of Suspicious Lesions'; }
		RL1_PrintGE_YN($chk,$chc,$nt2,'Mouth:');
		$chk='';
		$chc=ListLook($dt{'ee1_ge_mouth_dent'},'EE1_Denture');
		if($chc) { $chk='Dentition'; }
		RL1_PrintGE($chk,$chc,$nt3,'Mouth:');
	} else {
		if($dt{'ee1_ge_mouth_les'}=='1') { $chk=AppendItem($chk, 'Clear of Suspicious Lesions'); }
		$chc=ListLook($dt{'ee1_ge_mouth_dent'},'EE1_Denture');
		if($chc) { $chk=AppendItem($chk, 'Dentition: '.$chc); }
		RL1_PrintCompoundGE($chk, 'Mouth:');
	}
} else {
	if($dt{'ee1_ge_mouth_moist'}=='1') { $chk='Moist Mucus Membranes'; }
	RL1_PrintGE_YN($chk,$chc,$dt{'ee1_ge_mouth_moist_nt'},'Mouth:');
}
$nt=trim($dt{'ee1_ge_mouth_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Mouth:');
	RL1_PrintNote($nt,'General Physical Exam','Mouth:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$nt=trim($dt{'ee1_ge_thrt_ery_nt'});
	$chk=$dt{'ee1_ge_thrt_ery'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'No Erythema');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('No Erythema',$chc,$nt,'Throat:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Throat:');
		}
	}
	$nt=trim($dt{'ee1_ge_thrt_exu_nt'});
	$chk=$dt{'ee1_ge_thrt_exu'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'No Exudate');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('No Exudate',$chc,$nt,'Throat:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Throat:');
		}
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Throat:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_thrt_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Throat:');
	RL1_PrintNote($nt,'General Physical Exam','Throat:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk=$nt='';
	$chk=$dt{'ee1_ge_nk_sup'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Supple');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Supple',$chc,$nt,'Neck:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Neck:');
		}
	}
	$chc=$dt{'ee1_ge_nk_brit'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Bruits'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Buits'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Bruits',$chc,$nt,'Neck:'); }
	}
	$chc=$dt{'ee1_ge_nk_jvp'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No JVP'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'JVP'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('JVP',$chc,$nt,'Neck:'); }
	}
	$chc=$dt{'ee1_ge_nk_lymph'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Lymphadenopathy'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Lymphadenopathy'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Lymphadenopathy',$chc,$nt,'Neck:'); }
	}
	$chk=$dt{'ee1_ge_nk_trach'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Trachea Midline');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Trachea Midline',$chc,$nt,'Neck:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Neck:');
		}
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Neck:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_nk_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Neck:');
	RL1_PrintNote($nt,'General Physical Exam','Neck:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$nt=trim($dt{'ee1_ge_thy_norm_nt'});
	$chk=$dt{'ee1_ge_thy_norm'};
	if(empty($nt)) {
		if($chk == 1 && $cnt == 1) $prnt=RL1_AppendItem($prnt,'Normal Size');
	} else {
		if($chk == 1 && $cnt == 4) {
			RL1_PrintGE_YN('Normal Size',$chc,$nt,'Thyroid:');
		} else if($cnt == 0) {
			RL1_PrintNote($nt,'General Physical Exam','Thyroid:');
		}
	}
	$chc=$dt{'ee1_ge_thy_nod'};
	$nt=trim($dt{'ee1_ge_thy_nod_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Nodules'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Nodules'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Nodules',$chc,$nt,'Thyroid:'); }
	}
	$chc=$dt{'ee1_ge_thy_brit'};
	$nt=trim($dt{'ee1_ge_thy_brit_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Bruits'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Bruits'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Bruits',$chc,$nt,'Thyroid:'); }
	}
	$chc=$dt{'ee1_ge_thy_tnd'};
	$nt=trim($dt{'ee1_ge_thy_tnd_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Tenderness'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Tenderness'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Tenderness',$chc,$nt,'Thyroid:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Thyroid:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_thy_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Thyroid:');
	RL1_PrintNote($nt,'General Physical Exam','Thyroid:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$prnt=$chc=$chk='';
if($client_id == 'cffm') {
	$chc=$dt{'ee1_ge_br_sym'};
	$nt=trim($dt{'ee1_ge_br_sym_nt'});
	if($chc || $nt) {
		RL1_PrintGE_YN('Symmetrical',$chc,$nt,'Breasts:');
	}
}

$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_brr_axil'};
	$nt=trim($dt{'ee1_ge_brr_axil_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Axillary Nodes'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Axillary Nodes'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Axillary Nodes',$chc,$nt,'Breasts:','Right Breast'); }
	}
	$chc=$dt{'ee1_ge_brr_mass'};
	$nt=trim($dt{'ee1_ge_brr_mass_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Mass'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Mass'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Mass',$chc,$nt,'Breasts:','Right Breast'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Breasts:','Right Breast'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_brr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Breasts:','Right Breast');
	RL1_PrintNote($nt,'General Physical Exam','Breasts:','Right Breast');
}

$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_nipr_ev'};
	$nt=trim($dt{'ee1_ge_nipr_ev_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Everted'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Everted'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Everted',$chc,$nt,'Breasts:','Right Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipr_in'};
	$nt=trim($dt{'ee1_ge_nipr_in_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Inverted'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Inverted'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Inverted',$chc,$nt,'Breasts:','Right Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipr_mass'};
	$nt=trim($dt{'ee1_ge_nipr_mass_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Mass'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Mass'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Mass',$chc,$nt,'Breasts:','Right Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipr_dis'};
	$nt=trim($dt{'ee1_ge_nipr_dis_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Discharge'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Discharge'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Discharge',$chc,$nt,'Breasts:','Right Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipr_ret'};
	$nt=trim($dt{'ee1_ge_nipr_ret_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Retraction'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Retraction'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Retraction',$chc,$nt,'Breasts:','Right Nipple'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Breasts:','Right Nipple'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_nipr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Breasts:','Right Nipple');
	RL1_PrintNote($nt,'General Physical Exam','Breasts:','Right Nipple');
}

$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_brl_axil'};
	$nt=trim($dt{'ee1_ge_brl_axil_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Axillary Nodes'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Axillary Nodes'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Axillary Nodes',$chc,$nt,'Breasts:','Left Breast'); }
	}
	$chc=$dt{'ee1_ge_brl_mass'};
	$nt=trim($dt{'ee1_ge_brl_mass_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Mass'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Mass'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Mass',$chc,$nt,'Breasts:','Left Breast'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Breasts:','Left Breast'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_brl_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Breasts:','Left Breast');
	RL1_PrintNote($nt,'General Physical Exam','Breasts:','Left Breast');
}

$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_nipl_ev'};
	$nt=trim($dt{'ee1_ge_nipl_ev_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Everted'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Everted'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Everted',$chc,$nt,'Breasts:','Left Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipl_in'};
	$nt=trim($dt{'ee1_ge_nipl_in_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Inverted'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Inverted'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Inverted',$chc,$nt,'Breasts:','Left Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipl_mass'};
	$nt=trim($dt{'ee1_ge_nipl_mass_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Mass'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Mass'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Mass',$chc,$nt,'Breasts:','Left Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipl_dis'};
	$nt=trim($dt{'ee1_ge_nipl_dis_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Discharge'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Discharge'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Discharge',$chc,$nt,'Breasts:','Left Nipple'); }
	}
	$chc=$dt{'ee1_ge_nipl_ret'};
	$nt=trim($dt{'ee1_ge_nipl_ret_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Retraction'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Retraction'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Retraction',$chc,$nt,'Breasts:','Left Nipple'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Breasts:','Left Nipple'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_nipl_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Breasts:','Left Nipple');
	RL1_PrintNote($nt,'General Physical Exam','Breasts:','Left Nipple');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_cr_norm'};
	$nt=trim($dt{'ee1_ge_cr_norm_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Regular Rate&nbsp;&amp;&nbsp;Rhythm'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Regular Rate&nbsp;&amp;&nbsp;Rhythm'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Regular Rate&nbsp;&amp;&nbsp;Rhythm',$chc,$nt,'Cardiovascular:'); }
	}
	$chc=$dt{'ee1_ge_cr_mur'};
	$nt=trim($dt{'ee1_ge_cr_mur_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Murmur'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Murmur'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Murmur',$chc,$nt,'Cardiovascular:'); }
	}
	$chc=$dt{'ee1_ge_cr_gall'};
	$nt=trim($dt{'ee1_ge_cr_gall_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Gallops'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Gallops'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Gallops',$chc,$nt,'Cardiovascular:'); }
	}
	$chc=$dt{'ee1_ge_cr_click'};
	$nt=trim($dt{'ee1_ge_cr_click_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Clicks'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Clicks'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Clicks',$chc,$nt,'Cardiovascular:'); }
	}
	$chc=$dt{'ee1_ge_cr_rubs'};
	$nt=trim($dt{'ee1_ge_cr_rubs_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Rubs'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Rubs'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Rubs',$chc,$nt,'Cardiovascular:'); }
	}
	$chc=$dt{'ee1_ge_cr_extra'};
	$nt=trim($dt{'ee1_ge_cr_extra_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Extra Sound'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Extra Sound'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Extra Sound',$chc,$nt,'Cardiovascular:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Cardiovascular:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_cr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Cardiovascular:');
	RL1_PrintNote($nt,'General Physical Exam','Cardiovascular:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_pul_clear'};
	$nt='';
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Clear to Auscaultation'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Clear to Auscaultation'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Clear to Auscaultation',$chc,$nt,'Pulmonary:'); }
	}
	$chc=$dt{'ee1_ge_pul_rales'};
	$nt='';
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Rales'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Rales'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Rales',$chc,$nt,'Pulmonary:'); }
	}
	$chc=$dt{'ee1_ge_pul_whz'};
	$nt='';
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Wheezes'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Wheezes'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Wheezes',$chc,$nt,'Pulmonary:'); }
	}
	$chc=$dt{'ee1_ge_pul_ron'};
	$nt='';
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Ronchi'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Ronchi'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Ronchi',$chc,$nt,'Pulmonary:'); }
	}
	$chc=$dt{'ee1_ge_pul_dec'};
	$nt='';
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Decreased Breach Sounds'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Decreased Breath Sounds'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Decreased Breath Sounds',$chc,$nt,'Pulmonary:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Pulmonary:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_pul_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Pulmonary:');
	RL1_PrintNote($nt,'General Physical Exam','Pulmonary:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk=ListLook($dt{'ee1_ge_gi_tend'},'EE1_Tender');
$chc=ListLook($dt{'ee1_ge_gi_tend_loc'},'EE1_GI_Location');
RL1_PrintGE($chk,$chc,'','Gastrointestinal:');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk=$nt='';
	$chc=$dt{'ee1_ge_gi_soft'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Soft'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Soft'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Soft',$chc,$nt,'Gastrointestinal:'); }
	}
	$chc=$dt{'ee1_ge_gi_dis'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Nondistended'); }
		if($cnt == 3 && $chc == 'd') { $prnt=RL1_AppendItem($prnt,'Distended'); }
	}
	$chc=$dt{'ee1_ge_gi_scar'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Scar(s)'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Scar(s)'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Scar(s)',$chc,$nt,'Gastrointestinal:'); }
	}
	$chc=$dt{'ee1_ge_gi_hern'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Hernia'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Hernia'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Hernia',$chc,$nt,'Gastrointestinal:'); }
	}
	$chc=$dt{'ee1_ge_gi_bowel'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Bowel Sounds'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Bowel Sounds'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Bowel Sounds',$chc,$nt,'Gastrointestinal:'); }
	}
	$chc=$dt{'ee1_ge_gi_hepa'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Hepatomegaly'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Hepatomegaly'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Hepatomegaly',$chc,$nt,'Gastrointestinal:'); }
	}
	$chc=$dt{'ee1_ge_gi_spleno'};
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Splenomegaly'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Splenomegaly'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Splenomegaly',$chc,$nt,'Gastrointestinal:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Gastrointestinal:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_gi_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Gastrointestinal:');
	RL1_PrintNote($nt,'General Physical Exam','Gastrointestinal:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk='';
$chc=ListLook($dt{'ee1_ge_neu_ao'},'EE1_AO');
if(!empty($chc)) { $chk='Alert&nbsp;&amp;&nbsp;Oriented'; }
RL1_PrintGE($chk,$chc,'','Neurological:');
$chk='';
$chc=ListLook($dt{'ee1_ge_neu_cn'},'EE1_YesNo');
if(!empty($chc)) { $chk='Cranial Nerves II-XII Grossly Intact'; }
RL1_PrintGE_YN($chk,$chc,$dt{'ee1_ge_neu_cn_nt'},'Neurological:');

// For this section print any with commments and build an output of those
// with a choice but no comment for the end.
$prnt='';
$chc=ListLook($dt{'ee1_ge_neu_bicr'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_bicr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Right Bicep',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Right Bicep: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_bicl'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_bicl_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Left Bicep',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Left Bicep: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_trir'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_trir_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Right Tricep',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Right Tricep: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_tril'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_tril_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Left Tricep',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Left Tricep: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_brar'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_brar_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Right Brachioradialas',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Right Brachioradialas: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_bral'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_bral_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Left Brachioradialas',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Left Brachioradialas: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_patr'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_patr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Right Patella',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Right Patella: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_patl'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_patl_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Left Patella',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Left Patella: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_achr'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_achr_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Right Achilles',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Right Achilles: '.$chc); }
}
$chc=ListLook($dt{'ee1_ge_neu_achl'},'EE1_DTR');
$nt=trim($dt{'ee1_ge_neu_achl_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Left Achilles',$chc,$nt,'Neurological:','DTR\'s');
} else {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Left Achilles: '.$chc); }
}
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Neurological:','DTR\'s'); }

$GLOBALS['sub_printed']=false;
$prnt='';
$chc=ListLook($dt{'ee1_ge_neu_pup'},'Zero_to_5');
if($chc != '') { $prnt=RL1_AppendItem($prnt,'Proximal Upper: '.$chc); }
$chc=ListLook($dt{'ee1_ge_neu_plow'},'Zero_to_5');
if($chc != '') { $prnt=RL1_AppendItem($prnt,'Proximal Lower: '.$chc); }
$chc=ListLook($dt{'ee1_ge_neu_dup'},'Zero_to_5');
if($chc != '') { $prnt=RL1_AppendItem($prnt,'Distal Upper: '.$chc); }
$chc=ListLook($dt{'ee1_ge_neu_dlow'},'Zero_to_5');
if($chc != '') { $prnt=RL1_AppendItem($prnt,'Distal Lower: '.$chc); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Neurological:','Strength'); }
$nt=trim($dt{'ee1_ge_neu_str_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Neurological:','Strength');
	RL1_PrintNote($nt,'General Physical Exam','Neurological:','Strength');
}
$nt=trim($dt{'ee1_ge_neu_sense'});
if(!empty($nt)) {
	RL1_PrintGE('Sensation:','','','Neurological:');
	RL1_PrintNote($nt,'General Physical Exam','Neurological:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$chk=$chc='';
if($dt{'ee1_ge_ms_intact'}=='1') { $chk='Intact Without Atrophy'; }
RL1_PrintGE_YN($chk,$chc,$dt{'ee1_ge_ms_intact_nt'},'Musculoskeletal:');
$nt=trim($dt{'ee1_ge_ms_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Musculoskeletal:');
	RL1_PrintNote($nt,'General Physical Exam','Musculoskeletal:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk=ListLook($dt{'ee1_ge_ext_edema_chc'},'Edema');
$chc=ListLook($dt{'ee1_ge_ext_edema'},'EE1_YesNo');
$prnt='';
if($dt{'ee1_ge_ext_edema'} == 'n') {
	$prnt='No Edema';
} else if($dt{'ee1_ge_ext_edema'} == 'y' || $chk) {
	$prnt='Edema: '.$chk;
}
RL1_PrintGE($prnt,'',$dt{'ee1_ge_ext_edema_nt'},'Extremeties:');
// Append all the pulses on one print line
$prnt='';
$chc=ListLook($dt{'ee1_ge_ext_pls_rad'},'Zero_to_4');
if($chc != '') {
	$prnt=RL1_AppendItem($prnt,'Radial: '.$chc);
}
$chc=ListLook($dt{'ee1_ge_ext_pls_dors'},'Zero_to_4');
if($chc != '') {
	$prnt=RL1_AppendItem($prnt,'Dosalis Pedis: '.$chc);
}
$chc=ListLook($dt{'ee1_ge_ext_pls_post'},'Zero_to_4');
if($chc != '') {
	$prnt=RL1_AppendItem($prnt,'Posterior Tibial: '.$chc);
}
$chc=ListLook($dt{'ee1_ge_ext_pls_pop'},'Zero_to_4');
if($chc != '') {
	$prnt=RL1_AppendItem($prnt,'Popliteal: '.$chc);
}
$chc=ListLook($dt{'ee1_ge_ext_pls_fem'},'Zero_to_4');
if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Femoral: '.$chc); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Extremeties:','Pulses'); }

$GLOBALS['sub_printed']=false;
$chk='';
$chc=ListLook($dt{'ee1_ge_ext_refill'},'EE1_YesNo');
if(!empty($chc)) { $chk='Less Than 3 Seconds'; }
RL1_PrintGE_YN($chk,$chc,'','Extremeties:','Capillary Refill');
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_ext_club'};
	$nt=trim($dt{'ee1_ge_ext_club_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Clubbing'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Clubbing'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Clubbing',$chc,$nt,'Extremeties:'); }
	}
	$chc=$dt{'ee1_ge_ext_cyan'};
	$nt=trim($dt{'ee1_ge_ext_cyan_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Cyanosis'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Cyanosis'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Cyanosis',$chc,$nt,'Extremeties:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Extremeties:'); }
	$cnt++;
}
$nt=trim($dt{'ee1_ge_ext_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Extremeties:');
	RL1_PrintNote($nt,'General Physical Exam','Extremeties:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$prnt='';
$chc=ListLook($dt{'ee1_ge_db_prop'},'NormAbnorm');
$nt=trim($dt{'ee1_ge_db_prop_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Proprioception',$chc,$nt,'Diabetic Foot:');
} else if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Proprioception: '.$chc); }
$chc=ListLook($dt{'ee1_ge_db_vib'},'NormAbnorm');
$nt=trim($dt{'ee1_ge_db_vib_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Vibration Sense',$chc,$nt,'Diabetic Foot:');
} else if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Vibration Sense: '.$chc); }
$chc=ListLook($dt{'ee1_ge_db_sens'},'NormAbnorm');
$nt=trim($dt{'ee1_ge_db_sens_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Sensation to Monofilament Testing',$chc,$nt,'Diabetic Foot:');
} else if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Sensation to Monofilment Testing: '.$chc); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Diabetic Foot:'); }
$nt=trim($dt{'ee1_ge_db_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Diabetic Foot:');
	RL1_PrintNote($nt,'General Physical Exam','Diabetic Foot:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_te_cir'};
	$nt=trim($dt{'ee1_ge_te_cir_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Circumcised'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Circumcised'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Circumcised',$chc,$nt,'Testicular:','Penile'); }
	}
	$chc=$dt{'ee1_ge_te_les'};
	$nt=trim($dt{'ee1_ge_te_les_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Lesions'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Lesions'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Lesions',$chc,$nt,'Testicular:','Penile'); }
	}
	$chc=$dt{'ee1_ge_te_dis'};
	$nt=trim($dt{'ee1_ge_te_dis_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Discharge'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Discharge'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Discharge',$chc,$nt,'Testicular:','Penile'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Testicular:','Penile'); }
	$cnt++;
}

$chk='';
$chc=ListLook($dt{'ee1_ge_te_size'},'EE1_Testes_Size');
if(!empty($chc)) { $chk='Testes Size'; }
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_te_size_nt'},'Testicular:');

$GLOBALS['sub_printed']=false;
$prnt='';
$chc=ListLook($dt{'ee1_ge_te_palp'},'HardSoft');
$nt=trim($dt{'ee1_ge_te_palp_nt'});
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chc);
} else { RL1_PrintGE($chc,'',$nt,'Testicular:','Palpation'); }
$chc=ListLook($dt{'ee1_ge_te_mass'},'MassNone');
$nt=trim($dt{'ee1_ge_te_mass_nt'});
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chc);
} else { RL1_PrintGE($chc,'',$nt,'Testicular:','Palpitation'); }
$chc=ListLook($dt{'ee1_ge_te_tend'},'EE1_Tender');
$nt=trim($dt{'ee1_ge_te_tend_nt'});
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chc);
} else { RL1_PrintGE($chc,'',$nt,'Testicular:','Palpitation'); }
$chc=ListLook($dt{'ee1_ge_te_ery'},'Erythema');
$nt=trim($dt{'ee1_ge_te_ery_nt'});
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chc);
} else { RL1_PrintGE($chc,'',$nt,'Testicular:','Palpitation'); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Testicular:','Palpitation'); }
$nt=trim($dt{'ee1_ge_te_nt'});
if(!empty($nt)) {
	RL1_PrintGE('Notes:','','','Testicular:');
	RL1_PrintNote($nt,'General Physical Exam','Testicular:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$prnt='';
$chc=ListLook($dt{'ee1_ge_rc_tone'},'EE1_Tone');
$nt=trim($dt{'ee1_ge_rc_tone_nt'});
if(empty($nt)) {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Tone: '.$chc); }
} else { RL1_PrintGE('Tone',$chc,$nt,'Rectal:'); }
$chc=ListLook($dt{'ee1_ge_rc_ext'},'EE1_YesNo');
$nt=trim($dt{'ee1_ge_rc_ext_nt'});
if(empty($nt)) {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'External Hemorrhoid: '.$chc); }
} else { RL1_PrintGE('External Hemorrhoid',$chc,$nt,'Rectal:'); }
$chc=ListLook($dt{'ee1_ge_rc_pro'},'EE1_Prostate');
$nt=trim($dt{'ee1_ge_rc_pro_nt'});
if(empty($nt)) {
	if(!empty($chc)) { $prnt=RL1_AppendItem($prnt,'Prostate Size: '.$chc); }
} else { RL1_PrintGE('Prostate Size',$chc,$nt,'Rectal:'); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Rectal:'); }

$cnt=0;
while($cnt < 5) {
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$prnt=$chc=$chk='';
	$chc=$dt{'ee1_ge_rc_bog'};
	$nt=trim($dt{'ee1_ge_rc_bog_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Boggy'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Boggy'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Boggy',$chc,$nt,'Rectal:'); }
	}
	$chc=$dt{'ee1_ge_rc_hard'};
	$nt=trim($dt{'ee1_ge_rc_hard_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Hard'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Hard'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Hard',$chc,$nt,'Rectal:'); }
	}
	$chc=$dt{'ee1_ge_rc_mass'};
	$nt=trim($dt{'ee1_ge_rc_mass_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'No Masses'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Masses'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Masses',$chc,$nt,'Rectal:'); }
	}
	$chc=$dt{'ee1_ge_rc_tend'};
	$nt=trim($dt{'ee1_ge_rc_tend_nt'});
	if(empty($nt)) {
		if($cnt == 1 && $chc == 'n') { $prnt=RL1_AppendItem($prnt,'Not Tender'); }
		if($cnt == 3 && $chc == 'y') { $prnt=RL1_AppendItem($prnt,'Tender'); }
	} else {
		if($match == $chc) { RL1_PrintGE_YN('Tender',$chc,$nt,'Rectal:'); }
	}
	if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Rectal:'); }
	$cnt++;
}
$nt=$chk='';
$chc=ListLook($dt{'ee1_ge_rc_color'},'EE1_Stool_Color');
if($client_id == 'cffm') {
	if(!empty($chc) || !empty($dt{'ee1_ge_rc_color_nt'})) { $chk='Stool GWIAC'; }
} else {
	if(!empty($chc) || !empty($dt{'ee1_ge_rc_color_nt'})) { $chk='Stool'; }
}
RL1_PrintGE($chk,$chc,$dt{'ee1_ge_rc_color_nt'},'Rectal:');
$nt=trim($dt{'ee1_ge_rc_nt'});
if(!empty($nt)) {
	RL1_PrintGE_YN('Notes:','','','Rectal:');
	RL1_PrintNote($nt,'General Physical Exam','Rectal:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chk=$chc='';
if($dt{'ee1_ge_skin_app'}) { $chk='Normal Appendages'; }
if($dt{'ee1_ge_skin_les'}) { $chk=AppendItem($chk, 'No Suspicious Lesions Noted'); }
$chc=$dt{'ee1_ge_skin_ver'};
if($chc == 'n') { $chk=AppendItem($chk, 'No Veracities'); }
if($chc == 'y') { $chk=AppendItem($chk, 'Veracities'); }
RL1_PrintCompoundGE($chk, 'Skin:');
$nt=trim($dt{'ee1_ge_skin_nt'});
if(!empty($nt)) {
	RL1_PrintGE_YN('Notes:','','','Skin:');
	RL1_PrintNote($nt,'General Physical Exam','Skin:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";
$GLOBALS['list_printed']=false;
$GLOBALS['hdr_printed']=false;
$GLOBALS['sub_printed']=false;
$chc=$chk=$prnt='';
$nt=trim($dt{'ee1_ge_psych_judge_nt'});
if($dt{'ee1_ge_psych_judge'} == 1) { $chk='Assessment of Judgement/Insight'; }
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chk);
} else { RL1_PrintGE($chk,$chc,$nt,'Psychiatric:'); }
$chk='';
$nt=trim($dt{'ee1_ge_psych_orient_nt'});
if($dt{'ee1_ge_psych_orient'} == 1) { $chk='Orientation to Time, Place, Person'; }
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chk);
} else { RL1_PrintGE($chk,$chc,$nt,'Psychiatric:'); }
$chk='';
$nt=trim($dt{'ee1_ge_psych_memory_nt'});
if($dt{'ee1_ge_psych_memory'} == 1) { $chk='Assessment of Memory (Recent/Remoter)'; }
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chk);
} else { RL1_PrintGE($chk,$chc,$nt,'Psychiatric:'); }
$chk='';
$nt=trim($dt{'ee1_ge_psych_mood_nt'});
if($dt{'ee1_ge_psych_mood'} == 1) { $chk='Assessment of Mood/Affect'; }
if(empty($nt)) {
	$prnt=RL1_AppendItem($prnt,$chk);
} else { RL1_PrintGE($chk,$chc,$nt,'Psychiatric:'); }
if(!empty($prnt)) { RL1_PrintCompoundGE($prnt,'Psychiatric:'); }
$nt=trim($dt{'ee1_ge_psych_nt'});
if(!empty($nt)) {
	RL1_PrintGE_YN('Notes:','','','Psychiatric:');
	RL1_PrintNote($nt,'General Physical Exam','Physchiatric:');
}

if ($GLOBALS['list_printed']) echo "</ul>\n";

