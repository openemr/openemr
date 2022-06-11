<?php

/**
 * validate_messages_document_ajax.php is an AJAX rest api for retrieving a validation report for a given document.
 * The user must have permissions to the specific document in order to run the validation process. This file can return
 * results in both html or json format.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");
require_once("$srcdir/pid.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\Cda\CdaValidateDocumentObject;
use OpenEMR\Common\Logging\SystemLogger;

$format = $_GET['format'] ?? "html";
$format = in_array($format, ['json', 'html']) ? $format : "html";

try {
    $twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf"])) {
        http_response_code(403);
        CsrfUtils::csrfNotVerified(true, true, false);
        echo $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]);
        exit;
    }


    if (!AclMain::aclCheckCore('patients', 'notes')) {
        http_response_code(403);
        echo $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]);
        exit;
    }

    if (empty($_GET['doc'])) {
        http_response_code(400);
        echo $twig->render('error/400.' . $format . '.twig', ['errorMessage' => xl("Missing document id")]);
        exit;
    }

    $docId = intval($_GET['doc']);
    $document = new Document($docId);
    if ($document->get_size() <= 0) {
        // doc not found
        http_response_code(404);
        echo $twig->render('error/404.' . $format . '.twig', ['errorMessage' => xl("Missing document id")]);
        exit;
    }
    if (!$document->can_access($docId)) {
        http_response_code(403);
        echo $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]);
        exit;
    }

    // now we can validate our documents
    $cdaDocumentValidator = new CdaValidateDocumentObject();
    $validationErrors = $cdaDocumentValidator->getValidationErrorsForDocument($document);
    if (!empty($validationErrors)) {
        echo $twig->render('carecoordination/cda/cda-validate-results.' . $format . '.twig', ['document' => $document, 'validation' => $validationErrors]);
    } else {
        echo xlt("No errors found, Document(s) passed Import Validation");
    }
} catch (Exception $exception) {
    (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    if (isset($twig)) {
        http_response_code(500);
        $twig->render('error/general_http_error', ['statusCode' => 500]);
        exit;
    } else {
        echo xlt("Server error occured. Check logs for details");
    }
}
