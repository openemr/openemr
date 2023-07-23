<?php

/**
 * Command-line / Unattended document import utility.
 *
 * Expected arguments:
 * site - As required for all cronjobs
 * path - $GLOBALS key specifying the path
 * pid - patient id to be used for all selected documents
 * category - encoded category description to assign all selected documents
 * owner - owner of all selected documents
 * limit - Maximum number of files to be imported
 * in_situ - Retain documents in current folder
 *
 * Examples:
 * 1. To import received faxes:
 *    a. Set 'Scanner Directory' to network location where a fax machine can save received files
 *    b. Schedule cronjob '/usr/bin/php -f /var/www/html/emr/custom/zutil.cli.doc_import.php site=default'
 *    c. View and process received faxes using 'New Documents' menu.
 *
 * 2. To import collection of medical record files for a specific patient:
 *    a. Save files in Scanner Directory
 *    b. For patient nnn and category 'Patient Records', access this script online with request parameters as:
 *       zutil.cli.doc_import?site=default&pid=nnn&category=Patient+Records&limit=1000
 *
 * 3. Use in_situ=true option to create document records without relocating the files.
 *    This option requires good understanding of current functionality.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2017 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Allow this script to be run as a cronjob
require_once(dirname(__FILE__, 2) . "/library/allow_cronjobs.php");

// Defaults
$arg = array(
    'path' => 'scanner_output_directory',
    'pid' => '00',
    'category' => 1,
    'owner' => '',
    'limit' => 10,
    'in_situ' => 0,
);

foreach ($arg as $key => $def) {
    if ($key == "path") {
        // do not let setting of path via GET for security reasons
        continue;
    }
    if (isset($_GET[$key])) {
        $arg[$key] = $_GET[$key];
    }
}

require_once(dirname(__FILE__, 2) . "/interface/globals.php");
require_once("$srcdir/documents.php");

if ($arg['category'] != 1) {
    $rec_cat = sqlQuery('SELECT id FROM categories WHERE name=?', array(urldecode($arg['category'])));
    if (isset($rec_cat['id'])) {
        $arg['category'] = $rec_cat['id'];
    }
}
// Defined here as fallback
$ext2mime = array(
    "pdf" => "application/pdf",
    "exe" => "application/octet-stream",
    "zip" => "application/zip",
    "docx" => "application/msword",
    "doc" => "application/msword",
    "xls" => "application/vnd.ms-excel",
    "ppt" => "application/vnd.ms-powerpoint",
    "gif" => "image/gif",
    "png" => "image/png",
    "jpeg" => "image/jpg",
    "jpg" => "image/jpg",
    "mp3" => "audio/mpeg",
    "wav" => "audio/x-wav",
    "mpeg" => "video/mpeg",
    "mpg" => "video/mpeg",
    "mpe" => "video/mpeg",
    "mov" => "video/quicktime",
    "avi" => "video/x-msvideo",
    "3gp" => "video/3gpp",
    "css" => "text/css",
    "jsc" => "application/javascript",
    "js" => "application/javascript",
    "php" => "text/html",
    "htm" => "text/html",
    "html" => "text/html"
);

printf('%s %s %s (%s)%s', xlt('Import'), text($arg['limit']), xlt('documents'), text($arg['path']), "\n");

$docs = new DirectoryIterator($arg['path']);
foreach ($docs as $doc) {
    if ($doc->isDot()) {
        continue;
    }

    $doc_pathname = $doc->getPathname();
    $doc_url = "file://" . $doc_pathname;

    $finfo = finfo_open();
    $str_mime = finfo_file($finfo, $doc_pathname, FILEINFO_MIME_TYPE);
    finfo_close($finfo);

    if (!$str_mime) {
        $str_mime = $ext2mime[$doc->getExtension()];
    }

    if ($arg['in_situ']) {
        // Skip prior documents
        // mdsupport - Check both formats since there is no consistent code for managing urls.
        $rec_doc = sqlQuery('SELECT id FROM documents WHERE url=? or url=?', array($doc_pathname, $doc_url));
        if (isset($rec_doc['id'])) {
            continue;
        }
        // mdsupport - This should be a standard method with DocStore variations.  Until then,
        // Create a document record for file
        $objDoc = new Document();
        $objDoc->set_storagemethod('0');
        $objDoc->set_mimetype($str_mime);
        $objDoc->set_url($doc_url);
        $objDoc->set_size($doc->getSize());
        $objDoc->set_hash(hash_file('sha3-512', $doc_pathname));
        $objDoc->set_type($objDoc->type_array['file_url']);
        $objDoc->set_owner($arg['owner']);
        $objDoc->set_foreign_id($arg['pid']);
        $objDoc->persist();
        $objDoc->populate();
        // mdsupport - Need set_category method for the Document
        if (is_numeric($objDoc->get_id())) {
            sqlStatement("INSERT INTO categories_to_documents(category_id, document_id) VALUES(?,?)", array($arg['category'], $objDoc->get_id()));
        }
        printf('%s - %s%s', text($doc_pathname), (is_numeric($objDoc->get_id()) ? text($objDoc->get_url()) : xlt('Documents setup error')), "\n");
    } else {
        // Too many parameters for the function make the following setup necessary for readability.
        $doc_params = array(
            'name' => $doc->getFilename(),
            'mime_type' => $str_mime,
            'full_path' => $doc_pathname,
            'upload_error' => '',
            'size' => $doc->getSize(),
            'owner' => $arg['owner'],
            'patient_id' => $arg['pid'],
            'category_id' => '1',
            'higher_level_path' => '',
            'path_depth' => '1',
            'skip_acl_check' => true
        );
        $new_doc = call_user_func_array('addNewDocument', $doc_params);
        printf('%s - %s%s', text($doc_pathname), (isset($new_doc) ? text($new_doc->get_url()) : xlt('Documents setup error')), "\n");
        if (!$new_doc) {
            die();
        } elseif (!unlink($doc_pathname)) {
            printf('%s - %s', text($doc_pathname), xlt('Original file deletion error'));
            die();
        }
    }
    if (--$arg['limit'] < 1) {
        break;
    }
}
