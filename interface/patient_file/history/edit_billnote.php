<?php

/**
 * Billing notes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();

$feid = $request->query->getInt('feid'); // id from form_encounter table

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: Billing Note", xl("Billing Note"));
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body>
    <?php
    $submitted = $request->request->getString('form_submit') !== '';
    if ($submitted || $request->request->getString('form_cancel') !== '') {
        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

        if ($submitted) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE form_encounter SET billing_note = ? WHERE id = ?",
                [trim($request->request->getString('form_note')), $feid]
            );
        }

        echo "<script>\n";
        echo "dlgclose();";
        echo "</script></body></html>\n";

        exit();
    }

    $fenote = QueryUtils::fetchSingleValue("SELECT billing_note FROM form_encounter WHERE id = ?", 'billing_note', [$feid]);
    $fenote = is_string($fenote) ? $fenote : '';

    // Size the box to the note on the server: one row per line, plus the rows long
    // lines wrap to in the small dialog (about 35 characters wide), plus one to type
    // in. Measuring it in the browser gave 0 when the dialog wasn't laid out yet.
    $noteRows = 0;
    foreach (preg_split('/\r\n|\r|\n/', $fenote) ?: [] as $line) {
        $noteRows += max(1, (int) ceil(mb_strlen($line) / 35));
    }
    $noteRows = min(20, max(4, $noteRows + 1));
    ?>

    <div class="container">
        <h2><?php echo xlt('Billing Note'); ?></h2>
        <form method='post' action='edit_billnote.php?feid=<?php echo attr_url((string) $feid); ?>' onsubmit='return top.restoreSession()'>
            <div class="form-group">
                <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
                <textarea class='form-control' name='form_note' id='form_note' rows='<?php echo $noteRows; ?>'><?php echo text($fenote); ?></textarea>
            </div>
            <div class="form-group">
                <div class="btn-group btn-group-sm mt-3">
                    <button type='submit' class='btn btn-primary btn-save btn-sm' name='form_submit' value='<?php echo xla('Save'); ?>'>
                        <?php echo xlt('Save'); ?>
                    </button>
                    <button type='submit' class='btn btn-secondary btn-cancel btn-sm' name='form_cancel' value='<?php echo xla('Cancel'); ?>'>
                        <?php echo xlt('Cancel'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
