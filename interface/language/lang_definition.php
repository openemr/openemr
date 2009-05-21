<?php
require_once("language.inc.php");

if ($_POST['Submit'] == "Load Definitions") {
	// query for entering new definitions it picks the cons_id because is existant.
	$sql = "INSERT INTO lang_definitions (`cons_id`,`lang_id`,`definition`) VALUES  ";
  if (!empty($_POST['cons_id'])) {
    foreach ($_POST['cons_id'] as $key => $value) {
      $value = mysql_real_escape_string (trim($value));
      $sql .= " ('$key', ";
      $sql .= "'" . $_POST['lang_id'] . "',";
      $sql .= "'" . $value . "'),";
      $go = 'yes';
    }
  }
	if ($go=='yes') {
		$sql=substr($sql,0,-1);
		if (SqlStatement($sql)) {
			xl ("New Definition set added",'e');
		}
	}
  // query for updating preexistant definitions uses def_id because there is no def yet.
  // echo ('<pre>');	print_r($_POST['def_id']);	echo ('</pre>');
  if (!empty($_POST['def_id'])) {
    foreach ($_POST['def_id'] as $key => $value) {
      $value = mysql_real_escape_string (trim($value));
      $sql = "UPDATE `lang_definitions` SET `definition` = '$value' WHERE `def_id`='$key' LIMIT 1";
      SqlStatement($sql);
    }
  }
}

if ($_GET['edit'] != ''){
	$lang_id = (int)$_GET['edit'];

  $lang_filter = isset($_GET['filter']) ? $_GET['filter'] : '';
  $lang_filter .= '%';

	$sql = "SELECT lc.cons_id, lc.constant_name, ld.def_id, ld.definition " .
    "FROM lang_definitions AS ld " .
    "RIGHT JOIN ( lang_constants AS lc, lang_languages AS ll ) ON " .
    "( lc.cons_id = ld.cons_id AND ll.lang_id = ld.lang_id ) " .
    "WHERE lc.constant_name LIKE '$lang_filter' AND ( ll.lang_id = 1 ";
  if ($lang_id != 1) {
		$sql .= "OR ll.lang_id = '$lang_id' ";
		$what = "SELECT * from lang_languages where lang_id = $lang_id LIMIT 1";
		$res = SqlStatement($what);
		$row = SqlFetchArray($res);
		$lang_name = $row['lang_description'];
	}
	$sql .= ") ORDER BY lc.constant_name";

	$res = SqlStatement($sql);

	echo ('<table><FORM METHOD=POST ACTION="?m=definition">');
	// only english definitions
	if ($lang_id==1) { 
		while ($row=SqlFetchArray($res)){
			echo ('<tr><td>'.$row['constant_name'].'</td>');
			// if there is no definition
			if (empty($row['def_id'])){
				$cons_name = "cons_id[" . $row['cons_id'] . "]";
			// if there is a previous definition
			} else {
				$cons_name = "def_id[" . $row['def_id'] . "]";
			}
			echo ('<td><INPUT TYPE="text" size="50" NAME="' . $cons_name . '" value="' . $row['definition'] . '">');
			echo ('</td><td></td></tr>');
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.$lang_id.'">');
	// english plus the other
	} else { 
		while ($row=SqlFetchArray($res)){
			echo ('<tr><td>'.$row['constant_name'].'</td>');
			if ($row['definition']=='' OR $row['definition']=='NULL') { 
				$def="NULL" ;
			} else {
				$def=$row['definition'];
			}
			echo ('<td>'.$def.'</td>');
			$row=SqlFetchArray($res); // jump one to get the second language selected
			if ($row['def_id']=='' OR $row['def_id']=='NULL'){
				$cons_name="cons_id[".$row['cons_id']."]";
			// if there is a previous definition
			} else {
				$cons_name="def_id[".$row['def_id']."]";;
			}
			echo ('<td><INPUT TYPE="text" size="50" NAME="'.$cons_name.'" value="'.$row['definition'].'">');
			echo ('</td></tr>');
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.$lang_id.'">');
	}
	echo ('<tr><td colspan=3><INPUT TYPE="submit" name="Submit" Value="Load Definitions"></td></tr>');
	echo ('</FORM></table>');
}

?>
