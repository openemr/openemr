<?php

/**
 * physical_exam new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("lines.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

$returnurl = 'encounter_top.php';

function showExamLine($line_id, $description, &$linedbrow, $sysnamedisp)
{
    $dres = sqlStatement("SELECT * FROM form_physical_exam_diagnoses " .
    "WHERE line_id = ? ORDER BY ordering, diagnosis", array($line_id));

    echo " <tr>\n";
    echo "  <td align='center'><input type='checkbox' name='form_obs[" . attr($line_id) . "][wnl]' " .
    "value='1'" . ($linedbrow['wnl'] ? " checked" : "") . " /></td>\n";
    echo "  <td align='center'><input type='checkbox' name='form_obs[" . attr($line_id) . "][abn]' " .
    "value='1'" . ($linedbrow['abn'] ? " checked" : "") . " /></td>\n";
    echo "  <td nowrap>" . text($sysnamedisp) . "</td>\n";
    echo "  <td nowrap>" . text($description) . "</td>\n";

    echo "  <td><select name='form_obs[" . attr($line_id) . "][diagnosis]' onchange='seldiag(this, " . attr_js($line_id) . ")' style='width:100%'>\n";
    echo "   <option value=''></option>\n";
    $diagnosis = $linedbrow['diagnosis'];
    while ($drow = sqlFetchArray($dres)) {
        $sel = '';
        $diag = $drow['diagnosis'];
        if ($diagnosis && $diag == $diagnosis) {
            $sel = 'selected';
            $diagnosis = '';
        }

        echo "   <option value='" . attr($diag) . "' $sel>" . text($diag) . "</option>\n";
    }

 // If the diagnosis was not in the standard list then it must have been
 // there before and then removed.  In that case show it in parentheses.
    if ($diagnosis) {
        echo "   <option value='" . attr($diagnosis) . "' selected>(" . text($diagnosis) . ")</option>\n";
    }

    echo "   <option value='*'>-- Edit --</option>\n";
    echo "   </select></td>\n";

    echo "  <td><input type='text' name='form_obs[" . attr($line_id) . "][comments]' " .
    "size='20' maxlength='250' style='width:100%' " .
    "value='" . attr($linedbrow['comments']) . "' /></td>\n";
    echo " </tr>\n";
}

function showTreatmentLine($line_id, $description, &$linedbrow)
{
    echo " <tr>\n";
    echo "  <td align='center'><input type='checkbox' name='form_obs[" . attr($line_id) . "][wnl]' " .
    "value='1'" . ($linedbrow['wnl'] ? " checked" : "") . " /></td>\n";
    echo "  <td></td>\n";
    echo "  <td colspan='2' nowrap>" . text($description) . "</td>\n";
    echo "  <td colspan='2'><input type='text' name='form_obs[" . attr($line_id) . "][comments]' " .
    "size='20' maxlength='250' style='width:100%' " .
    "value='" . attr($linedbrow['comments']) . "' /></td>\n";
    echo " </tr>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
 // We are to update/insert multiple table rows for the form.
 // Each has 2 checkboxes, a dropdown and a text input field.
 // Skip rows that have no entries.
 // There are also 3 special rows with just one checkbox and a text
 // input field.  Maybe also a diagnosis line, not clear.
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($formid) {
        $query = "DELETE FROM form_physical_exam WHERE forms_id = ?";
        sqlStatement($query, array($formid));
    } else {
        $formid = addForm($encounter, "Physical Exam", 0, "physical_exam", $pid, $userauthorized);
        $query = "UPDATE forms SET form_id = id WHERE id = ? AND form_id = 0";
        sqlStatement($query, array($formid));
    }

    $form_obs = $_POST['form_obs'];
    foreach ($form_obs as $line_id => $line_array) {
        $wnl = $line_array['wnl'] ? '1' : '0';
        $abn = $line_array['abn'] ? '1' : '0';
        $diagnosis = $line_array['diagnosis'] ? $line_array['diagnosis'] : '';
        $comments  = $line_array['comments']  ? $line_array['comments'] : '';
        if ($wnl || $abn || $diagnosis || $comments) {
            $query = "INSERT INTO form_physical_exam (
             forms_id, line_id, wnl, abn, diagnosis, comments
             ) VALUES (
             ?, ?, ?, ?, ?, ?
             )";
            sqlStatement($query, array($formid, $line_id, $wnl, $abn, $diagnosis, $comments));
        }
    }

    if (! $_POST['form_refresh']) {
        formHeader("Redirecting....");
        formJump();
        formFooter();
        exit;
    }
}

// Load all existing rows for this form as a hash keyed on line_id.
//
$rows = array();
if ($formid) {
    $res = sqlStatement("SELECT * FROM form_physical_exam WHERE forms_id = ?", array($formid));
    while ($row = sqlFetchArray($res)) {
        $rows[$row['line_id']] = $row;
    }
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<script>

 function seldiag(selobj, line_id) {
  var i = selobj.selectedIndex;
  var opt = selobj.options[i];
  if (opt.value == '*') {
   selobj.selectedIndex = 0;
   dlgopen('../../forms/physical_exam/edit_diagnoses.php?lineid=' + encodeURIComponent(line_id), '_blank', 500, 400);
  }
 }

 function refreshme() {
  top.restoreSession();
  var f = document.forms[0];
  f.form_refresh.value = '1';
  f.submit();
 }

</script>
</head>

<body class="body_top">
<form method="post" action="<?php echo $rootdir ?>/forms/physical_exam/new.php?id=<?php echo attr_url($formid); ?>"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table border='0' width='98%'>

 <tr>
  <td align='center' width='1%' nowrap><b><?php echo xlt('WNL'); ?></b></td>
  <td align='center' width='1%' nowrap><b><?php echo xlt('ABNL'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php echo xlt('System'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php echo xlt('Specific'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php echo xlt('Diagnosis'); ?></b></td>
  <td align='left'  width='95%' nowrap><b><?php echo xlt('Comments'); ?></b></td>
 </tr>

<?php
foreach ($pelines as $sysname => $sysarray) {
    $sysnamedisp = $sysname;
    if ($sysname == '*') {
       // TBD: Show any remaining entries in $rows (should not be any).
        echo " <tr><td colspan='6'>\n";
        echo "   &nbsp;<br /><b>" . xlt('Treatment:') . "</b>\n";
        echo " </td></tr>\n";
    } else {
        $sysnamedisp = xl($sysname);
    }

    foreach ($sysarray as $line_id => $description) {
        if ($sysname != '*') {
            showExamLine($line_id, $description, $rows[$line_id], $sysnamedisp);
        } else {
            showTreatmentLine($line_id, $description, $rows[$line_id]);
        }

        $sysnamedisp = '';
       // TBD: Delete $rows[$line_id] if it exists.
    } // end of line
} // end of system name
?>

</table>

<p>
<input type='hidden' name='form_refresh' value='' />
<input type='submit' name='bn_save' value='<?php echo xla('Save'); ?>' />
&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>'
 onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
<?php
// TBD: If $alertmsg, display it with a JavaScript alert().
?>
</body>
</html>
