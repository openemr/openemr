<?php

/**
 * lang_language.php script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
    $pat = "^[a-z]{2}\$";
    if (!check_pattern($_POST['lang_code'], $pat)) {
        echo xlt("Code must be two letter lowercase") . '<br />';
        $err = 'y';
    }

    $sql = "SELECT * FROM lang_languages WHERE lang_code LIKE ? or lang_description LIKE ? limit 1" ;
    $res = SqlQuery($sql, array("%" . $_POST['lang_code'] . "%","%" . $_POST['lang_name']));
    if ($res) {
        echo xlt("Data Alike is already in database, please change code and/or description") . '<br />';
        $err = 'y';
    }

    if (!empty($err) && ($err == 'y')) {
        $val_lang_code = $_POST['lang_code'];
        $val_lang_name = $_POST['lang_name'];
    } else {
            //insert into the main table
        $sql = "INSERT INTO lang_languages SET lang_code=?, lang_description=?";
        SqlStatement($sql, array($_POST['lang_code'],$_POST['lang_name']));

        //insert into the log table - to allow persistant customizations
        insert_language_log($_POST['lang_name'], $_POST['lang_code'], '', '');

            echo xlt('Language definition added') . '<br />';
    }
}

?>

<form name="lang_form" method="post" action="?m=language&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <!-- Language Code -->
    <div class="form-group">
        <label for="languageCode"><?php  echo xlt('Language Code'); ?>:</label>
        <input type="text" class="form-control" id="languageCode" name="lang_code" size="2" maxlength="2" value="<?php echo attr($val_lang_code ?? ''); ?>">
    </div>
    <!-- Language Name -->
    <div class="form-group">
        <label for="languageName"><?php  echo xlt('Language Name'); ?>:</label>
        <input type="text" class="form-control" id="languageName" name="lang_name" size="24" value="<?php echo attr($val_lang_name ?? ''); ?>">
    </div>
    <!-- Submit Button -->
    <div class="form-group">
        <input type="submit" class="btn btn-primary" name="add" value="<?php echo xla('Add'); ?>">
    </div>
</form>

<script>
    $("#language-link").addClass("active");
    $("#definition-link").removeClass("active");
    $("#constant-link").removeClass("active");
    $("#manage-link").removeClass("active");
</script>
