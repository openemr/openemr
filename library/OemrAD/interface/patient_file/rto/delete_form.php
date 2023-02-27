<?php
/**
 * This script delete an Encounter form.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */




include_once("../../globals.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");

// allow a custom 'delete' form
$deleteform = $incdir . "/forms/" . $_REQUEST["formname"]."/delete.php";

check_file_dir_name($_REQUEST["formname"]);

if (file_exists($deleteform)) {
    include_once($deleteform);
    exit;
}

/* OEMR - Changes */
function delete_rto_form($pid) {
    $rto_id = isset($_REQUEST['rto_id']) ? $_REQUEST['rto_id'] : '';
    $rtoData = getRtoLayoutFormData($pid, $rto_id);
    $form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
    manageAction($pid, $rto_id, $form_id);
    exit();
}
/* End */

// if no custom 'delete' form, then use a generic one

// when the Cancel button is pressed, where do we go?
$returnurl = 'forms.php';

if ($_POST['confirm']) {
    if ($_POST['id'] != "*" && $_POST['id'] != '') {
        // OEMR - Change
        delete_rto_form($pid);
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
<?php //html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"></script>

</head>

<body class="body_top">

<span class="title"><?php echo xlt('Delete Encounter Form'); ?></span>

<form method="post" action="<?php echo $rootdir;?>/patient_file/rto/delete_form.php" name="my_form" id="my_form">
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
    if (confirm('<?php echo xls('This action cannot be undone. Are you sure you wish to delete this form?'); ?>')) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}

</script>

</html>
