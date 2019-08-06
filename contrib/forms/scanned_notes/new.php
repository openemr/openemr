<?php
/**
 * Encounter form for entering clinical data as a scanned document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

$row = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

$formid = $_GET['id'];
$imagedir = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($pid) . "/encounters";

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

 // If updating an existing form...
 //
    if ($formid) {
        $query = "UPDATE form_scanned_notes SET notes = ? WHERE id = ?";
        sqlStatement($query, array($_POST['form_notes'], $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_scanned_notes (notes) VALUES (?)";
        $formid = sqlInsert($query, array($_POST['form_notes']));
        addForm($encounter, "Scanned Notes", $formid, "scanned_notes", $pid, $userauthorized);
    }

    $imagepath = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . ".jpg";

 // Upload new or replacement document.
 // Always convert it to jpeg.
    if ($_FILES['form_image']['size']) {
        // If the patient's encounter image directory does not yet exist, create it.
        if (! is_dir($imagedir)) {
            $tmp0 = exec("mkdir -p " . escapeshellarg($imagedir), $tmp1, $tmp2);
            if ($tmp2) {
                die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
            }

            exec("touch " . escapeshellarg($imagedir.'/index.html'));
        }

        // Remove any previous image files for this encounter and form ID.
        for ($i = -1; true; ++$i) {
             $suffix = ($i < 0) ? "" : "-$i";
             $path = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . check_file_dir_name($suffix) . ".jpg";
            if (is_file($path)) {
                unlink($path);
            } else {
                if ($i >= 0) {
                    break;
                }
            }
        }

        $tmp_name = $_FILES['form_image']['tmp_name'];
        // default density is 72 dpi, we change to 96.  And -append was removed
        // to create a separate image file for each page.
        $cmd = "convert -density 96 " . escapeshellarg($tmp_name) . " " . escapeshellarg($imagepath);
        $tmp0 = exec($cmd, $tmp1, $tmp2);
        if ($tmp2) {
            die("\"" . text($cmd) . "\" returned " . text($tmp2) . ": " . text($tmp0));
        }
    }

 // formHeader("Redirecting....");
 // formJump();
 // formFooter();
 // exit;
}

$imagepath = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . ".jpg";
$imageurl = "$web_root/sites/" . $_SESSION['site_id'] .
  "/documents/" . check_file_dir_name($pid) . "/encounters/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . ".jpg";

if ($formid) {
    $row = sqlQuery(
        "SELECT * FROM form_scanned_notes WHERE " .
        "id = ? AND activity = '1'",
        array($formid)
    );
    $formrow = sqlQuery(
        "SELECT id FROM forms WHERE " .
        "form_id = ? AND formdir = 'scanned_notes'",
        array($formid)
    );
}
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script language='JavaScript'>

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=' + <?php echo js_url($pid); ?>,
   '_blank', 775, 500);
  return false;
 }

 // Process click on Delete button.
 function deleteme() {
  dlgopen('../../patient_file/deleter.php?formid=' + <?php echo js_url($formrow['id']); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  top.restoreSession();
  location = '<?php echo $GLOBALS['form_exit_url']; ?>';
 }

</script>

</head>

<body class="body_top">

<form method="post" enctype="multipart/form-data"
 action="<?php echo $rootdir ?>/forms/scanned_notes/new.php?id=<?php echo attr_url($formid); ?>"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'>Scanned Encounter Notes</td>
 </tr>

 <tr>
  <td width='5%'  class='dehead' nowrap>&nbsp;Comments&nbsp;</td>
  <td width='95%' class='detail' nowrap>
   <textarea name='form_notes' rows='4' style='width:100%'><?php echo text($row['notes']); ?></textarea>
  </td>
 </tr>

 <tr>
  <td class='dehead' nowrap>&nbsp;Document&nbsp;</td>
  <td class='detail' nowrap>
<?php
if ($formid && is_file($imagepath)) {
    echo "   <img src='$imageurl' />\n";
}
?>
   <p>&nbsp;
    <?php echo xlt('Upload this file:') ?>
   <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
   <input name="form_image" type="file" />
   <br />&nbsp;</p>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Add Appointment' onclick='newEvt()' />
&nbsp;
<input type='button' value='Back' onclick="parent.closeTab(window.name, false)" />
<?php if ($formrow['id'] && acl_check('admin', 'super')) { ?>
&nbsp;
<input type='button' value='Delete' onclick='deleteme()' style='color:red' />
<?php } ?>
</p>

</center>

</form>
</body>
</html>
