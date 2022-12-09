<?php 
if(!function_exists('W1_AddDashBoardTest')) {
function W1_AddDashBoardTest($dbrec,$colon='',$bd='',$chol='',$pap='',$mamm='') {
  $add=false;
  // If there is no dashboard record, and we captured data =>  ADD
  if($dbrec == '' || $dbrec == '0') { $add=true; }
  $dates_entered = ($colon != 0 && $colon != '');
  if(!$dates_entered) { $dates_entered= ($bd != 0 && $bd != ''); }
  if(!$dates_entered) { $dates_entered= ($chol > 0 && $chol != ''); }
  if(!$dates_entered) { $dates_entered= ($pap > 0 && $pap != ''); }
  if(!$dates_entered) { $dates_entered= ($mamm > 0 && $mamm != ''); }
  if($add && $dates_entered) { return true; }
  $add=false;
  if($dbrec != '' && $dbrec != '0' && $dates_entered) {
    $fres=sqlStatement("SELECT * FROM form_dashboard WHERE id='".$dbrec."'");
    $dash=sqlFetchArray($fres);
    if($dash) {
      if($colon != $dash{'db_last_colon'}) { $add=true; }
      if($bd != $dash{'db_last_bone'}) { $add=true; }
      if($chol!= $dash{'db_last_chol'}) { $add=true; }
      if($pap != $dash{'db_last_pap'}) { $add=true; }
      if($mamm != $dash{'db_last_mamm'}) { $add=true; }
    }
  }
 return $add;
}

function WW2_UpdateDashboard($thisPid,$pap='',$mamm='',$bd='',$chol='',$colon='') {
  if(empty($thisPid)) { return false; }
  $update='';
  // echo "Add Dashboard Test, PID: ",$thisPid,"<br/>\n";
  echo 'Updating dashboard entries..';
  $dates_entered = ($colon != 0 && $colon != '');
  if(!$dates_entered) { $dates_entered= ($bd != 0 && $bd != ''); }
  if(!$dates_entered) { $dates_entered= ($chol > 0 && $chol != ''); }
  if(!$dates_entered) { $dates_entered= ($pap > 0 && $pap != ''); }
  if(!$dates_entered) { $dates_entered= ($mamm > 0 && $mamm != ''); }
  if(!$dates_entered) { return true; }
  echo '..';
  $fres=sqlStatement("SELECT * FROM form_dashboard WHERE pid='".$thisPid."'");
  echo '..';
  $dash=sqlFetchArray($fres);
  echo '..';
  if(!$dash) {
    // echo "No dashboard was found..Creating";
    sqlStatement("INSERT INTO form_dashboard (date, pid, user, ".
	  "groupname, authorized, activity) VALUES (NOW(), '".$thisPid.
	  "', '".$_SESSION['authUser']."', '".$_SESSION['authProvider']."', '".
	  $_SESSION['userauthorized']."', '1')");

  }
  if(!empty($colon) && $colon != $dash{'db_last_colon'}) { 
		$update.="db_last_colon='".$colon."', ";
  }
  if(!empty($bd) && $bd > $dash{'db_last_bone'}) {
		$update.="db_last_bone='".$bd."', ";
  }
  if(!empty($chol) && $chol > $dash{'db_last_chol'}) {
		$update.="db_last_chol='".$chol."', ";
  }
  if(!empty($pap) && $pap > $dash{'db_last_pap'}) {
		$update.="db_last_pap='".$pap."', ";
  }
  if(!empty($mamm) && $mamm > $dash{'db_last_mamm'}) {
		$update.="db_last_mamm='".$mamm."', ";
  }
	if(!empty($update)) {
		$form_dt=date('Y-m-d');
		$update.="db_form_dt='".$form_dt."'";
    $sql = "UPDATE form_dashboard set date=NOW(), ".$update.
			", user='".$_SESSION['authUser']."', groupname='".
			$_SESSION['authProvider']."', authorized='".$_SESSION['userauthorized'].
			"', activity='1' WHERE pid='".$thisPid."'";
    // echo $sql."<br/>\n";
    sqlInsert($sql);
  }
  echo '..';
  return $true;
}

function WHC_PrintROS($yn='', $note='', $title='', $section='', $match='')
{
	if($yn != $match) { return false; }
	$yn=ListLook($yn,'WHC_YesNo');
	$note=trim($note);
  if(!empty($yn) || !empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' style='width: 20%'>$title</td>\n";
		echo "			<td class='wmtPrnLabel' style='width: 5%'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function WHC_PrintCompoundROS($note='', $section='')
{
	$note=trim($note);
  if(!empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function WHC_PrintGE_YN($type='',$yn='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($yn) || !empty($note) || !empty($type)) {
		$yn=ListLook($yn, 'WHC_YesNo');
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function WHC_PrintGE($type='',$chc='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($chc) || !empty($note) || !empty($type)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$chc</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function WHC_PrintCompoundGE($note='', $section='', $subsection='')
{
	$note=trim($note);
  if(!empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function WHC_PrintNote($note='')
{
  $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
 	echo "  	<tr>\n";
	echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
	echo "		</tr>\n";
}

}
?>
