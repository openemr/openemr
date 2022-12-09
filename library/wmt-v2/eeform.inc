<?php 
function FlaggedListSelect($thisField, $thisList, $exists, $default) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                   "list_id = '".$thisList."' ORDER BY seq");
  echo "<option value=''";
  if(!$thisField && $exists) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if(($thisField == $rrow['option_id']) || (!$exists && ($rrow['option_id'] == $default))) echo " selected='selected'";
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function ListSelect($thisField, $thisList) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                    "list_id = '".$thisList."' ORDER BY seq");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if($thisField == $rrow['option_id']) echo " selected='selected'";
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function DrugSelect($thisField) {
  $rlist= sqlStatement("SELECT * FROM drugs WHERE " .
                    "name = '".$thisField."' ORDER BY name");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['name'] . "'";
    if($thisField == $rrow['name']) echo " selected='selected'";
    echo ">" . $rrow['name'];
    echo "</option>";
  }
}

function EEListLook($thisData, $thisList) {
  if(!$thisData || $thisData == '') {
    return ''; 
  }
  $fres=sqlStatement("SELECT * FROM list_options WHERE list_id='".
        $thisList."' AND option_id='".$thisData."'");
  if($fres) {
    $rret=sqlFetchArray($fres);
    $dispValue= $rret{'title'};
  } else {
    $dispValue= "Error";
  }
  return $dispValue;
}

function SetEESmokingDesc($thisValue) {
  $retval= "0";
  if(!$thisValue) $retval="0";
  if($thisValue == "1") $retval="currenttobacco";
  if($thisValue == "2") $retval="currenttobacco";
  if($thisValue == "3") $retval="quittobacco";
  if($thisValue == "4") $retval="nevertobacco";
  if($thisValue == "5") $retval="not_applicabletobacco";
  if($thisValue == "9") $retval="not_applicabletobacco";
  return $retval;
}

function EEGetList($pid, $type, $cols='*') {
  $sql = "SELECT $cols FROM lists WHERE pid='$pid' AND type='$type' " .
         "ORDER BY begdate";

  $res = sqlStatement($sql);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function EEPrintChapter($title, $printed) {
  if($printed) return true;
  echo "<div class='eePrintContainer'>\n";
  echo "<span class='eeReportHeader'>".$title."</span>\n";
  echo "<br/>\n";
  return true;
}

function EEPrintHeader($title, $printed, $print_break='true') {
  if($printed) return true;
  echo "<span class='eeReportLabel'>".$title."</span>\n";
  if($print_break) { echo "<br/>\n"; }
  return true;
}

function EEPrintTitle($title, $printed, $print_break='true') {
  if($printed) return true;
  echo "<span class='eeReportLabel'>&nbsp;&nbsp;".$title."</span>\n";
  if($print_break) { echo "<br/>\n"; }
  return true;
}

?>
