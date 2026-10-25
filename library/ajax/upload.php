<?php

/**
 * Drag and Drop file uploader.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\MessageService;

// Auth if core or portal.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getActiveSession();

$isPortal = false;
if (!empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    $isPortal = true;
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
}

require_once(__DIR__ . "/../../interface/globals.php");
require_once(__DIR__ . "/../documents.php");


if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"], session: $session)) {
    CsrfUtils::csrfNotVerified();
}

// check if this is for dicom image maintenance.
$action = $_POST['action'] ?? null;
$doc_id = (int)($_POST['doc_id'] ?? null);
$json_data = $_POST['json_data'] ?? null;

if ($action === 'save' || $action === 'fetch') {
    $requiredAcl = $action === 'save'
        ? AclMain::aclCheckCore('patients', 'docs', '', ['write', 'addonly'])
        : AclMain::aclCheckCore('patients', 'docs');
    if (!$requiredAcl) {
        AccessDeniedHelper::deny("ACL check failed for patients/docs: DICOM image state");
    }
    if ($doc_id <= 0) {
        AccessDeniedHelper::deny("Invalid document id for DICOM image state");
    }
    $doc = new Document($doc_id);
    $docForeignId = $doc->get_foreign_id();
    if (
        !is_numeric($doc->get_id())
        || !is_numeric($docForeignId)
        || (int) $docForeignId <= 0
        || !$doc->can_access()
    ) {
        AccessDeniedHelper::deny("Unauthorized attempt to access document $doc_id via DICOM image state");
    }

    if ($action === 'save') {
        $pass_it = dicom_history_action($action, $doc_id, $json_data);
        if ($pass_it === 'false') {
            // query success. send back a translated message for user.
            echo xlj("Server says thanks. Images state saved.");
        } else {
            echo xlj("Error! Images state save failed.");
        }

        exit();
    }

    $json_data = dicom_history_action($action, $doc_id);
    echo $json_data;

    exit();
}
// nope! so continue on with Sherwins uploader.
$patient_id = filter_input(INPUT_GET, 'patient_id');
$category_id = filter_input(INPUT_GET, 'parent_id');

if ($isPortal ?? false) {
    // The upload box is only rendered when portal uploads are enabled, so a request while they are off is not a normal use.
    if (!OEGlobalsBag::getInstance()->getBoolean('allow_portal_uploads')) {
        AccessDeniedHelper::deny("Portal document uploads are disabled");
    }
    $owner = OEGlobalsBag::getInstance()->get('userauthorized');
    $files = getMultiple();
    // Same file types the portal upload box offers (see initFileDrop in onsitedocuments.js); the browser filter is not a safeguard.
    $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'tsv', 'ppt', 'pptx', 'odt', 'rtf'];
    $rtn = [];
    if (count($files["file"] ?? []) > 0) {
        $messageService = new MessageService();
        $data = [];
        $note['groupname'] = 'Default';
        // will send to all auth'ed portal users
        $note['to'] = 'portal-user';
        $note['from'] = 'portal-user';
        $note['message_status'] = 'New';
        $note['title'] = 'New Document';
        $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", [$category_id])['id'] ?? 3;
        foreach ($files["file"] as $file) {
            $rawName = $file['name'] ?? '';
            $name = is_string($rawName) ? $rawName : '';
            $type = $file['type'] ?? '';
            $tmp_name = $file['tmp_name'] ?? '';
            $size = $file['size'] ?? 0;
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !in_array($extension, $allowedExtensions, true)) {
                $rtn[] = ['ok' => false, 'name' => $name, 'error' => 'not_accepted'];
                continue;
            }
            $data = addNewDocument(
                $name,
                $type,
                $tmp_name,
                '',
                $size,
                $owner,
                $pid,
                $category,
                '',
                '',
                true
            );
            // addNewDocument() returns false when the document could not be saved: only a returned document id counts as saved.
            $docId = is_array($data) ? filter_var($data['doc_id'] ?? null, FILTER_VALIDATE_INT) : false;
            if (is_int($docId) && $docId > 0) {
                $rtn[] = ['ok' => true, 'name' => $name, 'doc_id' => $docId];
            } else {
                $rtn[] = ['ok' => false, 'name' => $name, 'error' => 'not_saved'];
            }
        }
        // give user a break and send just one message for multi documents, and only for documents that were saved
        $names = '';
        foreach ($rtn as $data) {
            if ($data['ok'] === true) {
                $names .= '"' . preg_replace('/[<>"&\']/', '', $data['name']) . '", ';
            }
        }
        if (!empty($names)) {
            $note['body'] = xl('A Portal Patient has uploaded new documents titled') .
                ' ' . $names .
                xl('to the Documents Onsite Portal Patient category.') . "\n" .
                xl("Please review and take any necessary actions");
            $messageService->insert($pid, $note);
        }
    }
    // The page reads this JSON to tell the patient what happened; text() would HTML-escape quotes and break it.
    header('Content-Type: application/json');
    echo json_encode($rtn, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}
if (!empty($_FILES)) {
    $name = $_FILES['file']['name'];
    $type = $_FILES['file']['type'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $size = $_FILES['file']['size'];
    $owner = OEGlobalsBag::getInstance()->get('userauthorized');

    addNewDocument($name, $type, $tmp_name, '', $size, $owner, $patient_id, $category_id);
    exit;
}

function dicom_history_action($action, $doc_id, $json_data = ''): bool|string
{
    if ($action == 'save') {
        $json_data = base64_encode((string) $json_data);
        return json_encode(sqlQuery("UPDATE documents SET document_data = ? WHERE id = ?", [$json_data, $doc_id]));
    }

    if ($action == 'fetch') {
        $qrtn = sqlQuery("Select document_data FROM documents WHERE id = ?", [$doc_id]);
        return base64_decode((string) $qrtn['document_data']);
    }

    return xlj("Unknown");
}

/**
 * @return non-empty-array<non-empty-array<mixed>>[]
 */
function getMultiple(): array
{
    $_FILE = [];
    foreach ($_FILES as $name => $file) {
        foreach ($file as $property => $keys) {
            foreach ($keys as $key => $value) {
                $_FILE[$name][$key][$property] = $value;
            }
        }
    }
    return $_FILE;
}
