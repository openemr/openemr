<?php

/**
 * doc_lib.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Pdf\PatientPortalPDFDocumentCreator;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewType;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globalsBag = OEGlobalsBag::getInstance();

if (!empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['cpid'])) {
        if ($_POST['cpid'] != $session->get('pid')) {
            echo "illegal Action";
            SessionWrapperFactory::getInstance()->destroyPortalSession();
            exit;
        }
    }
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
    // only support download handler from patient portal
    if ($_POST['handler'] != 'download' && $_POST['handler'] != 'fetch_pdf') {
        AccessDeniedHelper::deny(
            'Unauthorized handler in patient portal document library',
            beforeExit: SessionWrapperFactory::getInstance()->destroyPortalSession(...),
        );
    }
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!$session->has('authUserID')) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
}
require_once(__DIR__ . "/appsql.class.php");


// portal doesn't need to be enabled to chart from documents
if (!$globalsBag->getBoolean('portal_onsite_two_enable')) {
    $msg = xlt('Patient Portal is turned off');
    error_log($msg);
    echo $msg;
}
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$logit = new ApplicationTable();
$htmlin = $_POST['content'] ?? null;
$dispose = $_POST['handler'] ?? null;
$cpid = $_POST['cpid'] ?: $globalsBag->get('pid');
$category = $_POST['catid'] ?? 0;


if ($dispose == $_POST['audit_delete'] ?? null) {
    // Staff remove a row from the review list: its review request is cancelled, and for a
    // document the un-charted document is removed with it, in one transaction. The list sends
    // the row's tableArgs as delete_id, which is the document id for a document and invoice or
    // profile JSON otherwise, so it is read as a string and only checked for documents.
    // (Portal sessions never reach here; they are limited to the download handlers above.)
    $postBody = CurrentRequest::get()->request;
    $requestId = filter_var($postBody->getString('update_id'), FILTER_VALIDATE_INT);
    $request = is_int($requestId) && $requestId > 0 ? ReviewQueueFactory::create()->find($requestId) : null;
    if (
        $request === null
        || !in_array($request->type, PortalReviewWorkflows::REVIEW_TYPES, true)
        || !$request->status->isOpen()
        || ($request->type === ReviewType::Document && $request->targetId !== $postBody->getString('delete_id'))
    ) {
        echo js_escape(['success' => false, 'message' => xl('Failed Action')]);
        exit;
    }
    try {
        // Throws if the request was closed since the list loaded; nothing is deleted then.
        ReviewQueueFactory::portalWorkflows()->discard($request, SessionReviewActor::staff($session), xl('User deleted audit'));
        echo js_escape(['success' => true]);
    } catch (\Throwable $e) {
        ServiceContainer::getLogger()->error('Could not remove a portal review request', ['request_id' => $request->id, 'exception' => $e]);
        echo js_escape(['success' => false, 'message' => xl('Failed to update or delete record.')]);
    }
    exit;
}

try {
    if (!$category) {
        $result = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", ["Reviewed"]);
        $category = $result['id'] ?: 3;
    }
    $form_filename = convert_safe_file_dir_name($_REQUEST['docid']) . '_' . convert_safe_file_dir_name($cpid) . '.pdf';
    $len = stripos((string) $htmlin, 'data:application/pdf;base64,');
    if ($len !== false) {
        if ($dispose == "download") {
            //'<object data=data:application/pdf;base64,'
            $len = strpos((string) $htmlin, ',');
            $content = substr((string) $htmlin, $len + 1);
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
            $file = base64_encode((string) $file);
            echo $file;
            $logit->portalLog('fetched PDF', $cpid, ('document:' . $form_filename));
            exit;
        } catch (\Throwable $e) {
            die(text($e->getMessage()));
        }
    }
} catch (\Throwable $e) {
    die(text($e->getMessage()));
}
