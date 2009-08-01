<?php

// gacl control
$thisauth = acl_check('admin', 'language');

if (!$thisauth) {
  echo "<html>\n<body>\n";
  echo "<p>" . xl('You are not authorized for this.','e') . "</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }



function check_pattern ($data,$pat) {
	if (ereg ($pat, $data)) { return TRUE ; } else { RETURN FALSE; }
}

//$pat="^(19[0-9]{2}|20[0-1]{1}[0-9]{1})-(0[1-9]|1[0-2])-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|3[0-1]{1})$";

?>
