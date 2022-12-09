<?php 

function U1UserLook($thisField) {
  if(!$thisField) return '';
  $ret = '';
  $rlist= sqlStatement("SELECT * FROM users WHERE username='" .
           $thisField."'");
  $rrow= sqlFetchArray($rlist);
  if($rrow) {
    $ret = $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
  }
  return $ret;
}

?>
