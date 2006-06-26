<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/forms.inc");
include_once("../../../library/sql.inc");

$field_names = array('category' => $_POST['category'], 'subcategory' => $_POST['subcategory'], 'item' => $_POST['item'], 'content' => $_POST['content']);

//to add codes to billing from CAMOS content field
//addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,$fee);


foreach ($field_names as $k => $var) {
$field_names[$k] = mysql_real_escape_string($var);
echo "$var\n";
}

//The following little section is to sift out data to be entered into the billing table
//or, in the future, wherever...
//as it is written write now, it just removes all but the list and rewrites the string with '-' 
//in between.  It uses '::' as a delimeter.  you will have to put a :: before each piece of data
//with no spaces between.  Since we will probably want to allow for multiple table entries,
//I guess we will need another delimiter to split first...well, at least this is proof that 
//it can be done and basically demonstrates how to do it.
$content_value = $field_names['content'];
$content_array = split('::',$field_names['content']);
if (count($content_array) > 1) {
  $content_value = '';
  $i = 0;
  foreach ($content_array as $k => $var) {
    if ($i > 0) {
      $content_value .= $var."-";
    }
    $i++;
  }
}
$field_names['content'] = $content_value;
//end of experimental '::' section :)

if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_CAMOS", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "CAMOS", $newid, "CAMOS", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_CAMOS set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), category='".$field_names["category"]."',subcategory='".$field_names["subcategory"]."',item='".$field_names["item"]."',content='".$content_value."' where id=$id");
}elseif ($_GET["mode"] == "delete") {
sqlInsert("delete from form_CAMOS where id=$id and date(date) = date(now())");
sqlInsert("delete from forms where form_name='CAMOS' and form_id=$id and pid='".$_SESSION["pid"]."' and date(date) = date(now())");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
