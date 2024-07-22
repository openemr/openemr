<?php

/**
 * Download selected patient documents in a zip file
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../verify_session.php");
require_once("$srcdir/documents.php");
require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

// Check if documents are selected
if (empty($_POST['documents'])) {
    die("No documents selected.");
}

// Get the temporary folder
$tmp = $GLOBALS['temporary_files_dir'];
$documentIds = $_POST['documents'];
$pid = $_SESSION['pid'];

// Process each selected document
foreach ($documentIds as $documentId) {
    $sql = "SELECT url, id, mimetype, `name`, `foreign_id` FROM `documents` WHERE `id` = ? AND `deleted` = 0";
    $file = sqlQuery($sql, array($documentId));
    if ($file['foreign_id'] != $pid && $file['foreign_id'] != $_SESSION['pid']) {
        die(xlt("Invalid document selected."));
    }
    // Find the document category
    $sql = "SELECT name, lft, rght FROM `categories`, `categories_to_documents`
            WHERE `categories_to_documents`.`category_id` = `categories`.`id`
            AND `categories_to_documents`.`document_id` = ?";
    $cat = sqlQuery($sql, array($file['id']));

    // Find the tree of the document's category
    $sql = "SELECT name FROM categories WHERE lft < ? AND rght > ? ORDER BY lft ASC";
    $pathres = sqlStatement($sql, array($cat['lft'], $cat['rght']));

    // Create the tree of the categories
    $path = "";
    while ($parent = sqlFetchArray($pathres)) {
        $path .= convert_safe_file_dir_name($parent['name']) . "/";
    }

    $path .= convert_safe_file_dir_name($cat['name']) . "/";
    // Create the folder structure at the temporary dir
    if (!is_dir($tmp . "/" . $pid . "/" . $path)) {
        if (!mkdir($concurrentDirectory = $tmp . "/" . $pid . "/" . $path, 0777, true) && !is_dir($concurrentDirectory)) {
            die("Error creating directory!");
        }
    }

    // Copy the document
    $obj = new C_Document();
    $document = $obj->retrieve_action("", $documentId, true, true, true);
    if ($document) {
        $pos = strpos(substr($file['name'], -5), '.');
        // Check if has an extension or find it from the mimetype
        if ($pos === false) {
            $file['name'] .= get_extension($file['mimetype']);
        }

        $dest = $tmp . "/" . $pid . "/" . $path . "/" . convert_safe_file_dir_name($file['name']);
        if (file_exists($dest)) {
            $x = 1;
            do {
                $dest = $tmp . "/" . $pid . "/" . $path . "/" . $x . "_" . convert_safe_file_dir_name($file['name']);
                $x++;
            } while (file_exists($dest));
        }

        file_put_contents($dest, $document);
    } else {
        echo xlt("Can't find file!");
    }
}

// Zip the folder
Zip($tmp . "/" . $pid . "/", $tmp . "/" . $pid . '.zip');

// Serve it to the patient
header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="patient_documents.zip"');
readfile($tmp . "/" . $pid . '.zip');

// Remove the temporary folders and files
recursive_remove_directory($tmp . "/" . $pid);
unlink($tmp . "/" . $pid . '.zip');

function recursive_remove_directory($directory, $empty = false)
{
    if (substr($directory, -1) == '/') {
        $directory = substr($directory, 0, -1);
    }

    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif (is_readable($directory)) {
        $handle = opendir($directory);
        while (false !== ($item = readdir($handle))) {
            if ($item != '.' && $item != '..') {
                $path = $directory . '/' . $item;
                if (is_dir($path)) {
                    recursive_remove_directory($path);
                } else {
                    unlink($path);
                }
            }
        }

        closedir($handle);
        if ($empty == false) {
            if (!rmdir($directory)) {
                return false;
            }
        }
    }

    return true;
}

function Zip($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            if ($file == $source . "/..") {
                continue;
            }

            $file = str_replace('\\', '/', realpath($file));
            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } elseif (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    } elseif (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}
