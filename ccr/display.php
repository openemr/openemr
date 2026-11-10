<?php

/**
 * display.php  Is responsible for display a CCR/CCD/CCDA document previewed from the documents folder.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Ajil P.M <ajilpm@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @deprecated 7.0.0 People should use the /interface/modules/zend_modules/public/encountermanager/previewDocument?docId=<id> REST action instead of this file
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <ajilpm@zhservices.com>
 * @copyright Copyright (c) 2013 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../interface/globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientDocuments\PatientDocumentViewCCDAEvent;
use Symfony\Component\HttpFoundation\Response;

$type = $_GET['type'];
$document_id = $_GET['doc_id'];
$d = new Document($document_id);

$ccrDisplaySession = SessionWrapperFactory::getInstance()->getActiveSession();
$ccrDisplaySessionPid = (int) ($ccrDisplaySession->get('pid') ?? 0);
$ccrDisplayDocPid = (int) $d->get_foreign_id();
$ccrDisplayPatientAuthorized = false;
if ($ccrDisplayDocPid > 0) {
    if ($ccrDisplaySessionPid > 0) {
        $ccrDisplayPatientAuthorized = $ccrDisplaySessionPid === $ccrDisplayDocPid;
    } else {
        $ccrDisplayPatientAuthorized = AclMain::aclCheckCore('patients', 'docs') !== false;
    }
}

try {
    $twig = ServiceContainer::getTwig();
    // The script runs in a legacy entry point whose output may already have
    // been buffered by the time RequestTerminator wants to set a status.
    // Emit the HTTP status with http_response_code() before any rendering so
    // the deny path reaches the wire with the intended code instead of a 200.
    // can_access will check session if no params are passed.
    if (!$d->can_access() || !$ccrDisplayPatientAuthorized) {
        http_response_code(Response::HTTP_UNAUTHORIZED);
        (new RequestTerminator())->respond(new Response(
            $twig->render("error/400.html.twig", ['statusCode' => 401, 'errorMessage' => 'Access Denied']),
            Response::HTTP_UNAUTHORIZED,
        ));
    } elseif ($d->is_deleted()) {
        http_response_code(Response::HTTP_NOT_FOUND);
        (new RequestTerminator())->respond(new Response(
            $twig->render("error/404.html.twig"),
            Response::HTTP_NOT_FOUND,
        ));
    }

    $xml = $d->get_data();
    if (empty($xml)) {
        http_response_code(Response::HTTP_NOT_FOUND);
        (new RequestTerminator())->respond(new Response(
            $twig->render("error/404.html.twig"),
            Response::HTTP_NOT_FOUND,
        ));
    }

    $viewCCDAEvent = new PatientDocumentViewCCDAEvent();
    $viewCCDAEvent->setCcdaType($type); // not sure if we want to send the old CCR... but I guess module authors can use it if they want
    if (!empty($d->get_foreign_reference_id())) {
        $viewCCDAEvent->setCcdaId($d->get_foreign_reference_id());
    }

    $viewCCDAEvent->setDocumentId($d->get_id());
    $viewCCDAEvent->setContent($d->get_data());
    $viewCCDAEvent->setFormat("html");

    $updatedViewCCDAEvent = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch($viewCCDAEvent, PatientDocumentViewCCDAEvent::EVENT_NAME);

    $content = $updatedViewCCDAEvent->getContent();
    if (empty($content)) {
        // TODO: @adunsulag log the security error as someone is trying to do a remote file inclusion
        (new RequestTerminator())->respond(new Response(
            $twig->render("error/general_http_error.html.twig", ['statusCode' => 500, 'errorMessage' => 'System error occurred in processing content']),
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ));
    }
    echo $updatedViewCCDAEvent->getContent();
} catch (\Throwable $exception) {
    ServiceContainer::getLogger()->error(
        "Failed to generate ccda for view",
        ['exception' => $exception, 'type' => $type, 'document_id' => $document_id]
    );
}
