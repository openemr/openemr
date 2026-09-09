<?php

/**
 * validate_messages_document_ajax.php is an AJAX rest api for retrieving a validation report for a given document.
 * The user must have permissions to the specific document in order to run the validation process. This file can return
 * results in both html or json format.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\Cda\CdaValidateDocumentObject;
use Symfony\Component\HttpFoundation\Response;

$format = $_GET['format'] ?? "html";
$format = in_array($format, ['json', 'html']) ? $format : "html";

$twig = null;
try {
    $twig = ServiceContainer::getTwig();
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf"], session: $session)) {
        CsrfUtils::csrfNotVerified(toScreen: false, beforeExit: static function () use ($twig, $format): void {
            echo $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]);
        });
    }


    if (!AclMain::aclCheckCore('patients', 'notes')) {
        (new RequestTerminator())->respond(new Response(
            $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]),
            Response::HTTP_FORBIDDEN,
        ));
    }

    if (empty($_GET['doc'])) {
        (new RequestTerminator())->respond(new Response(
            $twig->render('error/400.' . $format . '.twig', ['errorMessage' => xl("Missing document id")]),
            Response::HTTP_BAD_REQUEST,
        ));
    }

    $docId = intval($_GET['doc']);
    $document = new Document($docId);
    if ($document->get_size() <= 0) {
        // doc not found
        (new RequestTerminator())->respond(new Response(
            $twig->render('error/404.' . $format . '.twig', ['errorMessage' => xl("Missing document id")]),
            Response::HTTP_NOT_FOUND,
        ));
    }
    if (!$document->can_access($docId)) {
        (new RequestTerminator())->respond(new Response(
            $twig->render('core/unauthorized.' . $format . '.twig', ['pageTitle' => xl("Validate Message Documents")]),
            Response::HTTP_FORBIDDEN,
        ));
    }

    // now we can validate our documents
    $cdaDocumentValidator = new CdaValidateDocumentObject();
    $validationErrors = $cdaDocumentValidator->getValidationErrorsForDocument($document);
    if (!empty($validationErrors)) {
        echo $twig->render('carecoordination/cda/cda-validate-results.' . $format . '.twig', ['document' => $document, 'validation' => $validationErrors]);
    } else {
        echo xlt("No errors found, Document(s) passed Import Validation");
    }
} catch (\Throwable $exception) {
    ServiceContainer::getLogger()->error($exception->getMessage(), ['exception' => $exception]);
    $display = $twig?->render('error/general_http_error', ['statusCode' => 500])
        ?? xlt("Server error occurred. Check logs for details");

    (new RequestTerminator())->respond(new Response(
        $display,
        Response::HTTP_INTERNAL_SERVER_ERROR,
    ));
}
