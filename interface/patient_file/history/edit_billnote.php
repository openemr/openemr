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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$feid = $_GET['feid'] + 0; // id from form_encounter table

$info_msg = "";

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    die(xlt('Not authorized'));
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body>
    <?php
    if (!empty($_POST['form_submit']) || !empty($_POST['form_cancel'])) {
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

        echo "<script>\n";
        echo "dlgclose();";
        echo "</script></body></html>\n";

        exit();
    }

    $tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
    " WHERE id = ?", array($feid));
    $fenote = $tmp['billing_note'];
    ?>

    <div class="container">
        <h2><?php echo xlt('Billing Note'); ?></h2>
        <form method='post' action='edit_billnote.php?feid=<?php echo attr_url($feid); ?>' onsubmit='return top.restoreSession()'>
            <div class="form-group">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <textarea class='form-control' name='form_note'><?php echo text($fenote); ?></textarea>
            </div>
            <div class="form-group">
                <div class="btn-group btn-group-sm mt-3">
                    <button type='submit' class='btn btn-primary btn-save btn-sm' name='form_submit' value='<?php echo xla('Save'); ?>'>
                        <?php echo xlt('Save'); ?>
                    </button>
                    <button type='submit' class='btn btn-secondary btn-cancel btn-sm' name='form_cancel' value='<?php echo xla('Cancel'); ?>'>
                        <?php echo xla('Cancel'); ?>
                    </button>
                </div>
            </div>    
        </form>
    </div>
</body>
</html>
