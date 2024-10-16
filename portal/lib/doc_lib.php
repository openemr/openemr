<?php

/**
 * doc_lib.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Pdf\PatientPortalPDFDocumentCreator;

// portal doesn't need to be enabled to chart from documents
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    $msg = xlt('Patient Portal is turned off');
    error_log($msg);
    echo $msg;
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


if ($dispose == $_POST['audit_delete'] ?? null) {
    if (!empty($_POST['delete_id']) && !empty($_POST['update_id'])) {
        $deleteId = intval($_POST['delete_id']);
        $updateId = intval($_POST['update_id']);
        // Start a transaction
        QueryUtils::startTransaction();
        try {
            // Update table_action to 'delete' and narrative to user deleted msg
            $msg = xl("User deleted document");
            $updateActivity = sqlQuery("UPDATE onsite_portal_activity SET action_taken_time = NOW(), table_action = 'delete', status = 'deleted', narrative = ? WHERE id = ?", [$msg, $updateId]);
            $deleteDocument = sqlQuery("DELETE FROM onsite_documents WHERE id = ?", [$deleteId]);

            if (!$updateActivity && !$deleteDocument) {
                // Commit the transaction if both successful
                QueryUtils::commitTransaction();
                echo js_escape(['success' => true]);
            } else {
                // Rollback transaction on failure
                QueryUtils::rollbackTransaction();
                ;
                echo js_escape(['success' => false, 'message' => 'Failed to update or delete record.']);
            }
        } catch (Exception $e) {
            // Rollback on error
            QueryUtils::rollbackTransaction();
            echo js_escape(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo js_escape(['success' => false, 'message' => 'Failed Action']);
    }
    exit;
}

try {
    if (!$category) {
        $result = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array("Reviewed"));
        $category = $result['id'] ?: 3;
    }
    $form_filename = convert_safe_file_dir_name($_REQUEST['docid']) . '_' . convert_safe_file_dir_name($cpid) . '.pdf';
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
    $pdfCreator = new PatientPortalPDFDocumentCreator();
    $pdfObject = $pdfCreator->createPdfObject($htmlin);
    if ($dispose == 'download') {
        header('Content-type: application/pdf');
        header("Content-Disposition: attachment; filename=$form_filename");
        $pdfObject->Output($form_filename, 'D');
        $logit->portalLog('download document', $cpid, ('document:' . $form_filename));
        exit();
    }

    if ($dispose == 'chart') {
        if (!$cpid) {
            echo js_escape("ERROR " . xla("Missing Patient ID"));
            exit();
        }
        $data = $pdfObject->Output($form_filename, 'S');
        $d = new Document();
        $rc = $d->createDocument($cpid, $category, $form_filename, 'application/pdf', $data);
        $logit->portalLog('chart document', $cpid, ('document:' . $form_filename));
        exit();
    }

    if ($dispose == 'fetch_pdf') {
        try {
            $file = $pdfObject->Output($form_filename, 'S');
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
