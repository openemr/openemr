<?php

/**
 * Script to display results for a given procedure order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../globals.php');
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");

use Mpdf\Mpdf;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Pdf\Config_Mpdf;

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'med');
if (!$thisauth) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Order Results")]);
    exit;
}

$orderid = intval($_GET['orderid']);

$finals_only = empty($_POST['form_showall']);

if (!empty($_POST['form_sign']) && !empty($_POST['form_sign_list'])) {
    if (!AclMain::aclCheckCore('patients', 'sign')) {
        die(xlt('Not authorized to sign results'));
    }

  // When signing results we are careful to sign only those reports that were
  // in the sending form. While this will usually be all the reports linked to
  // the order it's possible for a new report to come in while viewing these,
  // and it would be very bad to sign results that nobody has seen!
    $arrSign = explode(',', $_POST['form_sign_list']);
    foreach ($arrSign as $id) {
        sqlStatement("UPDATE procedure_report SET " .
        "review_status = 'reviewed' WHERE " .
        "procedure_report_id = ?", array($id));
    }
    if ($orderid) {
        sqlStatement("UPDATE procedure_order SET " .
            "order_status = 'complete' WHERE " .
            "procedure_order_id = ?", array($orderid));
    }
}

// This mess generates a PDF report and sends it to the patient.
if (!empty($_POST['form_send_to_portal'])) {
  // Borrowing the general strategy here from custom_report.php.
  // See also: http://wiki.spipu.net/doku.php?id=html2pdf:en:v3:output
    require_once($GLOBALS["include_root"] . "/cmsportal/portal.inc.php");
    $config_mpdf = Config_Mpdf::getConfigMpdf();
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
    echo "<link rel='stylesheet' type='text/css' href='$webserver_root/interface/themes/style_pdf.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='$webserver_root/library/ESign/css/esign_report.css'>\n";
    $GLOBALS['PATIENT_REPORT_ACTIVE'] = true;
    generate_order_report($orderid, false, true, $finals_only);
    $GLOBALS['PATIENT_REPORT_ACTIVE'] = false;
  // echo ob_get_clean(); exit(); // debugging
    $pdf->writeHTML(ob_get_clean());
    $contents = $pdf->Output('', true);
  // Send message with PDF as attachment.
    $result = cms_portal_call(array(
    'action'   => 'putmessage',
    'user'     => $_POST['form_send_to_portal'],
    'title'    => xl('Your Lab Results'),
    'message'  => xl('Please see the attached PDF.'),
    'filename' => 'results.pdf',
    'mimetype' => 'application/pdf',
    'contents' => base64_encode($contents),
    ));
    if ($result['errmsg']) {
        die(text($result['errmsg']));
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

<script src="../../library/topdialog.js"></script>
<script>
    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

</head>
<body>
<?php if (empty($_POST['form_sign'])) {
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
    let stayHere = './single_order_results.php?orderid=' + <?php echo js_escape($orderid); ?>;
    window.location.assign(stayHere);
</script>
<?php } ?>
</body>
</html>
