
  <table>
    <form name='filterform' id='filterform' method='post' action='?m=definition' onsubmit="return top.restoreSession()">
    <tr>
      <td><?php echo htmlspecialchars(xl('Filter for Constants','','',':'),ENT_NOQUOTES); ?></td>
      <td><input type='text' name='filter_cons' size='8' value='<?php echo htmlspecialchars($_POST['filter_cons'],ENT_QUOTES); ?>' />
        <span class="text"><?php echo htmlspecialchars(xl('(% matches any string, _ matches any character)'),ENT_NOQUOTES); ?></span></td>
    </tr>
    <tr>
      <td><?php echo htmlspecialchars(xl('Filter for Definitions','','',':'),ENT_NOQUOTES); ?></td>
      <td><input type='text' name='filter_def' size='8' value='<?php echo htmlspecialchars($_POST['filter_def'],ENT_QUOTES); ?>' />
        <span class="text"><?php echo htmlspecialchars(xl('(% matches any string, _ matches any character)'),ENT_NOQUOTES); ?></span></td>
    </tr>
    <tr>	
      <td><?php echo htmlspecialchars(xl('Select Language').":",ENT_NOQUOTES); ?></td>
      <td>
	<select name='language_select'>
          <?php
          // sorting order of language titles depends on language translation options.
          $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
          if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation']))
          {
            $sql = "SELECT * FROM lang_languages ORDER BY lang_description, lang_id";
	    $res=SqlStatement($sql);
          }
          else {
            // Use and sort by the translated language name.
            $sql = "SELECT ll.lang_id, " .
              "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS lang_description " .
              "FROM lang_languages AS ll " .
              "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
              "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
              "ld.lang_id=? " .
              "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
            $res=SqlStatement($sql, array($mainLangID) );
	  }
          // collect the default selected language id, and then display list
          $tempLangID = isset($_POST['language_select']) ? $_POST['language_select'] : $mainLangID;
          while ($row=SqlFetchArray($res)){
	    if ($tempLangID == $row['lang_id']) {
	      echo "<option value='" . htmlspecialchars($row['lang_id'],ENT_QUOTES) . "' selected>" . htmlspecialchars($row['lang_description'],ENT_NOQUOTES) . "</option>";
	    }
	    else { 
              echo "<option value='" . htmlspecialchars($row['lang_id'],ENT_QUOTES) . "'>" . htmlspecialchars($row['lang_description'],ENT_NOQUOTES) . "</option>";
	    }
          }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan=2><INPUT TYPE="submit" name="edit" value="<?php echo htmlspecialchars(xl('Search'),ENT_QUOTES); ?>"></td>
    </tr>
    </form>
  </table>
  <br>
<?php

// set up the mysql collation string to ensure case is sensitive (or insensitive) in the mysql queries
if (!$disable_utf8_flag) {
  $case_sensitive_collation = "COLLATE utf8_bin";
  $case_insensitive_collation = "COLLATE utf8_general_ci";
}
else {
  $case_sensitive_collation = "COLLATE latin_bin";
  $case_insensitive_collation = "COLLATE latin1_swedish_ci";
}

if ($_POST['load']) {

  // query for entering new definitions it picks the cons_id because is existant.
  if (!empty($_POST['cons_id'])) {
    foreach ($_POST['cons_id'] as $key => $value) {
      $value = trim($value);
	
      // do not create new blank definitions
      if ($value == "") continue;
	
      // insert into the main language tables
      $sql = "INSERT INTO lang_definitions (`cons_id`,`lang_id`,`definition`) VALUES (?,?,?)";
      SqlStatement($sql, array($key, $_POST['lang_id'], $value) );
		
      // insert each entry into the log table - to allow persistant customizations
      $sql = "SELECT lang_description, lang_code FROM lang_languages WHERE lang_id=? LIMIT 1";
      $res = SqlStatement($sql, array($_POST['lang_id']) );
      $row_l = SqlFetchArray($res);
      $sql = "SELECT constant_name FROM lang_constants WHERE cons_id=? LIMIT 1";
      $res = SqlStatement($sql, array($key) );
      $row_c = SqlFetchArray($res);
      insert_language_log($row_l['lang_description'], $row_l['lang_code'], $row_c['constant_name'], $value);
	  
      $go = 'yes';
    }  
  }
    
  // query for updating preexistant definitions uses def_id because there is no def yet.
  // echo ('<pre>');	print_r($_POST['def_id']);	echo ('</pre>');
  if (!empty($_POST['def_id'])) {
    foreach ($_POST['def_id'] as $key => $value) {
      $value = trim($value);
	
      // only continue if the definition is new
      $sql = "SELECT * FROM lang_definitions WHERE def_id=? AND definition=? ".$case_sensitive_collation;
      $res_test = SqlStatement($sql, array($key, $value) );
      if (!SqlFetchArray($res_test)) {	
        // insert into the main language tables
        $sql = "UPDATE `lang_definitions` SET `definition`=? WHERE `def_id`=? LIMIT 1";
        SqlStatement($sql, array($value, $key) );
	
        // insert each entry into the log table - to allow persistant customizations	
        $sql = "SELECT ll.lang_description, ll.lang_code, lc.constant_name ";
        $sql .= "FROM lang_definitions AS ld, lang_languages AS ll, lang_constants AS lc ";
        $sql .= "WHERE ld.def_id=? ";
        $sql .= "AND ll.lang_id = ld.lang_id AND lc.cons_id = ld.cons_id LIMIT 1";
        $res = SqlStatement($sql, array($key) );
        $row = SqlFetchArray($res);
	insert_language_log($row['lang_description'], $row['lang_code'], $row['constant_name'], $value);
	  
	$go = 'yes';
      }
    }
  }
  if ($go=='yes') echo htmlspecialchars(xl("New Definition set added"),ENT_NOQUOTES);
}

if ($_POST['edit']){
  if ($_POST['language_select'] == '') {  
     exit(htmlspecialchars(xl("Please select a language"),ENT_NOQUOTES));
  }
    
  $lang_id = (int)formData('language_select');
    
  $lang_filter = isset($_POST['filter_cons']) ? $_POST['filter_cons'] : '';
  $lang_filter .= '%';
  $lang_filter_def = isset($_POST['filter_def']) ? $_POST['filter_def'] : '';
  $lang_filter_def .= '%';

  $bind_sql_array = array();
  array_push($bind_sql_array, $lang_filter);
	$sql = "SELECT lc.cons_id, lc.constant_name, ld.def_id, ld.definition, ld.lang_id " .
    "FROM lang_definitions AS ld " .
    "RIGHT JOIN ( lang_constants AS lc, lang_languages AS ll ) ON " .
    "( lc.cons_id = ld.cons_id AND ll.lang_id = ld.lang_id ) " .
    "WHERE lc.constant_name ".$case_insensitive_collation." LIKE ? AND ( ll.lang_id = 1 ";
  if ($lang_id != 1) {
                array_push($bind_sql_array, $lang_id);
		$sql .= "OR ll.lang_id=? ";
		$what = "SELECT * from lang_languages where lang_id=? LIMIT 1";
		$res = SqlStatement($what, array($lang_id) );
		$row = SqlFetchArray($res);
		$lang_name = $row['lang_description'];
	}
	$sql .= ") ORDER BY lc.constant_name ".$case_insensitive_collation;
	$res = SqlStatement($sql,$bind_sql_array);
	    
        $isResults = false; //flag to record whether there are any results
	echo ('<table><FORM METHOD=POST ACTION="?m=definition" onsubmit="return top.restoreSession()">');
	// only english definitions
	if ($lang_id==1) {
		while ($row=SqlFetchArray($res)){
		        $isShow = false; //flag if passes the definition filter
		        $stringTemp = '<tr><td>'.htmlspecialchars($row['constant_name'],ENT_NOQUOTES).'</td>';
			// if there is no definition
			if (empty($row['def_id'])){
				$cons_name = "cons_id[" . $row['cons_id'] . "]";
			        if ($lang_filter_def=='%') $isShow = true;
			// if there is a previous definition
			} else {
				$cons_name = "def_id[" . $row['def_id'] . "]";
			        $sql = "SELECT definition FROM lang_definitions WHERE def_id=? AND definition LIKE ?";
			        $res2 = SqlStatement($sql, array($row['def_id'], $lang_filter_def) );
			        if (SqlFetchArray($res2)) $isShow = true;   
			}
			$stringTemp .= '<td><INPUT TYPE="text" size="50" NAME="' . htmlspecialchars($cons_name,ENT_QUOTES) . '" value="' . htmlspecialchars($row['definition'],ENT_QUOTES) . '">';
			$stringTemp .= '</td><td></td></tr>';
		        if ($isShow) {
			        //definition filter passed, so show
			        echo $stringTemp;
			        $isResults = true;
		        }
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.htmlspecialchars($lang_id,ENT_QUOTES).'">');
	// english plus the other
	} else {
		while ($row=SqlFetchArray($res)){
      if (!empty($row['lang_id']) && $row['lang_id'] != '1') {
        // This should not happen, if it does that must mean that this
        // constant has more than one definition for the same language!
        continue;
      }
		        $isShow = false; //flag if passes the definition filter
			$stringTemp = '<tr><td>'.htmlspecialchars($row['constant_name'],ENT_NOQUOTES).'</td>';
			if ($row['definition']=='' OR $row['definition']=='NULL') { 
				$def=" " ;
			} else {
				$def=$row['definition'];
			}
			$stringTemp .= '<td>'.htmlspecialchars($def,ENT_NOQUOTES).'</td>';
			$row=SqlFetchArray($res); // jump one to get the second language selected
			if ($row['def_id']=='' OR $row['def_id']=='NULL'){
				$cons_name="cons_id[".$row['cons_id']."]";
			        if ($lang_filter_def=='%') $isShow = true;
			// if there is a previous definition
			} else {
				$cons_name="def_id[".$row['def_id']."]";;
			        $sql = "SELECT definition FROM lang_definitions WHERE def_id=? AND definition LIKE ?";
			        $res2 = SqlStatement($sql, array($row['def_id'], $lang_filter_def) );
			        if (SqlFetchArray($res2)) $isShow = true;
			}
			$stringTemp .= '<td><INPUT TYPE="text" size="50" NAME="'.htmlspecialchars($cons_name,ENT_QUOTES).'" value="'.htmlspecialchars($row['definition'],ENT_QUOTES).'">';
			$stringTemp .='</td></tr>';
                        if ($isShow) {
			        //definition filter passed, so show
			        echo $stringTemp;
			        $isResults = true;
			}
		}
		echo ('<INPUT TYPE="hidden" name="lang_id" value="'.htmlspecialchars($lang_id,ENT_QUOTES).'">');
	}
	if ($isResults) {
	        echo ('<tr><td colspan=3><INPUT TYPE="submit" name="load" Value="' . htmlspecialchars(xl('Load Definitions'),ENT_NOQUOTES) . '"></td></tr>');
                ?>
	        <INPUT TYPE="hidden" name="filter_cons" value="<?php echo htmlspecialchars($_POST['filter_cons'],ENT_QUOTES); ?>">
	        <INPUT TYPE="hidden" name="filter_def" value="<?php echo htmlspecialchars($_POST['filter_def'],ENT_QUOTES); ?>">
	        <INPUT TYPE="hidden" name="language_select" value="<?php echo htmlspecialchars($_POST['language_select'],ENT_QUOTES); ?>">
	        <?php
	}
        else {
	        echo htmlspecialchars(xl('No Results Found For Search'),ENT_NOQUOTES);   
	}
	echo ('</FORM></table>');
}
    
?>
