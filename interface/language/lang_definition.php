
  <form name='filterform' id='filterform' method='get' action='language.php' onsubmit="return top.restoreSession()">
  <span class='text'>&nbsp;&nbsp;&nbsp;<?php xl('Filter for Constants','e','',':'); ?>
  <input type='text' name='form_filter' size='8' value='' />
  <?php xl('(% matches any string, _ matches any character)','e'); ?>
  </form>
  </span>

  <div class="text">
  <br>

  <?php
  /* menu */
  $sql="SELECT * FROM lang_languages ORDER BY lang_id";
  $res=SqlStatement($sql);
  $string='|';
  while ($row=SqlFetchArray($res)){
    $string .= "| <a href='' onclick='return editLang(" . $row['lang_id'] . ")'>" . xl($row['lang_description']) . "</a> |";
  }
  $string.='|';
  echo (xl('Edit definitions').": $string <br><br>");

if ($_POST['load']) {
  // set up the mysql collation string to ensure case is sensitive in the mysql queries
  if (!$disable_utf8_flag) {
    $case_sensitive_collation = "COLLATE utf8_bin";
  }
  else {
    $case_sensitive_collation = "COLLATE latin_bin";
  }

	// query for entering new definitions it picks the cons_id because is existant.
  if (!empty($_POST['cons_id'])) {
    foreach ($_POST['cons_id'] as $key => $value) {
      $value = formDataCore($value,true);
	
      // do not create new blank definitions
      if ($value == "") continue;
	
      // insert into the main language tables
      $sql = "INSERT INTO lang_definitions (`cons_id`,`lang_id`,`definition`) VALUES ";
      $sql .= "('$key',";
      $sql .= "'" . formData('lang_id') . "',";
      $sql .= "'" . $value . "')";
      SqlStatement($sql);
		
      // insert each entry into the log table - to allow persistant customizations
      $sql = "SELECT lang_description, lang_code FROM lang_languages WHERE lang_id = '".formData('lang_id')."' LIMIT 1";
      $res = SqlStatement($sql);
      $row_l = SqlFetchArray($res);
      $sql = "SELECT constant_name FROM lang_constants WHERE cons_id = '".$key."' LIMIT 1";
      $res = SqlStatement($sql);
      $row_c = SqlFetchArray($res);
      insert_language_log(add_escape_custom($row_l['lang_description']), add_escape_custom($row_l['lang_code']), add_escape_custom($row_c['constant_name']), $value);
	  
      $go = 'yes';
    }  
  }
    
  // query for updating preexistant definitions uses def_id because there is no def yet.
  // echo ('<pre>');	print_r($_POST['def_id']);	echo ('</pre>');
  if (!empty($_POST['def_id'])) {
    foreach ($_POST['def_id'] as $key => $value) {
      $value = formDataCore($value,true);
	
      // only continue if the definition is new
      $sql = "SELECT * FROM lang_definitions WHERE def_id = '".$key."' AND definition = '".$value."' ".$case_sensitive_collation;
      $res_test = SqlStatement($sql);
      if (!SqlFetchArray($res_test)) {	
        // insert into the main language tables
        $sql = "UPDATE `lang_definitions` SET `definition` = '$value' WHERE `def_id`='$key' LIMIT 1";
        SqlStatement($sql);
	
        // insert each entry into the log table - to allow persistant customizations	
        $sql = "SELECT ll.lang_description, ll.lang_code, lc.constant_name ";
        $sql .= "FROM lang_definitions AS ld, lang_languages AS ll, lang_constants AS lc ";
        $sql .= "WHERE ld.def_id = '".$key."' ";
        $sql .= "AND ll.lang_id = ld.lang_id AND lc.cons_id = ld.cons_id LIMIT 1";
        $res = SqlStatement($sql);
        $row = SqlFetchArray($res);
	insert_language_log(add_escape_custom($row['lang_description']), add_escape_custom($row['lang_code']), add_escape_custom($row['constant_name']), $value);
	  
	$go = 'yes';
      }
    }
  }
  if ($go=='yes') xl("New Definition set added",'e');
}

if ($_GET['edit'] != ''){
	$lang_id = (int)formData('edit','G');

  $lang_filter = isset($_GET['filter']) ? formData('filter','G') : '';
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

	echo ('<table><FORM METHOD=POST ACTION="?m=definition" onsubmit="return top.restoreSession()">');
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
			echo ('<td><INPUT TYPE="text" size="50" NAME="' . $cons_name . '" value="' . htmlspecialchars($row['definition'],ENT_QUOTES) . '">');
			echo ('</td><td></td></tr>');
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.$lang_id.'">');
	// english plus the other
	} else { 
		while ($row=SqlFetchArray($res)){
			echo ('<tr><td>'.$row['constant_name'].'</td>');
			if ($row['definition']=='' OR $row['definition']=='NULL') { 
				$def=" " ;
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
			echo ('<td><INPUT TYPE="text" size="50" NAME="'.$cons_name.'" value="'.htmlspecialchars($row['definition'],ENT_QUOTES).'">');
			echo ('</td></tr>');
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.$lang_id.'">');
	}
	echo ('<tr><td colspan=3><INPUT TYPE="submit" name="load" Value="' . xl('Load Definitions') . '"></td></tr>');
	echo ('</FORM></table>');
}
    
?>
