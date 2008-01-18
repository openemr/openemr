<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/forms.inc");
include_once("../../../library/sql.inc");

if ($_GET["mode"] == "delete") {
  sqlInsert("delete from form_CAMOS where id=$id and DATE_FORMAT(date,'%Y-%m-%d') like current_date()");
  sqlInsert("delete from forms where form_name like 'CAMOS%' and form_id=$id and pid='".$_SESSION["pid"]."' and DATE_FORMAT(date,'%Y-%m-%d') like current_date()");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
