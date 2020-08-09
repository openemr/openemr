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
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../documents.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// check if this is for dicom image maintenance.
$action = $_POST['action'] ?? null;
$doc_id = (int)$_POST['doc_id'] ?? null;
$json_data = $_POST['json_data'] ?? null;

if ($action == 'save') {
    //$data = json_decode($json_data, true);
    //$json_data = base64_encode($data);
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

if (!empty($_FILES)) {
    $name = $_FILES['file']['name'];
    $type = $_FILES['file']['type'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $size = $_FILES['file']['size'];
    $owner = $GLOBALS['userauthorized'];

    addNewDocument($name, $type, $tmp_name, $error, $size, $owner, $patient_id, $category_id);
    exit();
}

function dicom_history_action($action, $doc_id, $json_data = '')
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
