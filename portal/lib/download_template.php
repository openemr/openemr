<?php

/**
 * Document Template Rendering front end.
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2023-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateRender;
use OpenEMR\Core\OEGlobalsBag;

// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
$session = SessionWrapperFactory::getInstance()->getWrapper();

$is_module = $_POST['isModule'] ?? 0;
if ($is_module) {
    require_once(__DIR__ . '/../../interface/globals.php');
} else {
    require_once(__DIR__ . "/../verify_session.php");
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['pid'])) {
        if ($_POST['pid'] != $session->get('pid')) {
            echo xlt("illegal Action");
            SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
}

$form_id = $_POST['template_id'] ?? null;
$pid = $_POST['pid'] ?? 0;
$user = $session->get('authUserID') ?? $session->get('sessionUser'); // $_SESSION['sessionUser'] is '-patient-'
$prepared_doc = xlt("Error! Missing template or template unavailable.");
if (!empty($form_id)) {
    $templateRender = new DocumentTemplateRender($pid, $user);
    $prepared_doc = $templateRender->doRender($form_id, null, null);

    if (!$prepared_doc) {
        throw new RuntimeException(xlt("Fetch failed in download template. No content to render in template render."));
    }
// add a version to template
    if (stripos($prepared_doc, 'portal_version') === false) {
        $prepared_doc .= "<input style='display: none;' id='portal_version' name='portal_version' type='hidden' value='New' />\n";
    }
}
echo $prepared_doc;
exit;
