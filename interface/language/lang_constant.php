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

use OpenEMR\Common\Csrf\CsrfUtils;

// Ensure this script is not called separately
if ((empty($_SESSION['lang_module_unique_id'])) ||
    (empty($unique_id)) ||
    ($unique_id != $_SESSION['lang_module_unique_id'])) {
    die(xlt('Authentication Error'));
}
unset($_SESSION['lang_module_unique_id']);

// gacl control
$thisauth = acl_check('admin', 'language');
if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

if ($_POST['add']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //validate
    if ($_POST['constant_name'] == "") {
            echo xlt('Constant name is blank').'<br>';
            $err='y';
    }

    $sql="SELECT * FROM lang_constants WHERE constant_name=? limit 1" ;
    $res=SqlQuery($sql, array($_POST['constant_name']));
    if ($res) {
        echo xlt('Data Alike is already in database, please change constant name').'<br>';
        $err='y';
    }

    if ($err=='y') {
        $val_constant=$_POST['constant_name'];
    } else {
            //insert into the main table
        $sql="INSERT INTO lang_constants SET constant_name=?";
        SqlStatement($sql, array($_POST['constant_name']));

                //insert into the log table - to allow persistant customizations
            insert_language_log('', '', $_POST['constant_name'], '');

        echo xlt('Constant') . ' ' . text($_POST['constant_name']) . ' ' . xlt('added') . '<br>';
    }



// echo "$sql here ";
}

?>

<TABLE>
<FORM name="cons_form" METHOD=POST ACTION="?m=constant&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<TR>
    <TD><?php echo xlt('constant name'); ?></TD>
    <TD><INPUT TYPE="text" NAME="constant_name" size="100" value="<?php echo attr($val_constant); ?>"></TD>
</TR>
<TR>
    <TD></TD>
    <TD><INPUT TYPE="submit" name="add" value="<?php echo xla('Add'); ?>"></TD>
</TR>
</FORM>
</TABLE>
<span class="text"><?php echo xlt('Please Note: constants are case sensitive and any string is allowed.'); ?></span>
