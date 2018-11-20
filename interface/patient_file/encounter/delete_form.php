<?php
/**
 * This script delete an Encounter form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");

// allow a custom 'delete' form
$deleteform = $incdir . "/forms/" . $_REQUEST["formname"]."/delete.php";

check_file_dir_name($_REQUEST["formname"]);

if (file_exists($deleteform)) {
    include_once($deleteform);
    exit;
}

// if no custom 'delete' form, then use a generic one

// when the Cancel button is pressed, where do we go?
$returnurl = 'forms.php';

if ($_POST['confirm']) {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }

    if ($_POST['id'] != "*" && $_POST['id'] != '') {
      // set the deleted flag of the indicated form
        $sql = "update forms set deleted=1 where id=?";
        sqlInsert($sql, array($_POST['id']));
      // Delete the visit's "source=visit" attributes that are not used by any other form.
        sqlStatement(
            "DELETE FROM shared_attributes WHERE " .
            "pid = ? AND encounter = ? AND field_id NOT IN (" .
            "SELECT lo.field_id FROM forms AS f, layout_options AS lo WHERE " .
            "f.pid = ? AND f.encounter = ? AND f.formdir LIKE 'LBF%' AND " .
            "f.deleted = 0 AND " .
            "lo.form_id = f.formdir AND lo.source = 'E' AND lo.uor > 0)",
            array($pid, $encounter, $pid, $encounter)
        );
    }
    // log the event
    newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Form ".$_POST['formname']." deleted from Encounter ".$_POST['encounter']);

    // redirect back to the encounter
    $address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$address';</script>\n";
    exit;
}
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/manual-added-packages/jquery-min-1-2-2/index.js"></script>

</head>

<body class="body_top">

<span class="title"><?php echo xlt('Delete Encounter Form'); ?></span>

<form method="post" action="<?php echo $rootdir;?>/patient_file/encounter/delete_form.php" name="my_form" id="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />

<?php
// output each GET variable as a hidden form input
foreach ($_GET as $key => $value) {
    echo '<input type="hidden" id="'.attr($key).'" name="'.attr($key).'" value="'.attr($value).'"/>'."\n";
}
?>
<input type="hidden" id="confirm" name="confirm" value="1"/>
<p>
<?php echo xlt('You are about to delete the following form from this encounter') . ': ' . text(xl_form_title($_GET['formname'])); ?>
</p>
<input type="button" id="confirmbtn" name="confirmbtn" value='<?php echo xla('Yes, Delete this form'); ?>'>
<input type="button" id="cancel" name="cancel" value='<?php echo xla('Cancel'); ?>'>
</form>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#confirmbtn").click(function() { return ConfirmDelete(); });
    $("#cancel").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
});

function ConfirmDelete() {
    if (confirm(<?php echo xlj('This action cannot be undone. Are you sure you wish to delete this form?'); ?>)) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}

</script>

</html>
