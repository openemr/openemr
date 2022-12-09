<?php

function ListCheck($prefix, $checkList, $thisList) {
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = '".$thisList."' ORDER BY seq");
	
	if (is_string($checkList)) $checkList = explode('+', $checkList);
	while ($rrow= sqlFetchArray($rlist)) {
		$name = str_replace(" ", "_", $prefix."_".$rrow['option_id']);
		$name = $prefix."[]";
		echo "<span style='white-space:nowrap'><input type='checkbox' class='wmtCheck' name='".$name."' value='".$rrow['option_id']."' ";
		if (in_array($rrow['option_id'], $checkList)) echo "checked ";
		echo " />";
		echo "<label for='".$name."' class='wmtCheck' style='padding-right:10px'>".$rrow['title']."</label></span> ";
	}
}

function CheckLook($checkList, $thisList) {
	if (!$checkList || $checkList == '') return '';
	
	$rres=sqlStatement("SELECT * FROM list_options WHERE list_id='".$thisList."' ORDER BY seq");

	$dispValue = '';
	if (is_string($checkList)) $checkList = explode('+', $checkList);
	while ($rrow= sqlFetchArray($rres)) {
		if (in_array($rrow['option_id'], $checkList)) {
			if ($dispValue) $dispValue .= ", ";
			$dispValue .= "<span style='white-space:nowrap'>".$rrow['title']."</span>";
		}
	}
	
	return $dispValue;
}

function DisplayChecks($prefix, $checkList, $thisList) {
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = '".$thisList."' ORDER BY seq");
	
	if (is_string($checkList)) $checkList = explode('+', $checkList);
	while ($rrow= sqlFetchArray($rlist)) {
		if (!in_array($rrow['option_id'], $checkList)) { continue; }
		$name = str_replace(" ", "_", $prefix."_".$rrow['option_id']);
		$name = $prefix."[]";
		echo "<span style='white-space:nowrap'><input type='checkbox' class='wmtCheck' name='".$name."' value='".$rrow['option_id']."' checked='checked'";
		echo " />";
		echo "<label for='".$name."' class='wmtCheck' style='padding-right:10px'>".$rrow['title']."</label></span> ";
	}
}

function SelMultiWithDesc($thisVal, $thisList, $default='', $test='') {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
             "list_id=? ORDER BY seq",array($thisList));
  echo "<option value=''";
	if(!$default) { 
		echo " selected='selected'"; 
	}
  echo ">Choose Another</option>";
	echo "<option value='~ra~'>Remove All</option>\n";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
		// Possibly check the hidden field for the value, bold selected ones
    // if(is_array($thisArray)) {
      // if(in_array($rrow['option_id'],$thisArray)) echo " selected='selected'";
    // }
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

