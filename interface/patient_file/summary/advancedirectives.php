<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/options.inc.php");
include_once("$srcdir/formdata.inc.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
<SCRIPT LANGUAGE="JavaScript">
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
function validate(f) {
if (f.form_adreviewed.value == "")
{
	alert("<?php xl('Please enter a date for Last Reviewed.','e'); ?>");
	f.form_adreviewed.focus();
	return false;
}
 return true;
}
$(document).ready(function(){
    $("#cancel").click(function() { window.close(); });
});
</script>
</head>

<body class="body_top">
<?php
if ($_POST['form_yesno'])
{
	sqlQuery("UPDATE patient_data SET completed_ad='".formData('form_yesno','P',true)."', ad_reviewed='".formData('form_adreviewed','P',true)."' where pid='$pid'");
  // Close this window and refresh the calendar display.
  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
  echo " window.close();\n";
  echo "</script>\n</body>\n</html>\n";
  exit();
}
	$sql = "select completed_ad, ad_reviewed from patient_data where pid='$pid'";
	$myrow = sqlQuery($sql);
if ($myrow)
{
	$form_completedad = $myrow['completed_ad'];
	$form_adreviewed = $myrow['ad_reviewed'];
}
?>
<span class="title"><?php xl('Advance Directives','e'); ?></span>
<br><br>
<form action='advancedirectives.php' method='post' onsubmit='return validate(this)' enctype="multipart/form-data">
      <table border=0 cellpadding=1 cellspacing=1>
      <?php
	echo "<tr><td class='required'>";
	xl('Completed','e');
	echo ":</td><td width=10></td><td class='text'>";
	generate_form_field(array('data_type'=>1,'field_id'=>'yesno','list_id'=>'yesno','empty_title'=>'SKIP'), $form_completedad);
	echo "</td></tr><tr><td class='required'>";
	xl('Last Reviewed','e');
	echo ":</td><td width=10></td><td class='text'>";
        generate_form_field(array('data_type'=>4,'field_id'=>'adreviewed'), $form_adreviewed);
        echo "<script language='JavaScript'>Calendar.setup({inputField:'form_adreviewed', ifFormat:'%Y-%m-%d', button:'img_adreviewed'});</script>";
	echo "</td></tr>";
	echo "<tr><td class=text colspan=2><br><input type=submit id=create value='" . xl('Save') . "' /> &nbsp; <input type=button id=cancel value='" . xl('Cancel') . "' /></td></tr>";
      ?>
      </table></form>
<div>
<?php
$query = "SELECT id FROM categories WHERE name='Advance Directive'";
$myrow2 = sqlQuery($query);
if ($myrow2) {
        $parentId = $myrow2['id'];
        $query = "SELECT id, name FROM categories WHERE parent='$parentId'";
        $resNew1 = sqlStatement($query);
        while ($myrows3 = sqlFetchArray($resNew1)) {
            $categoryId = $myrows3['id'];
            $nameDoc = $myrows3['name'];
            $query = "SELECT documents.date, documents.id " .
                     "FROM documents " .
                     "INNER JOIN categories_to_documents " .
                     "ON categories_to_documents.document_id=documents.id " .
	             "WHERE categories_to_documents.category_id='$categoryId' " .
	             "AND documents.foreign_id='$pid' " .
                     "ORDER BY documents.date DESC";
            $resNew2 = sqlStatement($query);
	    $counterFlag = false; //flag used to check for empty categories
            while ($myrows4 = sqlFetchArray($resNew2)) {
                $dateTimeDoc = $myrows4['date'];
                $idDoc = $myrows4['id'];
                echo "<br>";
                echo "<a href='$web_root/controller.php?document&retrieve&patient_id=$pid&document_id=".$idDoc."&as_file=true'>".xl_document_category($nameDoc)."</a> ".$dateTimeDoc;
		$counterFlag = true;
            }
	    // if no associated docs with category then show it's empty
	    if (!$counterFlag) {
	        echo "<br>";
		echo $nameDoc . " <span style='color:red;'>[" . xl('EMPTY') . "]</span>";
	    }
        }
}
?>
</div>
  </body>
</html>
