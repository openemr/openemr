<?php

/**
 * display.php  Is responsible for display a CCR/CCD/CCDA document previewed from the documents folder.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Ajil P.M <ajilpm@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @deprecated 7.0.0 People should use the /interface/modules/zend_modules/public/encountermanager/previewDocument?docId=<id> REST action instead of this file
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <ajilpm@zhservices.com>
 * @copyright Copyright (c) 2013 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../interface/globals.php");

use OpenEMR\Events\PatientDocuments\PatientDocumentViewCCDAEvent;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\SystemLogger;

$type = $_GET['type'];
$document_id = $_GET['doc_id'];
$d = new Document($document_id);


try {
    $twig = new TwigContainer(null, $GLOBALS['kernel']);
    // can_access will check session if no params are passed.
    if (!$d->can_access()) {
        echo $twig->getTwig()->render("templates/error/400.html.twig", ['statusCode' => 401, 'errorMessage' => 'Access Denied']);
        exit;
    } elseif ($d->is_deleted()) {
        echo $twig->getTwig()->render("templates/error/404.html.twig");
        exit;
    }

    $xml = $d->get_data();
    if (empty($xml)) {
        echo $twig->getTwig()->render("templates/error/404.html.twig");
        exit;
    }

    $viewCCDAEvent = new PatientDocumentViewCCDAEvent();
    $viewCCDAEvent->setCcdaType($type); // not sure if we want to send the old CCR... but I guess module authors can use it if they want
    if (!empty($d->get_foreign_reference_id())) {
        $viewCCDAEvent->setCcdaId($d->get_foreign_reference_id());
    }

    $viewCCDAEvent->setDocumentId($d->get_id());
    $viewCCDAEvent->setContent($d->get_data());
    $viewCCDAEvent->setFormat("html");

    $updatedViewCCDAEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch($viewCCDAEvent, PatientDocumentViewCCDAEvent::EVENT_NAME);

    $content = $updatedViewCCDAEvent->getContent();
    if (empty($content)) {
        // TODO: @adunsulag log the security error as someone is trying to do a remote file inclusion
        echo $twig->getTwig()->render("templates/error/general_http_error.html.twig", ['statusCode' => 500, 'errorMessage' => 'System error occurred in processing content']);
        exit;
    }
    echo $updatedViewCCDAEvent->getContent($content);
} catch (\Exception $exception) {
    (new SystemLogger())->errorLogCaller(
        "Failed to generate ccda for view",
        ['type' => $type, 'document_id' => $document_id, 'message' => $exception, 'trace' => $exception->getTraceAsString()]
    );
}
