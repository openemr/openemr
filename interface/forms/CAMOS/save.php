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

$embedded_camos = process_commands($field_names['content']);
$field_names['content'] = remove_comments($field_names['content']);

if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
  $newid = formSubmit("form_CAMOS", $field_names, $_GET["id"], $userauthorized);
  addForm($encounter, "CAMOS", $newid, "CAMOS", $pid, $userauthorized);
  //deal with embedded camos submissions here
  foreach($embedded_camos as $val) {
    foreach($val as $k => $v) {
      $val[$k] = trim($v);  
    } 
    $newid = formSubmit("form_CAMOS", $val, $_GET["id"], $userauthorized);
    addForm($encounter, "CAMOS", $newid, "CAMOS", $pid, $userauthorized);
  }
}
elseif ($_GET["mode"] == "update") {
  sqlInsert("update form_CAMOS set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), category='".$field_names["category"]."',subcategory='".$field_names["subcategory"]."',item='".$field_names["item"]."',content='".$field_names['content']."' where id=$id");
}
elseif ($_GET["mode"] == "delete") {
  sqlInsert("delete from form_CAMOS where id=$id and date(date) = date(now())");
  sqlInsert("delete from forms where form_name='CAMOS' and form_id=$id and pid='".$_SESSION["pid"]."' and date(date) = date(now())");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
