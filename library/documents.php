<?php

/**
 * Functions for documents.
 *
 * Copyright (C) 2013 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    https://www.open-emr.org
 */

require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

/**
 * Function to add a document via the C_Document class.
 *
 * @param string  $name                           Name of the document
 * @param string  $type                           Mime type of file
 * @param string  $tmp_name                       Temporary file name
 * @param string  $error                          Errors in file upload
 * @param string  $size                           Size of file
 * @param int     $owner                          Owner/user/service that imported the file
 * @param string  $patient_id_or_simple_directory Patient id or simple directory for storage when patient id not known (such as '00' or 'direct')
 * @param int     $category_id                    Document category id
 * @param string  $higher_level_path              Can set a higher level path here (and then place the path depth in $path_depth)
 * @param int     $path_depth                     Path depth when using the $higher_level_path feature
 * @param boolean $skip_acl_check                 This needs to be set to true for when uploading via services that piggyback on any user (ie. the background services) or uses cron/cli
 * @return array/boolean                          Array(doc_id,url) of the file as stored in documents table, false = failure
 */
function addNewDocument(
    $name,
    $type,
    $tmp_name,
    $error,
    $size,
    $owner = '',
    $patient_id_or_simple_directory = "00",
    $category_id = '1',
    $higher_level_path = '',
    $path_depth = '1',
    $skip_acl_check = false
) {

    if (empty($owner)) {
        $owner = $_SESSION['authUserID'];
    }

    // Build the $_FILES array
    $TEMP_FILES = [];
    $TEMP_FILES['file']['name'][0] = $name;
    $TEMP_FILES['file']['type'][0] = $type;
    $TEMP_FILES['file']['tmp_name'][0] = $tmp_name;
    $TEMP_FILES['file']['error'][0] = $error;
    $TEMP_FILES['file']['size'][0] = $size;
    $_FILES = $TEMP_FILES;

    // Build the parameters
    $_GET['higher_level_path'] = $higher_level_path;
    $_GET['patient_id'] = $patient_id_or_simple_directory;
    $_POST['destination'] = '';
    $_POST['submit'] = 'Upload';
    $_POST['path_depth'] = $path_depth;
    $_POST['patient_id'] = (is_numeric($patient_id_or_simple_directory) && $patient_id_or_simple_directory > 0) ? $patient_id_or_simple_directory : "00";
    $_POST['category_id'] = $category_id;
    $_POST['process'] = 'true';

    // Add the Document and return the newly added document id
    $cd = new C_Document();
    $cd->manual_set_owner = $owner;
    if ($skip_acl_check) {
        $cd->skipAclCheck();
    }
    $cd->upload_action_process();
    $v = $cd->getTemplateVars("file");
    if (!isset($v) || !$v) {
        return false;
    }

    return ["doc_id" => $v[0]->id, "url" => $v[0]->url, "name" => $v[0]->name];
}

/**
 * Function to return the category id of a category title.
 *
 * @param string $category_title category title
 * @return int/boolean              category id (returns false if the category title does not exist)
 */
function document_category_to_id($category_title)
{
    $ret = sqlQuery("SELECT `id` FROM `categories` WHERE `name`=?", [$category_title]);
    if ($ret['id']) {
        return $ret['id'];
    } else {
        return false;
    }
}

/**
 * Function used in the documents request for patient portal..
 *
 * @param string $imagetype Image type
 * @return string File extension Image type (returns false if the Image type does not exist)
 */
function get_extension($imagetype)
{
    if (empty($imagetype)) {
        return false;
    }

    return match ($imagetype) {
        'application/andrew-inset' => '.ez',
        'application/mac-binhex40' => '.hqx',
        'application/mac-compactpro' => '.cpt',
        'application/msword' => '.doc',
        'application/octet-stream' => '.bin',
        'application/octet-stream' => '.dms',
        'application/octet-stream' => '.lha',
        'application/octet-stream' => '.lzh',
        'application/octet-stream' => '.exe',
        'application/octet-stream' => '.class',
        'application/octet-stream' => '.so',
        'application/octet-stream' => '.dll',
        'application/oda' => '.oda',
        'application/pdf' => '.pdf',
        'application/postscript' => '.ai',
        'application/postscript' => '.eps',
        'application/postscript' => '.ps',
        'application/smil' => '.smi',
        'application/smil' => '.smil',
        'application/vnd.wap.wbxml' => '.wbxml',
        'application/vnd.wap.wmlc' => '.wmlc',
        'application/vnd.wap.wmlscriptc' => '.wmlsc',
        'application/x-bcpio' => '.bcpio',
        'application/x-cdlink' => '.vcd',
        'application/x-chess-pgn' => '.pgn',
        'application/x-cpio' => '.cpio',
        'application/x-csh' => '.csh',
        'application/x-director' => '.dcr',
        'application/x-director' => '.dir',
        'application/x-director' => '.dxr',
        'application/x-dvi' => '.dvi',
        'application/x-futuresplash' => '.spl',
        'application/x-gtar' => '.gtar',
        'application/x-hdf' => '.hdf',
        'application/x-javascript' => '.js',
        'application/x-koan' => '.skp',
        'application/x-koan' => '.skd',
        'application/x-koan' => '.skt',
        'application/x-koan' => '.skm',
        'application/x-latex' => '.latex',
        'application/x-netcdf' => '.nc',
        'application/x-netcdf' => '.cdf',
        'application/x-sh' => '.sh',
        'application/x-shar' => '.shar',
        'application/x-shockwave-flash' => '.swf',
        'application/x-stuffit' => '.sit',
        'application/x-sv4cpio' => '.sv4cpio',
        'application/x-sv4crc' => '.sv4crc',
        'application/x-tar' => '.tar',
        'application/x-tcl' => '.tcl',
        'application/x-tex' => '.tex',
        'application/x-texinfo' => '.texinfo',
        'application/x-texinfo' => '.texi',
        'application/x-troff' => '.t',
        'application/x-troff' => '.tr',
        'application/x-troff' => '.roff',
        'application/x-troff-man' => '.man',
        'application/x-troff-me' => '.me',
        'application/x-troff-ms' => '.ms',
        'application/x-ustar' => '.ustar',
        'application/x-wais-source' => '.src',
        'application/xhtml+xml' => '.xhtml',
        'application/xhtml+xml' => '.xht',
        'application/zip' => '.zip',
        'audio/basic' => '.au',
        'audio/basic' => '.snd',
        'audio/midi' => '.mid',
        'audio/midi' => '.midi',
        'audio/midi' => '.kar',
        'audio/mpeg' => '.mpga',
        'audio/mpeg' => '.mp2',
        'audio/mpeg' => '.mp3',
        'audio/x-aiff' => '.aif',
        'audio/x-aiff' => '.aiff',
        'audio/x-aiff' => '.aifc',
        'audio/x-mpegurl' => '.m3u',
        'audio/x-pn-realaudio' => '.ram',
        'audio/x-pn-realaudio' => '.rm',
        'audio/x-pn-realaudio-plugin' => '.rpm',
        'audio/x-realaudio' => '.ra',
        'audio/x-wav' => '.wav',
        'chemical/x-pdb' => '.pdb',
        'chemical/x-xyz' => '.xyz',
        'image/bmp' => '.bmp',
        'image/gif' => '.gif',
        'image/ief' => '.ief',
        'image/jpeg' => '.jpeg',
        'image/jpeg' => '.jpg',
        'image/jpeg' => '.jpe',
        'image/png' => '.png',
        'image/tiff' => '.tiff',
        'image/tif' => '.tif',
        'image/vnd.djvu' => '.djvu',
        'image/vnd.djvu' => '.djv',
        'image/vnd.wap.wbmp' => '.wbmp',
        'image/x-cmu-raster' => '.ras',
        'image/x-portable-anymap' => '.pnm',
        'image/x-portable-bitmap' => '.pbm',
        'image/x-portable-graymap' => '.pgm',
        'image/x-portable-pixmap' => '.ppm',
        'image/x-rgb' => '.rgb',
        'image/x-xbitmap' => '.xbm',
        'image/x-xpixmap' => '.xpm',
        'image/x-windowdump' => '.xwd',
        'model/iges' => '.igs',
        'model/iges' => '.iges',
        'model/mesh' => '.msh',
        'model/mesh' => '.mesh',
        'model/mesh' => '.silo',
        'model/vrml' => '.wrl',
        'model/vrml' => '.vrml',
        'text/css' => '.css',
        'text/html' => '.html',
        'text/html' => '.htm',
        'text/plain' => '.asc',
        'text/plain' => '.txt',
        'text/richtext' => '.rtx',
        'text/rtf' => '.rtf',
        'text/sgml' => '.sgml',
        'text/sgml' => '.sgm',
        'text/tab-seperated-values' => '.tsv',
        'text/vnd.wap.wml' => '.wml',
        'text/vnd.wap.wmlscript' => '.wmls',
        'text/x-setext' => '.etx',
        'text/xml' => '.xml',
        'text/xml' => '.xsl',
        'video/mpeg' => '.mpeg',
        'video/mpeg' => '.mpg',
        'video/mpeg' => '.mpe',
        'video/quicktime' => '.qt',
        'video/quicktime' => '.mov',
        'video/vnd.mpegurl' => '.mxu',
        'video/x-msvideo' => '.avi',
        'video/x-sgi-movie' => '.movie',
        'x-conference-xcooltalk' => '.ice',
        default => "",
    };
}
