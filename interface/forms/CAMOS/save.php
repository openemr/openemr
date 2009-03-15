<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/forms.inc");
include_once("../../../library/sql.inc");
include_once("./content_parser.php");

if ($_GET["mode"] == "delete") {
  foreach($_POST as $key => $val) {
    if (substr($key,0,3) == 'ch_' and $val='on') {
      $id = substr($key,3); 
      if ($_POST['delete']) {
        sqlInsert("delete from form_CAMOS where id=$id");
        sqlInsert("delete from forms where form_name like 'CAMOS%' and form_id=$id");
      }
      if ($_POST['update']) {
        sqlInsert("update form_CAMOS set content='".$_POST['textarea_'.$id]."' where id=$id");
      }
    }
  }

}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
