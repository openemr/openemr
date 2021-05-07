<?php

/**
 * Ajax endpoint for interface/billing/billing_tracker.php,
 * which is the interface that provides tracking information for a claim batch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../interface/globals.php";

use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;
use OpenEMR\Common\Csrf\CsrfUtils;

// verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$remoteTracker = new X12RemoteTracker();
$claim_files = $remoteTracker->fetchAll();
$response = new stdClass();
$response->data = [];
foreach ($claim_files as $claim_file) {
    $element = new stdClass();
    $element->x12_partner_id = text($claim_file['x12_partner_id']);
    $element->x12_partner_name = text($claim_file['name']);
    $element->x12_filename = text($claim_file['x12_filename']);
    $element->status = xl($claim_file['status']);
    $element->created_at = oeFormatDateTime($claim_file['created_at']);
    $element->updated_at = oeFormatDateTime($claim_file['updated_at']);
    $element->claims = json_decode($claim_file['claims']);
    $element->messages = json_decode($claim_file['messages']);
    $response->data[] = $element;
}

echo json_encode($response);
exit();
