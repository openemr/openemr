<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("../../../library/forms.inc");
include_once("../../../library/sql.inc");
include_once("./content_parser.php");
include_once("../../../library/formdata.inc.php");

if ($_GET["mode"] == "delete") {
  foreach($_POST as $key => $val) {
    if (substr($key,0,3) == 'ch_' and $val='on') {
      $id = substr($key,3); 
      if ($_POST['delete']) {
        sqlInsert("delete from form_CAMOS where id=$id");
        sqlInsert("delete from forms where form_name like 'CAMOS%' and form_id=$id");
      }
      if ($_POST['update']) {
        // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
        //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
        //   before being submitted to the database. Will also continue to support placeholder conversion on report
        //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
        //   version 4.0).
        $content = strip_escape_custom( $_POST['textarea_'.${id}] );
        $content = add_escape_custom( replace($pid,$encounter,$content) );
        sqlInsert("update form_CAMOS set content='$content' where id=$id");
      }
    }
  }

}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
