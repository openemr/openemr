<?php

/**
 * trusted-messages-ajax.php takes data from the POST/GET request, validates the data and then sends a message via the
 * Direct protocol to the trusted email address.  Results / errors are returned via JSON
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../ccr/transmitCCD.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\PatientService;
use OpenEMR\Common\Acl\AccessDeniedException;

$result = ['success' => false];

// TODO: should we put these mappings into list options so people can configure them?  More versatile, but could fail
$mimeTypeMappings = [
    'application/pdf' => 'pdf'
    ,'text/xml' => 'xml'
    ,'application/xml' => 'xml'
];

$csrf = $_REQUEST['csrf_token_form'] ?? null;
$verifyMessageReceived = false;
if (!CsrfUtils::verifyCsrfToken($csrf)) {
    $result['errorCode'] = 'invalidCsrf';
    $isValid = false;
} else {
    $document = null;

    try {
        $pid = $_REQUEST['pid'] ?? null;

        if (empty($pid) || intval($pid) <= 0) {
            throw new InvalidArgumentException("pid is required and must be a valid patient id");
        }
        $patientService = new PatientService();
        $patient = $patientService->findByPid($pid);
        if (empty($patient)) {
            throw new InvalidArgumentException("pid is required and must be a valid patient id");
        }

        $requested_by = $_SESSION['authUser'];
        if (empty($requested_by)) {
            throw new AccessDeniedException("patients", "demo", "authUser was missing and could not validate sender");
        }

        $recipient = $_REQUEST['trusted_email'] ?? null;
        if (empty($recipient)) {
            throw new InvalidArgumentException("trusted_email is required and must be a valid Direct email address");
        }

        $documentId = $_REQUEST['documentId'] ?? null;
        $message = $_REQUEST['message'] ?? '';
        if ((empty($documentId) || intval($documentId) <= 0) && empty($message)) {
            throw new InvalidArgumentException("document_id is required if no message is sent and must be a valid document id");
        }

        if (!empty($documentId) && intval($documentId) > 0) {
            // now we need to lookup the document
            $document = new \Document($documentId);

            // make sure the user can access this document
            if (!$document->can_access()) {
                throw new AccessDeniedException("patients", "demo", "Access to patient data is denied");
            }
        }
        $verifyMessageReceived = intval($_REQUEST['verifyMessageReceived'] ?? 0) == 1;
        $isValid = true;
    } catch (AccessDeniedException $exception) {
        http_response_code(401);
        $result['errorCode'] = 'permissionDenied';
        $isValid = false;
        (new SystemLogger())->error("Access was denied", ['trace' => $error->getTraceAsString(), 'message' => $error->getMessage()]);
    } catch (\Exception $error) {
        $result['errorCode'] = 'invalidRequest';
        (new SystemLogger())->error("Data was invalid", ['trace' => $error->getTraceAsString(), 'message' => $error->getMessage()]);
        $isValid = false;
    }
}

if ($isValid) {
    try {
        if (empty($document)) {
            $transmitResult = transmitMessage($message, $recipient, $verifyMessageReceived);
        } else {
            $mimeType = $document->get_mimetype();
            $formatType = 'xml';
            $xmlType = "CDA";

            if (isset($mimeTypeMappings[$mimeType])) {
                $formatType = $mimeTypeMappings[$mimeType];
            } else {
                throw new \InvalidArgumentException("Invalid mime type " . $mimeType);
            }

            $dataToSend = $document->get_data();
            // use the filename that exists in the document for what is sent
            $fileName = $document->get_name();

            $transmitResult = transmitCCD($pid, $dataToSend, $recipient, $requested_by, $xmlType, $formatType, $message, $fileName, $verifyMessageReceived);
        }
        if ($transmitResult !== "SUCCESS") {
            $result['errorCode'] = 'directError';
            $result['errorMessage'] = $transmitResult;
        } else {
            $result['success'] = true;
        }
    } catch (\InvalidArgumentException $error) {
        (new SystemLogger())->error(
            "trusted-messages-ajax.php received an invalid document mime type",
            ['trace' => $error->getTraceAsString(), 'message' => $error->getMessage(), 'pid' => $pid
                , 'document' => $documentId, 'requestor' => $requested_by, 'recipient' => $recipient
            ]
        );
        $result['errorCode'] = 'invalidDocumentFormat';
    } catch (\Exception $error) {
        (new SystemLogger())->error(
            "trusted-messages-ajax.php threw an exception when attempting to send",
            ['trace' => $error->getTraceAsString(), 'message' => $error->getMessage(), 'pid' => $pid
                , 'document' => $documentId, 'requestor' => $requested_by, 'recipient' => $recipient
            ]
        );
        $result['errorCode'] = 'serverError';
    }
}

try {
    (new SystemLogger())->debug("trusted-messages-ajax.php result object", $result);
    echo json_encode($result, JSON_THROW_ON_ERROR);
} catch (\Exception $error) {
    (new SystemLogger())->error("Failed to encode json response", ['trace' => $error->getTraceAsString(), 'message' => $error->getMessage(), 'result' => $result]);
    http_response_code(500);
}
exit;
