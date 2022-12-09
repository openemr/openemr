<?php
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decmial','',$frmdir);
$vitals = new wmtVitals($dt{'vid'}, $suppress_decimal);
$chp_printed = $hdr_printed = false;
$nt='';
if(!empty($vitals->height)) $nt .= "<td class='wmtPrnLabel'>Height:</td><td class='wmtPrnBody'>$vitals->height</td>";
if(!empty($vitals->weight)) $nt .= "<td class='wmtPrnLabel'>Weight:</td><td class='wmtPrnBody'>$vitals->weight</td>";
if((!empty($vitals->BMI) && ($vitals->BMI != '0.0')) || (!empty($vitals->BMI_status))) { 
   $nt .= "<td class='wmtPrnLabel'>BMI:</td><td class='wmtPrnBody'>$vitals->BMI - $vitals->BMI_status</td>";
}
if(!empty($vitals->pulse) && ($vitals->pulse != '0.00')) $nt .= "<td class='wmtPrnLabel'>Pulse:</td><td class='wmtPrnBody'>$vitals->pulse</td>";
if(!empty($vitals->respiration) && ($vitals->respiration != '0.00')) $nt.="<td class='wmtPrnLabel'>Respiration:</td><td class='wmtPrnBody'>$vitals->respiration</td>";
if(!empty($vitals->temperature) && ($vitals->temperature != '0.00')) $nt .= "<td class='wmtPrnLabel'>Temp:</td><td class='wmtPrnBody'>$vitals->temperature</td>";
if(!empty($nt)) {
  $chp_printed=PrintChapter($chp_title, $chp_printed);
  $hdr_printed=PrintHeader('Vitals Taken: '.$vitals->date,$hdr_printed);
  echo "		<tr>\n";
	echo "			",$nt,"\n";
	echo "		</tr>\n";
}

if($client_id == 'hcardio' || $client_id == 'ccc') {
	$nt='';
	if((!empty($dt{'ee1_lay_bps'})) || (!empty($dt{'ee1_lay_bpd'}))) { 
  		$nt.="<td class='wmtPrnLabel'>Supine BP:</td><td class='wmtPrnBody'>".$dt{'ee1_lay_bps'}." / ".$dt{'ee1_lay_bpd'}."</td>";
	}
	if((!empty($dt{'ee1_bps'})) || (!empty($dt{'ee1_bpd'}))) { 
  	$nt.="<td class='wmtPrnLabel'>Seated BP:</td><td class='wmtPrnBody'>".$dt{'ee1_bps'}." / ".$dt{'ee1_bpd'}."</td>";
	}
} else {
	$nt='';
	if((!empty($dt{'ee1_bps'})) || (!empty($dt{'ee1_bpd'}))) { 
  	$nt.="<td class='wmtPrnLabel'>Seated BP:</td><td class='wmtPrnBody'>".$dt{'ee1_bps'}." / ".$dt{'ee1_bpd'}."</td>";
	}
	if((!empty($dt{'ee1_lay_bps'})) || (!empty($dt{'ee1_lay_bpd'}))) { 
		if($client_id == 'cffm') {
  		$nt.="<td class='wmtPrnLabel'>Seated 2 BP:</td><td class='wmtPrnBody'>".$dt{'ee1_lay_bps'}." / ".$dt{'ee1_lay_bpd'}."</td>";
		} else {
  		$nt.="<td class='wmtPrnLabel'>Prone BP:</td><td class='wmtPrnBody'>".$dt{'ee1_lay_bps'}." / ".$dt{'ee1_lay_bpd'}."</td>";
		}
	}
}

if(!empty($vitals->bps) || !empty($vitals->bpd)) { 
   $nt.="<td class='wmtPrnLabel'>BP:&nbsp;</td><td class='wmtPrnBody'>$vitals->bps / $vitals->bpd</td>";
}
$tmp=ListLook($vitals->arm,'Vital_Arm');
if(!empty($tmp)) { $nt.="<td class='wmtPrnLabel'>Arm:</td><td class='wmtPrnBody'>$tmp</td>"; }
if(!empty($vitals->diabetes_accucheck)) { $nt.="<td class='wmtPrnLabel'>Finger Stick:</td><td class='wmtPrnBody'>$vitals->diabetes_accucheck</td>"; }
if(!empty($vitals->oxygen_saturation)) { $nt.="<td class='wmtPrnLabel'>O<sub>2</sub> Saturation:</td><td class='wmtPrnBody'>$vitals->oxygen_saturation</td>"; }
if(!empty($vitals->temperature) && ($vitals->temperature != '0.00')) { 
   $nt.="<td class='wmtPrnLabel'>Temperature:</td><td class='wmtPrnBody'>$vitals->temperature</td>";
}
if(!empty($nt)) {
  $chp_printed=PrintChapter($chp_title, $chp_printed);
  $hdr_printed=PrintHeader('Vitals Taken: '.$vitals->date,$hdr_printed);
  echo "		<tr>\n";
	echo "			$nt\n";
  echo "		</tr>\n";
}
$nt = trim($vitals->note);
if(!empty($nt)) {
  $chp_printed=PrintChapter($chp_title, $chp_printed);
  $hdr_printed=PrintHeader('Vitals Taken: '.$dt{'ee1_vital_timestamp'},$hdr_printed);
  echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel'>Vital Note:</td><td class='wmtPrnBody' colspan='12'>$nt</td>\n";
  echo "		</tr>\n";
}
?>
