<?php

/**
 * Script to display results for a given procedure order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2013-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/../globals.php');
require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getIncludeRoot() . "/orders/single_order_results.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Orders\ProcedureOrderAccess;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'med');
if (!$thisauth) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for patients/med: Order Results", xl("Order Results"));
}

$request = CurrentRequest::get();
$orderid = $request->query->getInt('orderid');

// Results belong to the encounter the order was placed in, so honor that
// encounter's sensitivity the same way encounter forms do (load_form.php).
// Checked before signing as well as viewing.
ProcedureOrderAccess::assertCanViewOrder($orderid, xl('Order Results'));

$finals_only = $request->request->getString('form_showall') === '';
$signing = $request->request->getString('form_sign') !== '';
$signList = $request->request->getString('form_sign_list');

if ($signing && $signList !== '') {
    if (!AclMain::aclCheckCore('patients', 'sign')) {
        AccessDeniedHelper::deny('Not authorized to sign order results');
    }

  // When signing results we are careful to sign only those reports that were
  // in the sending form. While this will usually be all the reports linked to
  // the order it's possible for a new report to come in while viewing these,
  // and it would be very bad to sign results that nobody has seen!
    foreach (explode(',', $signList) as $id) {
        QueryUtils::sqlStatementThrowException("UPDATE procedure_report SET " .
        "review_status = 'reviewed' WHERE " .
        "procedure_report_id = ?", [$id]);
    }
    if ($orderid) {
        QueryUtils::sqlStatementThrowException("UPDATE procedure_order SET " .
            "order_status = 'complete' WHERE " .
            "procedure_order_id = ?", [$orderid]);
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
<title><?php echo xlt('Order Results'); ?></title>
<style>
body {
 margin: 9pt;
 font-family: sans-serif;
 font-size: 1em;
}
</style>

<script src="../../library/topdialog.js?v=<?php echo attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes')); ?>"></script>
<script>
    <?php require(OEGlobalsBag::getInstance()->getSrcDir() . "/restoreSession.php"); ?>
</script>

</head>
<body>
<?php if (!$signing) {
    generate_order_report($orderid, true, true, $finals_only);
} else { ?>
<script>
    if (opener.document.forms && opener.document.forms[0]) {
        // Opener should be list_reports.php. Make it refresh.
        var f = opener.document.forms[0];
        if (f.form_external_refresh) {
            f.form_external_refresh.value = '1';
            f.submit();
        }
    }
    let stayHere = './single_order_results.php?orderid=' + <?php echo js_escape((string) $orderid); ?>;
    window.location.assign(stayHere);
</script>
<?php } ?>
</body>
</html>
