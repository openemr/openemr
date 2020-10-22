<?php

/**
 * Download documents from OpenEMR to the patient portal in a zip file(get_patient_documents.php)
 *
 * This program is used to download patient documents in a zip file in the Patient Portal.
 * The original author did not pursue this but I thought it would be a good addition to
 * the patient portal
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Giorgos Vasilakos <giorg.vasilakos@gmail.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Giorgos Vasilakos <giorg.vasilakos@gmail.com>
 * @copyright Copyright (c) 2015-2017 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    require_once("verify_session.php");
    require_once("$srcdir/documents.php");
    require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

    // get the temporary folder
    $tmp = $GLOBALS['temporary_files_dir'];
    // get all the documents of the patient
    $sql = "SELECT url, id, mimetype, `name` FROM `documents` WHERE `foreign_id` = ? AND `deleted` = 0";
    $fres = sqlStatement($sql, array($pid));

    // for every document
while ($file = sqlFetchArray($fres)) {
    // find the document category
    $sql = "SELECT name, lft, rght FROM `categories`, `categories_to_documents`
				WHERE `categories_to_documents`.`category_id` = `categories`.`id`
				AND `categories_to_documents`.`document_id` = ?";
    $catres = sqlStatement($sql, array($file['id']));
    $cat = sqlFetchArray($catres);

    // find the tree of the documents category
    $sql = "SELECT name FROM categories WHERE lft < ? AND rght > ? ORDER BY lft ASC";
    $pathres = sqlStatement($sql, array($cat['lft'], $cat['rght']));

    // create the tree of the categories
    $path = "";
    while ($parent = sqlFetchArray($pathres)) {
        $path .= convert_safe_file_dir_name($parent['name']) . "/";
    }

    $path .= convert_safe_file_dir_name($cat['name']) . "/";
    // create the folder structure at the temporary dir
    if (!is_dir($tmp . "/" . $pid . "/" . $path)) {
        if (!mkdir($tmp . "/" . $pid . "/" . $path, 0777, true)) {
            echo xlt("Error creating directory!") . "<br />";
        }
    }

    // copy the document
    $documentId = $file['id'];
    $obj = new \C_Document();
    $document = $obj->retrieve_action("", $documentId, true, true, true);
    if ($document) {
        $pos = strpos(substr($file['name'], -5), '.');
        // check if has an extension or find it from the mimetype
        if ($pos === false) {
            $file['name'] = $file['name'] . get_extension($file['mimetype']);
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
        echo xlt("Can't find file!") . "<br />";
    }
}

    // zip the folder
    Zip($tmp . "/" . $pid . "/", $tmp . "/" . $pid . '.zip');

    // serve it to the patient
    header('Content-type: application/zip');
    header('Content-Disposition: attachment; filename="patient_documents.zip"');
    readfile($tmp . "/" . $pid . '.zip');

    // remove the temporary folders and files
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
