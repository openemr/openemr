<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/forms.inc");
include_once("../../../library/sql.inc");
include_once("content_parser.php");

$field_names = array('category' => $_POST['category'], 'subcategory' => $_POST['subcategory'], 'item' => $_POST['item'], 'content' => $_POST['content']);

//to add codes to billing from CAMOS content field
//addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,$fee);


foreach ($field_names as $k => $var) {
$field_names[$k] = mysql_real_escape_string($var);
echo "$var\n";
}

process_commands($field_names['content'], $embedded_camos); 

$CAMOS_form_name = "CAMOS-".$field_names['category'].'-'.$field_names['subcategory'].'-'.$field_names['item'];

if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
  if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/",$field_names['content']) == 0) { //make sure blanks do not get submitted
    $newid = formSubmit("form_CAMOS", $field_names, $_GET["id"], $userauthorized);
    addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
  }
  //deal with embedded camos submissions here
  foreach($embedded_camos as $val) {
    if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/",$val['content']) == 0) { //make sure blanks not submitted
      foreach($val as $k => $v) {
        $val[$k] = trim($v);  
      } 
      $CAMOS_form_name = "CAMOS-".$val['category'].'-'.$val['subcategory'].'-'.$val['item'];
      $newid = formSubmit("form_CAMOS", $val, $_GET["id"], $userauthorized);
      addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
    }
  }
}
elseif ($_GET["mode"] == "update") {
  sqlInsert("update form_CAMOS set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), category='".$field_names["category"]."',subcategory='".$field_names["subcategory"]."',item='".$field_names["item"]."',content='".$field_names['content']."' where id=$id");
}
elseif ($_GET["mode"] == "delete") {
  sqlInsert("delete from form_CAMOS where id=$id and DATE_FORMAT(date,'%Y-%m-%d') like current_date()");
  sqlInsert("delete from forms where form_name like 'CAMOS%' and form_id=$id and pid='".$_SESSION["pid"]."' and DATE_FORMAT(date,'%Y-%m-%d') like current_date()");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
