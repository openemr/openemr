<?php 
require_once("{$GLOBALS['srcdir']}/sql.inc");

function DB_DiagSel($thisField) {
  $rlist= sqlStatement("SELECT * FROM codes WHERE code_type='2' ".
           "AND active='1' ORDER BY code");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='ICD9:" . $rrow['code']. "'";
    if($thisField == $rrow['code']) echo " selected='selected'";
    echo ">" . $rrow['code'] . " - " . $rrow['code_text'];
    echo "</option>";
  }
}

function DB_DiagLook($thisData) {
  if(!$thisData || $thisData == '') {
    return ''; 
  }
  $fres= sqlStatement("SELECT * FROM codes WHERE code_type='2' ".
           "AND active='1' AND code='".$thisData."'");
  $rret=sqlFetchArray($fres);
  $dispValue= $rret{'code'}."-".$rret{'code_text'};
  return $dispValue;
}

?>
