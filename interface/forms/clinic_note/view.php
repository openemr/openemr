<?php

/**
 * clinic_note view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$row = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname)
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        $tmp = '0';
    }

    return "$tmp";
}

function cbvalue($cbname)
{
    return $_POST[$cbname] ? '1' : '0';
}

function rbinput($name, $value, $desc, $colname)
{
    global $row;
    $ret  = "<input type='radio' name='" . attr($name) . "' value='" . attr($value) . "'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />" . text($desc);
    return $ret;
}

function rbcell($name, $value, $desc, $colname)
{
    return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname)
{
    global $row;
    $ret  = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }

    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname)
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . text($desc) . "</td>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if (!empty($_POST['bn_save'])) {
    $fu_timing = $_POST['fu_timing'];
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

 // If updating an existing form...
 //
    if ($formid) {
        $query = "UPDATE form_clinic_note SET
         history = ?,
         examination = ?,
         plan = ?,
         followup_required = ?,
         followup_timing = ?
         WHERE id = ?";

        sqlStatement($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'], rbvalue('fu_required'), $fu_timing, $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_clinic_note ( " .
         "history, examination, plan, followup_required, followup_timing
         ) VALUES ( ?, ?, ?, ?, ? )";

        $newid = sqlInsert($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'], rbvalue('fu_required'), $fu_timing));
        addForm($encounter, "Clinic Note", $newid, "clinic_note", $pid, $userauthorized);
    }

    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

if ($formid) {
    $row = sqlQuery("SELECT * FROM form_clinic_note WHERE " .
    "id = ? AND activity = '1'", array($formid)) ;
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body <?php echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/clinic_note/new.php?id=<?php echo attr_url($formid); ?>"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b><?php echo xlt("This Encounter"); ?></b></td>
 </tr>

 <tr>
  <td width='5%'  nowrap> <?php echo xlt("History"); ?> </td>
  <td width='95%' nowrap>
   <textarea name='form_history' rows='7' style='width:100%'><?php echo text($row['history']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> <?php echo xlt("Examination"); ?> </td>
  <td nowrap>
   <textarea name='form_examination' rows='7' style='width:100%'><?php echo text($row['examination']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> <?php echo xlt("Plan"); ?> </td>
  <td nowrap>
   <textarea name='form_plan' rows='7' style='width:100%'><?php echo text($row['plan']) ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap><?php echo xlt("Follow Up"); ?></td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='5%' nowrap>
        <?php echo rbinput('fu_required', '1', xl('Required in') . ':', 'followup_required') ?>
     </td>
     <td nowrap>
      <input type='text' name='fu_timing' size='10' style='width:100%'
       title='<?php echo xla("When to follow up"); ?>'
       value='<?php echo attr($row['followup_timing']) ?>' />
     </td>
    </tr>
    <tr>
     <td colspan='2' nowrap>
        <?php echo rbinput('fu_required', '2', xl('Pending investigation'), 'followup_required') ?>
     </td>
    </tr>
    <tr>
     <td colspan='2' nowrap>
        <?php echo rbinput('fu_required', '0', xl('None required'), 'followup_required') ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='<?php echo xla("Save"); ?>' />
&nbsp;
<input type='button' value='<?php echo xla("Cancel"); ?>' onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
</body>
</html>
