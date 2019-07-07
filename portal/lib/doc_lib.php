<?php
/**
 * doc_lib.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    if (! isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: '.$landingpage);
        exit;
    }
}

require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once(dirname(__FILE__)."/appsql.class.php");

use Mpdf\Mpdf;

$logit = new ApplicationTable();
$htmlin = $_REQUEST['content'];
$dispose = $_POST['handler'];
$cpid = $_REQUEST['cpid'] ? $_REQUEST['cpid'] : $GLOBALS['pid'];

try {
    $result = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array("Reviewed"));
    $category = $result['id'] ? $result['id'] : 3;
    $form_filename = convert_safe_file_dir_name($_REQUEST['docid']) . '_' . convert_safe_file_dir_name($cpid) . '.pdf';
    $templatedir = $GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/patient_documents";
    $templatepath = "$templatedir/$form_filename";
    $htmlout = '';
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => $GLOBALS['pdf_size'],
        'default_font_size' => '9',
        'default_font' => 'dejavusans',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => $GLOBALS['pdf_layout'],
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'autoScriptToLang' => true,
        'keep_table_proportions' => true
    );
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    $pdf->writeHtml($htmlin);
    if ($dispose == 'download') {
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename=$form_filename');
        $pdf->Output($form_filename, 'D');
        $logit->portalLog('download document', $cpid, ('document:'.$form_filename));
    }

    if ($dispose == 'view') {
        Header("Content-type: application/pdf");
        $pdf->Output($templatepath, 'I');
    }

    if ($dispose == 'chart') {
        $data = $pdf->Output($form_filename, 'S');
        ob_start();
        $d = new Document();

        if (!$cpid) {
            echo xla("ERROR Missing Patient ID");
            exit();
        }
        $rc = $d->createDocument($cpid, $category, $form_filename, 'application/pdf', $data);
        ob_clean();
        echo $rc;
        $logit->portalLog('chart document', $cpid, ('document:'.$form_filename));

        exit(0);
    };
} catch (Exception $e) {
    echo 'Message: ' .$e->getMessage();
    die(xlt("no signature in document"));
}

// not currently used but meant to be.
function doc_toDoc($htmlin)
{
    header("Content-type: application/vnd.oasis.opendocument.text");
    header("Content-Disposition: attachment;Filename=document_name.html");
    echo "<html>";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
    echo "<body>";
    echo $htmlin;
    echo "</body>";
    echo "</html>";
    ob_clean();
    flush();
    readfile($fname);
};
