<?php 
require_once("{$GLOBALS['srcdir']}/sql.inc");

function AL_AddVitalsTest($vrec,$ht,$wt,$bps,$bpd,$hr,$resp,$temp,$pf,$o2) {
  $add=false;
  // If there is no vitals record, and we captured data =>  ADD
  if($vrec == '' || $vrec == '0') { $add=true; }
  $vitals_entered = ($ht > 0);
  if(!$vitals_entered) { $vitals_entered= ($wt > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bps > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bpd > 0); }
  if(!$vitals_entered) { $vitals_entered= ($hr > 0); }
  if(!$vitals_entered) { $vitals_entered= ($resp > 0); }
  if(!$vitals_entered) { $vitals_entered= ($temp > 0); }
  if(!$vitals_entered) { $vitals_entered= ($pf > 0); }
  if(!$vitals_entered) { $vitals_entered= ($o2 > 0); }
  if($add && $vitals_entered) { return true; }
  $add=false;
  if($vrec != '' && $vrec != '0' && $vitals_entered) {
    $fres=sqlStatement("SELECT * FROM form_vitals WHERE id='".$vrec."'");
    $vitals=sqlFetchArray($fres);
    if($vitals) {
      if($ht != $vitals{'height'}) { $add=true; }
      if($wt != $vitals{'weight'}) { $add=true; }
      if($bps!= $vitals{'bps'}) { $add=true; }
      if($bpd != $vitals{'bpd'}) { $add=true; }
      if($hr != $vitals{'pulse'}) { $add=true; }
      if($resp != $vitals{'respiration'}) { $add=true; }
      if($temp!= $vitals{'temperature'}) { $add=true; }
      if($pf!= $vitals{'peak_flow'}) { $add=true; }
      if($o2!= $vitals{'oxygen_saturation'}) { $add=true; }
    }
  }
 return $add;
}

function getShots($pid, $cols='*') {
  $sql= "SELECT $cols FROM form_shot WHERE pid='".$pid.
    "' ORDER BY shot_timestamp DESC";
  $res= sqlStatement($sql);
  for($iter=0; $row=sqlFetchArray($res); $iter++)
  $all[$iter]= $row;
  return $all;
}

function getVials($pid, $cols='*') {
  $sql= "SELECT $cols FROM form_vial WHERE pid='".$pid.
    "' ORDER BY form_vial.id DESC";
  $res= sqlStatement($sql);
  for($iter=0; $row=sqlFetchArray($res); $iter++)
  $all[$iter]= $row;
  return $all;
}

function VialSel($pid, $emptyRow='1', $thisField='') {
  $rlist= sqlStatement("SELECT * FROM form_vial WHERE pid = '".$pid.
    "' ORDER BY id DESC");
  if($emptyRow) {
    echo "<option value=''";
    if(!$thisField) echo " selected='selected'";
    echo ">&nbsp;</option>";
  }
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['id'] . "'";
    if($thisField == $rrow['id']) echo " selected='selected'";
    echo ">" . $rrow['vial_id'].'-'.$rrow['vial_desc'];
    echo "</option>";
  }
}

function VialLook($pid, $thisField) {
  if(!$thisField) return('');
  $sql="SELECT * FROM form_vial WHERE pid = '" .
         $pid."' AND id = '" . $thisField . "'";
  $rlist= sqlStatement($sql);
  $rrow= sqlFetchArray($rlist);
  return($rrow['vial_id'].'-'.$rrow['vial_desc']);
}

function AL_PrintOverheadwNote($lbl='',$txt='',$nt='')
{
	if(!empty($lbl) || !empty($txt) || !empty($nt)) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel'>$lbl</td>\n";
		echo "			<td class='wmtPrnBody'>$txt</td>\n";
		echo "		</tr>\n";
		if(!empty($nt)) {
			echo "		<tr>\n";
			echo "			<td class='wmtPrnLabel2'>&nbsp;&nbsp;&nbsp;Notes:</td>\n";
			echo "			<td class='wmtPrnBody'>$nt</td>\n";
			echo "		</tr>\n";
		}
	}
}

?>
