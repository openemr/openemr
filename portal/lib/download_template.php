<?php

/**
 * Document Template Rendering front end.
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

$is_module = $_POST['isModule'] ?? 0;
if ($is_module) {
    require_once(dirname(__file__) . '/../../interface/globals.php');
} else {
    require_once(dirname(__file__) . "/../verify_session.php");
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['pid'])) {
        if ($_POST['pid'] != $_SESSION['pid']) {
            echo xlt("illegal Action");
            OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
}

use OpenEMR\Services\DocumentTemplates\DocumentTemplateRender;

$form_id = $_POST['template_id'] ?? null;
$pid = $_POST['pid'] ?? 0;
$user = $_SESSION['authUserID'] ?? $_SESSION['sessionUser']; // $_SESSION['sessionUser'] is '-patient-'
$prepared_doc = xlt("Error! Missing template or template unavailable.");
if (!empty($form_id)) {
    $templateRender = new DocumentTemplateRender($pid, $user);
    $prepared_doc = $templateRender->doRender($form_id, null, null);

    if (!$prepared_doc) {
        throw new RuntimeException(xlt("Fetch failed in download template. No content to render in template render."));
    }
// add a version to template
    if (stripos($prepared_doc, 'portal_version') === false) {
        $prepared_doc = $prepared_doc . "<input style='display: none;' id='portal_version' name='portal_version' type='hidden' value='New' />\n";
    }
}
echo $prepared_doc;
exit;
