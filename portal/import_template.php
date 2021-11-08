<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$templateService = new DocumentTemplateService();
$patient = json_decode($_POST['upload_pid']);

if ($_POST['mode'] === 'get') {
    if ($_POST['docid']) {
        $template = $templateService->fetchTemplate($_POST['docid']);
        echo $template['template_content'];
        exit();
    } else {
        die(xlt('Invalid File'));
    }
} elseif ($_POST['mode'] === 'save') {
    if ($_POST['docid']) {
        if (stripos($_POST['content'], "<?php") === false) {
            $template = $templateService->updateTemplateContent($_POST['docid'], $_POST['content']);
        } else {
            die(xlt('Invalid Content'));
        }
    } else {
        die(xlt('Invalid File'));
    }
} elseif ($_POST['mode'] === 'delete') {
    if ($_POST['docid']) {
        $template = $templateService->deleteTemplate($_POST['docid']);
        exit(true);
    }
    die(xlt('Invalid File'));
} elseif ($_POST['mode'] === 'send') {
    if (!empty($_POST['docid'])) {
        $pids_array = json_decode($_POST['docid']) ?: ['0'];
        $ids = json_decode($_POST['checked']) ?: [];

        $last_id = $templateService->sendTemplate($pids_array, $ids, $_POST['category']);
        if ($last_id) {
            echo xlt("Templates Successfully sent to patients.");
        } else {
            echo xlt('Error. Problem sending one or more templates. Some templates may not have been sent.');
        }
        exit;
    }
    die(xlt('Invalid Request'));
} elseif (!empty($_FILES["template_files"])) {
    // so it is a template file import. create record(s).
    $import_files = $_FILES["template_files"];
    $total = count($_FILES['template_files']['name']);
    for ($i = 0; $i < $total; $i++) {
        if ($_FILES['template_files']['error'][$i] !== UPLOAD_ERR_OK) {
            header('refresh:3;url= import_template_ui.php');
            echo '<title>' . xlt('Error') . " ...</title><h4 style='color:red;'>" .
                xlt('An error occurred: Missing file to upload. Returning to form.') . '</h4>';
            exit;
        }
        // parse out what we need
        $name = preg_replace("/[^A-Z0-9.]/i", " ", $_FILES['template_files']['name'][$i]);
        if (preg_match("/(.*)\.(php|php7|php8|doc|docx)$/i", $name) !== 0) {
            die(xlt('Invalid file type.'));
        }
        $parts = pathinfo($name);
        $name = ucwords(strtolower($parts["filename"]));
        // get em and dispose
        $success = $templateService->uploadTemplate($name, $_POST['template_category'], $_FILES['template_files']['tmp_name'][$i], $patient);
        if (!$success) {
            echo "<p>" . xlt("Unable to save files. Use back button!") . "</p>";
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

function redirect()
{
    header('HTTP/1.0 404 Not Found');
    die();
}
