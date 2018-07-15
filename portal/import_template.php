<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */


require_once("../interface/globals.php");

if ($_POST['mode'] == 'get') {
    $rebuilt = validateFile($_POST['docid']);
    if ($rebuilt) {
        echo file_get_contents($rebuilt);
        exit();
    } else {
        die(xlt('Invalid File'));
    }
} else if ($_POST['mode'] == 'save') {
    $rebuilt = validateFile($_POST['docid']);
    if ($rebuilt) {
        if (stripos($_POST['content'], "<?php") === false) {
            file_put_contents($rebuilt, $_POST['content']);
            exit(true);
        } else {
            die(xlt('Invalid Content'));
        }
    } else {
        die(xlt('Invalid File'));
    }
} else if ($_POST['mode'] == 'delete') {
    $rebuilt = validateFile($_POST['docid']);
    if ($rebuilt) {
        unlink($rebuilt);
        exit(true);
    } else {
        die(xlt('Invalid File'));
    }
}

// so it is an import
if (!isset($_POST['up_dir'])) {
    define("UPLOAD_DIR", $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/');
} else {
    if ($_POST['up_dir'] > 0) {
        define("UPLOAD_DIR", $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/' . convert_safe_file_dir_name($_POST['up_dir']) . '/');
    } else {
        define("UPLOAD_DIR", $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/');
    }
}

if (!empty($_FILES["tplFile"])) {
    $tplFile = $_FILES["tplFile"];

    if ($tplFile["error"] !== UPLOAD_ERR_OK) {
        header("refresh:2;url= import_template_ui.php");
        echo "<p>" . xlt("An error occurred: Missing file to upload: Use back button!") . "</p>";
        exit;
    }

    // ensure a safe filename
    $name = preg_replace("/[^A-Z0-9._-]/i", "_", $tplFile["name"]);
    if (preg_match("/(.*)\.(php|php3|php4|php5|php7)$/i", $name) !== 0) {
        die(xlt('Executables not allowed'));
    }
    $parts = pathinfo($name);
    $name = $parts["filename"] . '.tpl';
    // don't overwrite an existing file
    while (file_exists(UPLOAD_DIR . $name)) {
        $i = rand(0, 128);
        $newname = $parts["filename"] . "-" . $i . "." . $parts["extension"] . ".replaced";
        rename(UPLOAD_DIR . $name, UPLOAD_DIR . $newname);
    }

    // preserve file from temporary directory
    $success = move_uploaded_file($tplFile["tmp_name"], UPLOAD_DIR . $name);
    if (!$success) {
        echo "<p>" . xlt("Unable to save file: Use back button!") . "</p>";
        exit;
    }

    // set proper permissions on the new file
    chmod(UPLOAD_DIR . $name, 0644);
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

function validateFile($filename = '')
{
    $knownPath = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/'; // default path
    $unknown = str_replace("\\", "/", realpath($filename)); // normalize requested path
    $parts = pathinfo($unknown);
    $unkParts = explode('/', $parts['dirname']);
    $ptpid = $unkParts[count($unkParts) - 1]; // is this a patient or global template
    $ptpid = ($ptpid == 'templates') ? '' : ($ptpid . '/'); // last part should be pid or template
    $rebuiltPath = $knownPath . $ptpid . $parts['filename'] . '.tpl';
    if (file_exists($rebuiltPath) === false || $parts['extension'] != 'tpl') {
        redirect();
    } elseif (realpath($rebuiltPath) != realpath($filename)) { // these need to match to be valid request
        redirect();
    } elseif (stripos(realpath($filename), realpath($knownPath)) === false) { // this needs to pass be a valid request
        redirect();
    }

    return $rebuiltPath;
}

function redirect()
{
    header('HTTP/1.0 404 Not Found');
    die();
}
