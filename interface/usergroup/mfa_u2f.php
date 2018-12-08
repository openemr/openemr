<?php
/**
 * FIDO U2F Support Module
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

// https is required, and with a proxy the server might not see it.
$scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
$appId = $scheme . $_SERVER['HTTP_HOST'];
$u2f = new u2flib_server\U2F($appId);

$userid = $_SESSION['authId'];
$action = $_REQUEST['action'];
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('U2F Registration'); ?></title>
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
<script>

function doregister() {
  var f = document.forms[0];
  if (f.form_name.value.trim() == '') {
    alert(<?php echo xlj("Please enter a name for this key."); ?>);
    return;
  }
  var request = JSON.parse(f.form_request.value);
  u2f.register(
    <?php echo js_escape($appId); ?>,
    [request],
    [],
    function(data) {
      if(data.errorCode && data.errorCode != 0) {
        alert(<?php echo xlj("Registration failed with error"); ?> + ' ' + data.errorCode);
        return;
      }
      f.form_registration.value = JSON.stringify(data);
      f.action.value = 'reg2';
      top.restoreSession();
      f.submit();
    },
    60
  );
}

function docancel() {
  window.location.href = 'mfa_registrations.php';
}

</script>
</head>
<body class="body_top">
<form method='post' action='mfa_u2f.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />

<?php

///////////////////////////////////////////////////////////////////////

if ($action == 'reg1') {
    list ($request, $signs) = $u2f->getRegisterData();
    ?>
<div class="container">
  <div class="row">
    <div class="col-xs-12">
      <div class="page-header">
        <h3><?php echo xlt('Register U2F Key'); ?></h3>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      <p>
        <?php echo xlt('This will register a new U2F USB key.'); ?>
        <?php echo xlt('Type a name for your key, insert it into a USB port and click the Register button below.'); ?>
        <?php echo xlt('Then press the flashing button on your key within 1 minute to complete registration.'); ?>
      </p>
      <table><tr><td>
        <?php echo xlt('Please give this key a name'); ?>:
      <input type='text' name='form_name' value='' size='16' />&nbsp;</td>
      <td><input type='button' value='<?php echo xla('Register'); ?>' onclick='doregister()' />
      <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='docancel()'   />
      <input type='hidden' name='form_request' value='<?php echo attr(json_encode($request)); ?>' />
      <input type='hidden' name='form_signs'   value='<?php echo attr(json_encode($signs)); ?>' />
      <input type='hidden' name='form_registration' value='' />
      </td></tr></table>
      &nbsp;<br />
      <p>
        <?php echo xlt('A secure (HTTPS) web connection is required for U2F. Firefox and Chrome are known to work.'); ?>
      </p>
      <p>
        <?php echo xlt('For U2F support on Linux see'); ?>:
      <a href='https://www.key-id.com/enable-fido-u2f-linux/' target='_blank'>
      https://www.key-id.com/enable-fido-u2f-linux/</a>
      </p>
      <p>
        <?php echo xlt('For Firefox see'); ?>:
      <a href='https://www.trishtech.com/2018/07/enable-fido-u2f-security-key-yubikey-in-mozilla-firefox/' target='_blank'>
      https://www.trishtech.com/2018/07/enable-fido-u2f-security-key-yubikey-in-mozilla-firefox/</a>
      </p>
    </div>
  </div>
</div>
    <?php
} else if ($action == 'reg2') {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
    try {
        $data = $u2f->doRegister(json_decode($_POST['form_request']), json_decode($_POST['form_registration']));
    } catch (u2flib_server\Error $e) {
        die(xlt('Registration error') . ': ' . text($e->getMessage()));
    }
    echo "<script>\n";
    $row = sqlQuery(
        "SELECT COUNT(*) AS count FROM login_mfa_registrations WHERE " .
        "`user_id` = ? AND `name` = ?",
        array($userid, $_POST['form_name'])
    );
    if (empty($row['count'])) {
        sqlStatement(
            "INSERT INTO login_mfa_registrations " .
            "(`user_id`, `method`, `name`, `var1`, `var2`) VALUES " .
            "(?, 'U2F', ?, ?, ?)",
            array($userid, $_POST['form_name'], json_encode($data), '')
        );
    } else {
        echo " alert(" . xlj('This key name is already in use by you. Try again.') . ");\n";
    }
    echo " window.location.href = 'mfa_registrations.php';\n";
    echo "</script>\n";
}

///////////////////////////////////////////////////////////////////////

?>

<input type='hidden' name='action' value='' />
</form>
</body>
</html>
