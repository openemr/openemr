<?php

// gacl control
$thisauth = acl_check('admin', 'language');

if (!$thisauth) {
  echo "<html>\n<body>\n";
  echo "<p>You are not authorized for this.</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }



function check_pattern ($data,$pat) {
	if (ereg ($pat, $data)) { return TRUE ; } else { RETURN FALSE; }
}

//$pat="^(19[0-9]{2}|20[0-1]{1}[0-9]{1})-(0[1-9]|1[0-2])-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|3[0-1]{1})$";


// This function is kept here for information, but it is in a file called library/tranlation.inc.php

/*
function xl($constant,$mode='r',$prepend='',$append='') {
	$lang_id=LANGUAGE;
	$sql="SELECT * FROM lang_definitions JOIN lang_constants ON lang_definitions.cons_id = lang_constants.cons_id WHERE lang_id='$lang_id' AND constant_name = '$constant' LIMIT 1";
	$res=SqlStatement($sql);
	$row=SqlFetchArray($res);
	$string=$row['definition'];
	if ($string=='') { $string="$constant"; }
	$string=$prepend.$string.$append;
	if ($mode=='r'){ 
		return $string;	
	} else {
		echo $string;
	}
}
*/

?>