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
    $rtn = doOnetimeRequest();
}

function doOnetimeRequest()
{
    $service = new PatientPortalService();
    $details = json_decode($service->getRequest('details'), true);
    $content = $service->getRequest('comments');
    $data = [
        'pid' => $details['pid'] ?? 0,
        'onetime_period' => $details['onetime_period'] ?? 'PT60M',
        'notification_template_name' => $details['notification_template_name'] ?? '',
        'document_id' => $details['id'] ?? 0,
        'audit_id' => $details['audit_id'] ?? 0,
        'document_name' => $details['template_name'] ?? '',
        'notification_method' => $service->getRequest('notification_method', 'both'),
        'phone' => $details['phone'] ?? '',
        'email' => $details['email'] ?? '',
        'onetime' => $details['onetime'] ?? 0
    ];
    try {
        $rtn = $service->dispatchPortalOneTimeDocumentRequest($data['pid'], $data, $content);
    } catch (Exception $e) {
        // todo add logging
    }
    echo $rtn;
}
