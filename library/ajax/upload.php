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

// Auth if core or portal.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();
$isPortal = false;
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    $isPortal = true;
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
}

require_once(__DIR__ . "/../../interface/globals.php");
require_once(__DIR__ . "/../documents.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\MessageService;

if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// check if this is for dicom image maintenance.
$action = $_POST['action'] ?? null;
$doc_id = (int)$_POST['doc_id'] ?? null;
$json_data = $_POST['json_data'] ?? null;

if ($action == 'save') {
    $pass_it = dicom_history_action($action, $doc_id, $json_data);
    if ($pass_it === 'false') {
        // query success. send back a translated message for user.
        echo xlj("Server says thanks. Images state saved.");
    } else {
        echo xlj("Error! Images state save failed.");
    }

    exit();
}
if ($action == 'fetch') {
    $json_data = dicom_history_action($action, $doc_id);
    echo $json_data;

    exit();
}
// nope! so continue on with Sherwins uploader.
$patient_id = filter_input(INPUT_GET, 'patient_id');
$category_id = filter_input(INPUT_GET, 'parent_id');

if ($isPortal ?? false) {
    $owner = $GLOBALS['userauthorized'];
    $files = getMultiple();
    if (count($files["file"] ?? []) > 0) {
        $messageService = new MessageService();
        $data = [];
        $note['groupname'] = 'Default';
        // will send to all auth'ed portal users
        $note['to'] = 'portal-user';
        $note['from'] = 'portal-user';
        $note['message_status'] = 'New';
        $note['title'] = 'New Document';
        $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array($category_id))['id'] ?: 3;
        foreach ($files["file"] as $file) {
            $name = $file['name'];
            $type = $file['type'];
            $tmp_name = $file['tmp_name'];
            $size = $file['size'];
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
            $rtn[] = $data;
        }
        // give user a break and send just one message for multi documents
        $names = '';
        foreach ($rtn as $data) {
            $names .= '"' . $data['name'] . '", ';
        }
        if (!empty($names)) {
            $note['body'] = xl('A Portal Patient has uploaded new documents titled') .
                ' ' . $names .
                xl('to the Documents Onsite Portal Patient category.') . "\n" .
                xl("Please review and take any necessary actions");
            $messageService->insert($pid, $note);
        }
        echo text(json_encode($rtn));
    }
    exit;
}
if (!empty($_FILES)) {
    $name = $_FILES['file']['name'];
    $type = $_FILES['file']['type'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $size = $_FILES['file']['size'];
    $owner = $GLOBALS['userauthorized'];

    addNewDocument($name, $type, $tmp_name, '', $size, $owner, $patient_id, $category_id);
    exit;
}

function dicom_history_action($action, $doc_id, $json_data = ''): bool|string
{
    if ($action == 'save') {
        $json_data = base64_encode($json_data);
        return json_encode(sqlQuery("UPDATE documents SET document_data = ? WHERE id = ?", array($json_data, $doc_id)));
    }

    if ($action == 'fetch') {
        $qrtn = sqlQuery("Select document_data FROM documents WHERE id = ?", array($doc_id));
        return base64_decode($qrtn['document_data']);
    }

    return xlj("Unknown");
}

function getMultiple()
{
    $_FILE = array();
    foreach ($_FILES as $name => $file) {
        foreach ($file as $property => $keys) {
            foreach ($keys as $key => $value) {
                $_FILE[$name][$key][$property] = $value;
            }
        }
    }
    return $_FILE;
}
