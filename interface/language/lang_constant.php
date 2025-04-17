<?php

/**
 * lang_constant.php script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("language.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Ensure this script is not called separately
if ($langModuleFlag !== true) {
    die(function_exists('xlt') ? xlt('Authentication Error') : 'Authentication Error');
}

// gacl control
$thisauth = AclMain::aclCheckCore('admin', 'language');
if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

if (!empty($_POST['add'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //validate
    if ($_POST['constant_name'] == "") {
            echo xlt('Constant name is blank') . '<br />';
            $err = 'y';
    }

    $sql = "SELECT * FROM lang_constants WHERE constant_name=? limit 1" ;
    $res = SqlQuery($sql, array($_POST['constant_name']));
    if ($res) {
        echo xlt('Data Alike is already in database, please change constant name') . '<br />';
        $err = 'y';
    }

    if (!empty($err) && ($err == 'y')) {
        $val_constant = $_POST['constant_name'];
    } else {
            //insert into the main table
        $sql = "INSERT INTO lang_constants SET constant_name=?";
        SqlStatement($sql, array($_POST['constant_name']));

                //insert into the log table - to allow persistant customizations
            insert_language_log('', '', $_POST['constant_name'], '');

        echo xlt('Constant') . ' ' . text($_POST['constant_name']) . ' ' . xlt('added') . '<br />';
    }



// echo "$sql here ";
}

?>

<form name="cons_form" method="post" action="?m=constant&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <!-- Constant Name -->
    <div class="form-group">
        <label for="constantName"><?php  echo xlt('Constant Name'); ?>:</label>
        <input type="text" class="form-control" id="constantName" name="constant_name" size="100" value="<?php echo attr($val_constant ?? ''); ?>">
    </div>
    <!-- Submit Button -->
    <div class="form-group">
        <input type="submit" class="btn btn-primary" name="add" value="<?php echo xla('Add'); ?>">
    </div>
</form>

<span class="text"><?php echo xlt('Please Note: constants are case sensitive and any string is allowed.'); ?></span>

<?php echo activate_lang_tab('constant-link'); ?>
