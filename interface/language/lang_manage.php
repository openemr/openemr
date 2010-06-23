<?php
if ($_POST['check'] || $_POST['synchronize']){
  // set up flag if only checking for changes (ie not performing synchronization)
  $checkOnly = 0;
  if ($_POST['check']) {
    $checkOnly = 1;
  }
  
  // set up the mysql collation string to ensure case is sensitive in the mysql queries
  if (!$disable_utf8_flag) {
    $case_sensitive_collation = "COLLATE utf8_bin";   
  }
  else {
    $case_sensitive_collation = "COLLATE latin_bin";
  }
  $difference = 0; //flag
    
  //  
  // collect and display(synchronize) new custom languages
  //
  $sql = "SELECT lang_description FROM lang_languages";
  $res = SqlStatement($sql);
  $row_main = array();
  while ($row=SqlFetchArray($res)){
    $row_main[] = $row['lang_description'];
  }
  $sql = "SELECT lang_description FROM lang_custom";
  $res = SqlStatement($sql);
  $row_custom = array();
  while ($row=SqlFetchArray($res)){
    $row_custom[] = $row['lang_description'];
  }
  $custom_languages = array_diff(array_unique($row_custom),array_unique($row_main));
  foreach ($custom_languages as $var) {
    if ($var=='') continue;
    echo htmlspecialchars(xl('Following is a new custom language:'),ENT_NOQUOTES)." ".htmlspecialchars($var,ENT_NOQUOTES)."<BR>";   
    if (!$checkOnly) {
      // add the new language (first collect the language code)
      $sql = "SELECT lang_code FROM lang_custom WHERE constant_name='' AND lang_description=? ".$case_sensitive_collation." LIMIT 1";
      $res = SqlStatement($sql, array($var) );
      $row = SqlFetchArray($res);
      $sql="INSERT INTO lang_languages SET lang_code=?, lang_description=?";
      SqlStatement($sql, array($row['lang_code'], $var) );
      echo htmlspecialchars(xl('Synchronized new custom language:'),ENT_NOQUOTES)." ".htmlspecialchars($var,ENT_NOQUOTES)."<BR><BR>";
    }
    $difference = 1;
  }
    
  //  
  // collect and display(synchronize) new custom constants
  //  
  $sql = "SELECT constant_name FROM lang_constants";
  $res = SqlStatement($sql);
  $row_main = array();
  while ($row=SqlFetchArray($res)){
    $row_main[] = $row['constant_name'];
  }
  $sql = "SELECT constant_name FROM lang_custom";
  $res = SqlStatement($sql);
  $row_custom = array();
  while ($row=SqlFetchArray($res)){
    $row_custom[] = $row['constant_name'];
  }
  $custom_constants = array_diff(array_unique($row_custom),array_unique($row_main));
  foreach ($custom_constants as $var) {
    if ($var=='') continue;
    echo htmlspecialchars(xl('Following is a new custom constant:'),ENT_NOQUOTES)." ".htmlspecialchars($var,ENT_NOQUOTES)."<BR>";
    if (!$checkOnly) {
      // add the new constant
      $sql="INSERT INTO lang_constants SET constant_name=?";
      SqlStatement($sql, array($var) );
      echo htmlspecialchars(xl('Synchronized new custom constant:'),ENT_NOQUOTES)." ".htmlspecialchars($var,ENT_NOQUOTES)."<BR><BR>";
    }
    $difference = 1;
  }
    
  //  
  // collect and display(synchronize) custom definitions
  //
  $sql = "SELECT lang_description, lang_code, constant_name, definition FROM lang_custom WHERE lang_description != '' AND constant_name != ''";
  $res = SqlStatement($sql);
  while ($row=SqlFetchArray($res)){
      
    // collect language id
    $sql = "SELECT lang_id FROM lang_languages WHERE lang_description=? ".$case_sensitive_collation." LIMIT 1";
    $res2 = SqlStatement($sql, array($row['lang_description']) );
    $row2 = SqlFetchArray($res2);
    $language_id=$row2['lang_id'];
      
    // collect constant id
    $sql = "SELECT cons_id FROM lang_constants WHERE constant_name=? ".$case_sensitive_collation." LIMIT 1";
    $res2 = SqlStatement($sql, array($row['constant_name']) );
    $row2 = SqlFetchArray($res2);
    $constant_id=$row2['cons_id'];
      
    // collect definition id (if it exists)
    $sql = "SELECT def_id FROM lang_definitions WHERE cons_id=? AND lang_id=? LIMIT 1";
    $res2 = SqlStatement($sql, array($constant_id, $language_id) );
    $row2 = SqlFetchArray($res2);
    $def_id=$row2['def_id'];
    
    if ($def_id) {
      //definition exist, so check to see if different
      $sql = "SELECT * FROM lang_definitions WHERE def_id=? AND definition=? ".$case_sensitive_collation;
      $res_test = SqlStatement($sql, array($def_id, $row['definition']) );
      if (SqlFetchArray($res_test)) {
	//definition not different
        continue;
      }
      else {
        //definition is different
        echo htmlspecialchars(xl('Following is a new definition (Language, Constant, Definition):'),ENT_NOQUOTES).
	  " ".htmlspecialchars($row['lang_description'],ENT_NOQUOTES).
	  " ".htmlspecialchars($row['constant_name'],ENT_NOQUOTES).
	  " ".htmlspecialchars($row['definition'],ENT_NOQUOTES)."<BR>";
        if (!$checkOnly) {
          //add new definition
          $sql = "UPDATE `lang_definitions` SET `definition`=? WHERE `def_id`=? LIMIT 1";
          SqlStatement($sql, array($row['definition'], $def_id) );
          echo htmlspecialchars(xl('Synchronized new definition (Language, Constant, Definition):'),ENT_NOQUOTES).
	    " ".htmlspecialchars($row['lang_description'],ENT_NOQUOTES).
	    " ".htmlspecialchars($row['constant_name'],ENT_NOQUOTES).
	    " ".htmlspecialchars($row['definition'],ENT_NOQUOTES)."<BR><BR>";
        }
	$difference = 1;
      }
    }
    else {
      echo htmlspecialchars(xl('Following is a new definition (Language, Constant, Definition):'),ENT_NOQUOTES).
        " ".htmlspecialchars($row['lang_description'],ENT_NOQUOTES).
	" ".htmlspecialchars($row['constant_name'],ENT_NOQUOTES).
	" ".htmlspecialchars($row['definition'],ENT_NOQUOTES)."<BR>";
      if (!$checkOnly) {
        //add new definition
        $sql = "INSERT INTO lang_definitions (cons_id,lang_id,definition) VALUES (?,?,?)";
        SqlStatement($sql, array($constant_id, $language_id, $row['definition']) );
        echo htmlspecialchars(xl('Synchronized new definition (Language, Constant, Definition):'),ENT_NOQUOTES).
	  " ".htmlspecialchars($row['lang_description'],ENT_NOQUOTES).
	  " ".htmlspecialchars($row['constant_name'],ENT_NOQUOTES).
	  " ".htmlspecialchars($row['definition'],ENT_NOQUOTES)."<BR><BR>";
      }
      $difference = 1;
    } 
  }
  if (!$difference) {
    echo htmlspecialchars(xl('The translation tables are synchronized.'),ENT_NOQUOTES);
  }
}
?>

<TABLE>
<FORM name="manage_form" METHOD=POST ACTION="?m=manage" onsubmit="return top.restoreSession()">
  <TR>
    <TD><INPUT TYPE="submit" name="check" value="<?php echo htmlspecialchars(xl('Check'),ENT_QUOTES); ?>"></TD>
    <TD class="text">(<?php echo htmlspecialchars(xl('Check for differences of translations with custom language table.'),ENT_NOQUOTES); ?>)</TD>  
  </TR>
  <TR></TR>
  <TR>
    <TD><INPUT TYPE="submit" name="synchronize" value="<?php echo htmlspecialchars(xl('Synchronize'),ENT_QUOTES); ?>"></TD>
    <TD class="text">(<?php echo htmlspecialchars(xl('Synchronize translations with custom language table.'),ENT_NOQUOTES); ?>)</TD>
  </TR>
</FORM>
</TABLE>	
