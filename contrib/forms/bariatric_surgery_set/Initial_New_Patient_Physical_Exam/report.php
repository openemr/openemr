<?php
//================================================
//Form Created by
//Z&H Healthcare Solutions, LLC.
//www.zhservices.com
//sam@zhholdings.com
//Initial New Patient Physical Exam
//================================================
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
function Initial_New_Patient_Physical_Exam_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_Initial_New_Patient_Physical_Exam", $id);
if ($data) {
print "<hr><table><tr>";
foreach($data as $key => $value) {
if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
	continue;
}
if ($value == "on") {
$value = "yes";
}
$key=ucwords(str_replace("_"," ",$key));
$mykey = $key;
$myval = $value;
//Z&H Healthcare Solutions, LLC.
//www.zhservices.com
//Adding Starts
$pos = strpos($myval, 'Negative for ');
if ($pos === false) 
	;
elseif(!($mykey=='Note' || $mykey=='Note2' || $mykey=='Note3'))
	$myval=substr($myval, 0, $pos);
	
if(!($mykey=='Note' || $mykey=='Note2' || $mykey=='Note3'))
	$myval=str_replace("Positive for ","",$myval);
//Adding Ends
print "<td><span class=bold>".xl("$mykey").": </span><span class=text>".xl("$myval")."</span></td>";
$count++;
if ($count == $cols) {
$count = 0;
print "</tr><tr>\n";
}
}
}
print "</tr></table><hr>";
}
?> 
