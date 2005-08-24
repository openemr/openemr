<?php
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
include_once($GLOBALS["srcdir"]."/sql.inc");
function well_infant_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_well_infant", $id);
$sql = "SELECT name from form_well_infant_checks where foreign_id = '" . mysql_real_escape_string($id) . "'";
$results = sqlQ($sql);
$data2 = array();
while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
	$data2[] = $row['name'];
}
$data = array_merge($data,$data2);	
if ($data) {
	print "<table><tr>";
	foreach($data as $key => $value) {
		if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
			continue;
		}
		if ($value == "on") {
			$value = "yes";
		}
	
		$key=ucwords(str_replace("_"," ",$key));
		if (is_numeric($key)){
			$key = "check";	
		}
		print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
		$count++;
		if ($count == $cols) {
			$count = 0;
			print "</tr><tr>\n";
		}
	}	
}
print "</tr></table>";
}
?> 
