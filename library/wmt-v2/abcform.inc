<?php 
function ABCFlagListSel($thisField, $thisList, $exists, $default) {
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

function ABCListSel($thisField, $thisList) {
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

function ABCListLook($thisData, $thisList) {
  if(!$thisData || $thisData == '') {
    return 'Unassigned'; 
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

function SetABCSmokingDesc($thisValue) {
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

?>
