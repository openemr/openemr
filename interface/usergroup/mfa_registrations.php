<?php
/**
 * Multi-Factor Authentication Management
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */


require_once('../globals.php');

use OpenEMR\Core\Header;

function writeRow($method, $name)
{
    echo "        <tr><td>&nbsp;";
    echo text($method);
    echo "&nbsp;</td><td>&nbsp;";
    echo text($name);
    echo "&nbsp;</td><td>";
    echo "<input type='button' onclick='delclick(" . attr_js($method) . ", " .
        attr_js($name) . ")' value='" . xla('Delete') . "' />";
    echo "</td></tr>\n";
}

$userid = $_SESSION['authId'];

$message = '';
if (!empty($_POST['form_delete_method'])) {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
    // Delete the indicated MFA instance.
    sqlStatement(
        "DELETE FROM login_mfa_registrations WHERE user_id = ? AND method = ? AND name = ?",
        array($userid, $_POST['form_delete_method'], $_POST['form_delete_name'])
    );
    $message = xl('Delete successful.');
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Manage Multi Factor Authentication'); ?></title>
<script>

function delclick(mfamethod, mfaname) {
    var f = document.forms[0];
    f.form_delete_method.value = mfamethod;
    f.form_delete_name.value = mfaname;
    top.restoreSession();
    f.submit();
}

function addclick(sel) {
    top.restoreSession();
    if (sel.value) {
        if (sel.value == 'U2F') {
            window.location.href = 'mfa_u2f.php?action=reg1';
        }
        else {
            alert(<?php echo xlj('Not yet implemented.'); ?>);
        }
    }
    sel.selectedIndex = 0;
}

</script>
</head>
<body class="body_top">
<form method='post' action='mfa_registrations.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />

<div class="container">
  <div class="row">
    <div class="col-xs-12">
      <div class="page-header">
        <h3><?php echo xlt('Manage Multi Factor Authentication'); ?></h3>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      <div id="display_msg"><?php echo text($message); ?></div>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      <table>
        <tr>
          <th align='left'>&nbsp;<?php echo xlt('Method'); ?>&nbsp;</th>
          <th align='left'>&nbsp;<?php echo xlt('Key Name'); ?>&nbsp;</th>
          <th align='left'>&nbsp;<?php echo xlt('Action'); ?>&nbsp;</th>
        </tr>
<?php
$res = sqlStatement("SELECT name, method FROM login_mfa_registrations WHERE " .
    "user_id = ? ORDER BY method, name", array($userid));
while ($row = sqlFetchArray($res)) {
    writeRow($row['method'], $row['name']);
}
?>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      &nbsp;<br />
      <select name='form_add' onchange='addclick(this)'>
        <option value=''><?php echo xlt('Add New...'); ?></option>
        <option value='U2F' ><?php echo xlt('U2F USB Device'); ?></option>
        <option value='TOTP' disabled><?php echo xlt('TOTP Key'); ?></option>
      </select>
      <input type='hidden' name='form_delete_method' value='' />
      <input type='hidden' name='form_delete_name' value='' />
    </div>
  </div>
</div>

</form>
</body>
</html>
