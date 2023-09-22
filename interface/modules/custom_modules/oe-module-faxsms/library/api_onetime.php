<?php

/**
 * Portal OneTime for API
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientPortalService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'contact-form')) {
    CsrfUtils::csrfNotVerified();
}

if (isset($_REQUEST['sendOneTime'])) {
    try {
        $rtn = doOnetimeDocumentRequest();
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

/**
 * @throws Exception
 */
function doOnetimeDocumentRequest()
{
    $service = new PatientPortalService();
    // auto allow if a portal user else must be an admin
    if (!$service::isPortalUser()) {
        // default is admin documents
        if (!$service::verifyAcl()) {
            throw new Exception(xlt("Error! Not authorised. You must be an authorised portal user or admin."));
        }
    }
    $details = json_decode($service->getRequest('details'), true);
    $content = $service->getRequest('comments');
    $ot_pid = $details['pid'] ?? $service->getRequest('pid');
    if (!empty($ot_pid)) {
        $patient = $service->getPatientDetails($ot_pid);
    } else {
        throw new Exception(xlt("Error! Missing patient id."));
    }
    $data = [
        'pid' => $details['pid'] ?? 0,
        'onetime_period' => $details['onetime_period'] ?? 'PT60M',
        'notification_template_name' => $details['notification_template_name'] ?? '',
        'document_id' => $details['id'] ?? 0,
        'audit_id' => $details['audit_id'] ?? 0,
        'document_name' => $details['template_name'] ?? '',
        'notification_method' => $service->getRequest('notification_method', 'both'),
        'phone' => $patient['phone'] ?? '',
        'email' => $patient['email'] ?? '',
        'onetime' => $details['onetime'] ?? 0
    ];
    try {
        $rtn = $service->dispatchPortalOneTimeDocumentRequest($ot_pid, $data, $content);
    } catch (Exception $e) {
        die($e->getMessage());
    }
    echo js_escape($rtn);
}
