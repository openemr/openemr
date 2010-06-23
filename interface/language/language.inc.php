<?php

// gacl control
$thisauth = acl_check('admin', 'language');

if (!$thisauth) {
  echo "<html>\n<body>\n";
  echo "<p>" . htmlspecialchars(xl('You are not authorized for this.'),ENT_NOQUOTES) . "</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

function check_pattern ($data,$pat) {
	if (ereg ($pat, $data)) { return TRUE ; } else { RETURN FALSE; }
}

// Function to insert/modify items in the language log table, lang_custom
//
function insert_language_log($lang_desc,$lang_code,$cons_name,$def) {
    
  // set up the mysql collation string to ensure case is sensitive in the mysql queries
  if (!$disable_utf8_flag) {
    $case_sensitive_collation = "COLLATE utf8_bin";
  }
  else {
    $case_sensitive_collation = "COLLATE latin_bin";
  }
    
    
  if ($cons_name == '') {
    // NEW LANGUAGE
    // (ensure not a repeat log entry)
    $sql = "SELECT * FROM lang_custom WHERE constant_name='' AND lang_description=? ".$case_sensitive_collation;
    $res_test = SqlStatement($sql, array($lang_desc) );
    if (!SqlFetchArray($res_test)) {
      $sql="INSERT INTO lang_custom SET lang_code=?, lang_description=?";
      SqlStatement($sql, array($lang_code, $lang_desc) );
    }      
  }
  elseif ($lang_desc == '') {
    // NEW CONSTANT
    // (ensure not a repeat entry)
    $sql = "SELECT * FROM lang_custom WHERE lang_description='' AND constant_name=? ".$case_sensitive_collation;
    $res_test = SqlStatement($sql, array($cons_name) );
    if (!SqlFetchArray($res_test)) {
      $sql="INSERT INTO lang_custom SET constant_name=?";
      SqlStatement($sql, array($cons_name) );
    }      
  }
  else {
    // FULL ENTRY
    // (ensure not a repeat log entry)
    $sql = "SELECT * FROM lang_custom WHERE lang_description=? ".$case_sensitive_collation." AND constant_name=? ".$case_sensitive_collation." AND definition=? ".$case_sensitive_collation;
    $res_test = SqlStatement($sql, array($lang_desc, $cons_name, $def) );
    if (!SqlFetchArray($res_test)) {
      // either modify already existing log entry or create a new one
      $sql = "SELECT * FROM lang_custom WHERE lang_description=? ".$case_sensitive_collation." AND constant_name=? ".$case_sensitive_collation;
      $res_test2 = SqlStatement($sql, array($lang_desc, $cons_name) );
      if (SqlFetchArray($res_test2)) {
        // modify existing log entry(s)
        $sql = "UPDATE lang_custom SET definition=? WHERE lang_description=? ".$case_sensitive_collation." AND constant_name=? ".$case_sensitive_collation;
        SqlStatement($sql, array($def, $lang_desc, $cons_name) );
      }
      else {
        // create new log entry
        $sql = "INSERT INTO lang_custom (lang_description,lang_code,constant_name,definition) VALUES (?,?,?,?)";
        SqlStatement($sql, array($lang_desc, $lang_code, $cons_name, $def) );
      }
    }
  }
}

?>
