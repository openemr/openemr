<?php

/**
 * doc_lib.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['cpid'])) {
        if ($_POST['cpid'] != $_SESSION['pid']) {
            echo "illegal Action";
            OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    // only support download handler from patient portal
    if ($_POST['handler'] != 'download' && $_POST['handler'] != 'fetch_pdf') {
        echo xlt("Not authorized");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        exit;
    }
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
}

require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once(__DIR__ . "/appsql.class.php");

use Mpdf\Mpdf;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}
// confirm csrf (from both portal and core)
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'doc-lib')) {
    CsrfUtils::csrfNotVerified();
}

$logit = new ApplicationTable();
$htmlin = $_POST['content'] ?? null;
$dispose = $_POST['handler'] ?? null;
$cpid = $_POST['cpid'] ?: $GLOBALS['pid'];
$category = $_POST['catid'] ?? 0;

try {
    if (!$category) {
        $result = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array("Reviewed"));
        $category = $result['id'] ?: 3;
    }
    $form_filename = convert_safe_file_dir_name($_REQUEST['docid']) . '_' . convert_safe_file_dir_name($cpid) . '.pdf';
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
    $len = stripos($htmlin, 'data:application/pdf;base64,');
    if ($len !== false) {
        if ($dispose == "download") {
            //'<object data=data:application/pdf;base64,'
            $len = strpos($htmlin, ',');
            $content = substr($htmlin, $len + 1);
            $content = str_replace("type='application/pdf' width='100%' height='450'></object>", '', $content);

            $pdf = base64_decode($content);
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $form_filename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($pdf));
            ob_clean();
            flush();
            echo $pdf;
            flush();
            exit();
        }
    }

    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }

    // snatch style tags content to insert after content purified
    $style_flag = preg_match('#<\s*?style\b[^>]*>(.*?)</style\b[^>]*>#s', $htmlin, $style_matches);
    $style = str_replace('<style type="text/css">', '<style>', $style_matches);
    $pos = stripos($htmlin, "<style>");
    $pos1 = stripos($htmlin, "</style>");

    // purify html
    $config = HTMLPurifier_Config::createDefault();
    $config->set('URI.AllowedSchemes', array('data' => true, 'http' => true, 'https' => true));
    $purify = new \HTMLPurifier($config);
    $htmlin = $purify->purify($htmlin);
    // need to create custom stylesheet for templates
    // also our styles_pdf.scss isn't being compiled!!!
    // replace existing style tag in template after purifies removes! why!!!
    // e,g this scheme gets removed <html><head><body> etc
    $stylesheet = "<style>.signature {vertical-align: middle;max-height:65px; height:65px !important;width:auto !important;}</style>";
    if ($pos !== false && $pos1 !== false && !empty($style[0] ?? '')) {
        $stylesheet = str_replace('</style>', $stylesheet, $style[0]);
    }
    $htmlin = "<!DOCTYPE html><html><head>" . $stylesheet . "</head><body>$htmlin</body></html>";
    $pdf->writeHtml($htmlin);

    if ($dispose == 'download') {
        header('Content-type: application/pdf');
        header("Content-Disposition: attachment; filename=$form_filename");
        $pdf->Output($form_filename, 'D');
        $logit->portalLog('download document', $cpid, ('document:' . $form_filename));
        exit();
    }

    if ($dispose == 'chart') {
        if (!$cpid) {
            echo js_escape("ERROR " . xla("Missing Patient ID"));
            exit();
        }
        $data = $pdf->Output($form_filename, 'S');
        $d = new Document();
        $rc = $d->createDocument($cpid, $category, $form_filename, 'application/pdf', $data);
        $logit->portalLog('chart document', $cpid, ('document:' . $form_filename));
        exit();
    }

    if ($dispose == 'fetch_pdf') {
        try {
            $file = $pdf->Output($form_filename, 'S');
            $file = base64_encode($file);
            echo $file;
            $logit->portalLog('fetched PDF', $cpid, ('document:' . $form_filename));
            exit;
        } catch (Exception $e) {
            die(text($e->getMessage()));
        }
    }
} catch (Exception $e) {
    die(text($e->getMessage()));
}
