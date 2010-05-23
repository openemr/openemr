<?php
//------------report.php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
function Forms_Cardiology_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_Forms_Cardiology", $id);
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
$mykey = $key.": ";
$myval = stripslashes($value);
print "<td><span class=bold>".xl("$mykey")."</span><span class=text>".xl("$myval")."</span></td>";
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
