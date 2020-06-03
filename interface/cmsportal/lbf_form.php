<?php

/**
 * LBF form handling for the WordPress Patient Portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("portal.inc.php");

use OpenEMR\Core\Header;

$postid = intval($_REQUEST['postid']);

// Get the portal request data.
if (!$postid) {
    die(xlt('Request ID is missing!'));
}

$result = cms_portal_call(array('action' => 'getpost', 'postid' => $postid));
if ($result['errmsg']) {
    die(text($result['errmsg']));
}

// Look up the patient in OpenEMR.
$ptid = lookup_openemr_patient($result['post']['user']);
?>
<html>
<head>
<?php Header::setupHeader('datetime-picker'); ?>

<style>
tr.head {
  font-size: 0.8125rem;
  background-color: var(--gray400);
  text-align: center;
}

tr.detail {
  font-size: 0.8125rem;
  background-color: var(--gray300);
}

td input {
  background-color: transparent;
}
</style>

<script>

function myRestoreSession() {
 if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
 return true;
}

function validate() {
 var f = document.forms[0];
 // TBD
 return true;
}

function openPatient() {
 myRestoreSession();
 opener.top.RTop.document.location.href = '../patient_file/summary/demographics.php?set_pid=<?php echo attr($ptid); ?>';
}

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
</head>

<body class="body_top">

<?php echo "<!-- ";
print_r($result);
echo " -->\n"; // debugging ?>

<center>

<form method='post' action='lbf_form.php' onsubmit='return validate()'>

<table class='w-100' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <th align='left'><?php echo xlt('Field'); ?></th>
  <th align='left'><?php echo xlt('Value'); ?></th>
 </tr>

<?php
foreach ($result['fields'] as $field_id => $newvalue) {
    if (is_array($newvalue)) {
        $tmp = '';
        foreach ($newvalue as $value) {
            if ($tmp !== '') {
                $tmp .= ', ';
            }

            $tmp .= $value;
        }

        $newvalue = $tmp;
    }

    $newvalue = trim($newvalue);
    $field_title = $result['labels'][$field_id];
    echo " <tr class='detail'>\n";
    echo "  <td class='font-weight-bold'>" . text($field_title) . "</td>\n";
    echo "  <td>";
    echo text($newvalue);
    echo "</td>\n";
    echo " </tr>\n";
}
?>

</table>

<div class='btn-group'>
<input type='button' class='btn btn-primary' value='<?php echo xla('Open Patient'); ?>' onclick="openPatient()" />
<input type='button' class='btn btn-secondary' value='<?php echo xla('Back'); ?>' onclick="myRestoreSession();location='list_requests.php'" />
</div>

</form>
</center>
</body>
</html>
