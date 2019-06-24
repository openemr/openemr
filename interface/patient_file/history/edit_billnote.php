<?php
/**
 * Billing notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/acl.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

$feid = $_GET['feid'] + 0; // id from form_encounter table

$info_msg = "";

if (!acl_check('acct', 'bill', '', 'write')) {
    die(xlt('Not authorized'));
}
?>
<html>
<head>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<style>
</style>

</head>

<body>
<?php
if ($_POST['form_submit'] || $_POST['form_cancel']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $fenote = trim($_POST['form_note']);
    if ($_POST['form_submit']) {
        sqlStatement("UPDATE form_encounter " .
        "SET billing_note = ? WHERE id = ?", array($fenote,$feid));
    } else {
        $tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
        " WHERE id = ?", array($feid));
        $fenote = $tmp['billing_note'];
    }

  // escape and format note for viewing
    $fenote = $fenote;
    $fenote = str_replace("\r\n", "<br />", $fenote);
    $fenote = str_replace("\n", "<br />", $fenote);
    if (! $fenote) {
        $fenote = '['. xl('Add') . ']';
    }

    echo "<script language='JavaScript'>\n";
    echo " parent.closeNote(" . js_escape($feid) . ", " . js_escape($fenote) . ")\n";
    echo "</script></body></html>\n";
    exit();
}

$tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
  " WHERE id = ?", array($feid));
$fenote = $tmp['billing_note'];
?>

<form method='post' action='edit_billnote.php?feid=<?php echo attr_url($feid); ?>' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>
<textarea name='form_note' style='width:100%'><?php echo text($fenote); ?></textarea>
<p>
<input type='submit' name='form_submit' value='<?php echo xla('Save'); ?>' />
&nbsp;&nbsp;
<input type='submit' name='form_cancel' value='<?php echo xla('Cancel'); ?>' />
</center>
</form>
</body>
</html>
