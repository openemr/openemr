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
    echo xl('Following is a new custom language:')." ".$var."<BR>";   
    if (!$checkOnly) {
      // add the new language (first collect the language code)
      $sql = "SELECT lang_code FROM lang_custom WHERE constant_name='' AND lang_description='".add_escape_custom($var)."' ".$case_sensitive_collation." LIMIT 1";
      $res = SqlStatement($sql);
      $row = SqlFetchArray($res);
      $sql="INSERT INTO lang_languages SET lang_code='".add_escape_custom($row['lang_code'])."', lang_description='".add_escape_custom($var)."'";
      SqlStatement($sql);
      echo xl('Synchronized new custom language:')." ".$var."<BR><BR>";
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
    echo xl('Following is a new custom constant:')." ".$var."<BR>";
    if (!$checkOnly) {
      // add the new constant
      $sql="INSERT INTO lang_constants SET constant_name='".add_escape_custom($var)."'";
      SqlStatement($sql);
      echo xl('Synchronized new custom constant:')." ".$var."<BR><BR>";
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
    $sql = "SELECT lang_id FROM lang_languages WHERE lang_description='".add_escape_custom($row['lang_description'])."' ".$case_sensitive_collation." LIMIT 1";
    $res2 = SqlStatement($sql);
    $row2 = SqlFetchArray($res2);
    $language_id=$row2['lang_id'];
      
    // collect constant id
    $sql = "SELECT cons_id FROM lang_constants WHERE constant_name='".add_escape_custom($row['constant_name'])."' ".$case_sensitive_collation." LIMIT 1";
    $res2 = SqlStatement($sql);
    $row2 = SqlFetchArray($res2);
    $constant_id=$row2['cons_id'];
      
    // collect definition id (if it exists)
    $sql = "SELECT def_id FROM lang_definitions WHERE cons_id='".add_escape_custom($constant_id)."' AND lang_id='".add_escape_custom($language_id)."' LIMIT 1";
    $res2 = SqlStatement($sql);
    $row2 = SqlFetchArray($res2);
    $def_id=$row2['def_id'];
    
    if ($def_id) {
      //definition exist, so check to see if different
      $sql = "SELECT * FROM lang_definitions WHERE def_id = '".add_escape_custom($def_id)."' AND definition = '".add_escape_custom($row['definition'])."' ".$case_sensitive_collation;
      $res_test = SqlStatement($sql);
      if (SqlFetchArray($res_test)) {
	//definition not different
        continue;
      }
      else {
        //definition is different
        echo xl('Following is a new definition (Language, Constant, Definition):')." ".$row['lang_description']." ".$row['constant_name']." ".$row['definition']."<BR>";
        if (!$checkOnly) {
          //add new definition
          $sql = "UPDATE `lang_definitions` SET `definition` = '".add_escape_custom($row['definition'])."' WHERE `def_id`='".add_escape_custom($def_id)."' LIMIT 1";
          SqlStatement($sql);
          echo xl('Synchronized new definition (Language, Constant, Definition):')." ".$row['lang_description']." ".$row['constant_name']." ".$row['definition']."<BR><BR>";
        }
	$difference = 1;
      }
    }
    else {
      echo xl('Following is a new definition (Language, Constant, Definition):')." ".$row['lang_description']." ".$row['constant_name']." ".$row['definition']."<BR>";
      if (!$checkOnly) {
        //add new definition
        $sql = "INSERT INTO lang_definitions (cons_id,lang_id,definition) VALUES ('".add_escape_custom($constant_id)."','".add_escape_custom($language_id)."','".add_escape_custom($row['definition'])."')";
        SqlStatement($sql);
        echo xl('Synchronized new definition (Language, Constant, Definition):')." ".$row['lang_description']." ".$row['constant_name']." ".$row['definition']."<BR><BR>";
      }
      $difference = 1;
    } 
  }
  if (!$difference) {
    echo xl('The translation tables are synchronized.');
  }
}
?>

<TABLE>
<FORM name="manage_form" METHOD=POST ACTION="?m=manage" onsubmit="return top.restoreSession()">
  <TR>
    <TD><INPUT TYPE="submit" name="check" value="<?php xl('Check','e'); ?>"></TD>
    <TD class="text"> <?php xl('(Check for Differences of Translations with Custom Language Table)','e'); ?></TD>  
  </TR>
  <TR></TR>
  <TR>
    <TD><INPUT TYPE="submit" name="synchronize" value="<?php xl('Synchronize','e'); ?>"></TD>
    <TD class="text"> <?php xl('(Synchronize Translations with Custom Language Table)','e'); ?></TD>
  </TR>
</FORM>
</TABLE>	
