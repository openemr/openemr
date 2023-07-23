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
 * @param  string         $name                            Name of the document
 * @param  string         $type                            Mime type of file
 * @param  string         $tmp_name                        Temporary file name
 * @param  string         $error                           Errors in file upload
 * @param  string         $size                            Size of file
 * @param  int            $owner                           Owner/user/service that imported the file
 * @param  string         $patient_id_or_simple_directory  Patient id or simple directory for storage when patient id not known (such as '00' or 'direct')
 * @param  int            $category_id                     Document category id
 * @param  string         $higher_level_path               Can set a higher level path here (and then place the path depth in $path_depth)
 * @param  int            $path_depth                      Path depth when using the $higher_level_path feature
 * @param  boolean        $skip_acl_check                  This needs to be set to true for when uploading via services that piggyback on any user (ie. the background services) or uses cron/cli
 * @return array/boolean                                   Array(doc_id,url) of the file as stored in documents table, false = failure
 */
function addNewDocument($name, $type, $tmp_name, $error, $size, $owner = '', $patient_id_or_simple_directory = "00", $category_id = '1', $higher_level_path = '', $path_depth = '1', $skip_acl_check = false)
{

    if (empty($owner)) {
        $owner = $_SESSION['authUserID'];
    }

    // Build the $_FILES array
    $TEMP_FILES = array();
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

    return array ("doc_id" => $v[0]->id, "url" => $v[0]->url);
}

/**
 * Function to return the category id of a category title.
 *
 * @param  string  $category_title  category title
 * @return int/boolean              category id (returns false if the category title does not exist)
 */
function document_category_to_id($category_title)
{
    $ret = sqlQuery("SELECT `id` FROM `categories` WHERE `name`=?", array($category_title));
    if ($ret['id']) {
        return $ret['id'];
    } else {
        return false;
    }
}

/**
 * Function used in the documents request for patient portal..
 *
 * @param  string  $imagetype  Image type
 * @return File extension Image type (returns false if the Image type does not exist)
 */
function get_extension($imagetype)
{
    if (empty($imagetype)) {
        return false;
    }

    switch ($imagetype) {
        case 'application/andrew-inset':
            return '.ez';
        case 'application/mac-binhex40':
            return '.hqx';
        case 'application/mac-compactpro':
            return '.cpt';
        case 'application/msword':
            return '.doc';
        case 'application/octet-stream':
            return '.bin';
        case 'application/octet-stream':
            return '.dms';
        case 'application/octet-stream':
            return '.lha';
        case 'application/octet-stream':
            return '.lzh';
        case 'application/octet-stream':
            return '.exe';
        case 'application/octet-stream':
            return '.class';
        case 'application/octet-stream':
            return '.so';
        case 'application/octet-stream':
            return '.dll';
        case 'application/oda':
            return '.oda';
        case 'application/pdf':
            return '.pdf';
        case 'application/postscript':
            return '.ai';
        case 'application/postscript':
            return '.eps';
        case 'application/postscript':
            return '.ps';
        case 'application/smil':
            return '.smi';
        case 'application/smil':
            return '.smil';
        case 'application/vnd.wap.wbxml':
            return '.wbxml';
        case 'application/vnd.wap.wmlc':
            return '.wmlc';
        case 'application/vnd.wap.wmlscriptc':
            return '.wmlsc';
        case 'application/x-bcpio':
            return '.bcpio';
        case 'application/x-cdlink':
            return '.vcd';
        case 'application/x-chess-pgn':
            return '.pgn';
        case 'application/x-cpio':
            return '.cpio';
        case 'application/x-csh':
            return '.csh';
        case 'application/x-director':
            return '.dcr';
        case 'application/x-director':
            return '.dir';
        case 'application/x-director':
            return '.dxr';
        case 'application/x-dvi':
            return '.dvi';
        case 'application/x-futuresplash':
            return '.spl';
        case 'application/x-gtar':
            return '.gtar';
        case 'application/x-hdf':
            return '.hdf';
        case 'application/x-javascript':
            return '.js';
        case 'application/x-koan':
            return '.skp';
        case 'application/x-koan':
            return '.skd';
        case 'application/x-koan':
            return '.skt';
        case 'application/x-koan':
            return '.skm';
        case 'application/x-latex':
            return '.latex';
        case 'application/x-netcdf':
            return '.nc';
        case 'application/x-netcdf':
            return '.cdf';
        case 'application/x-sh':
            return '.sh';
        case 'application/x-shar':
            return '.shar';
        case 'application/x-shockwave-flash':
            return '.swf';
        case 'application/x-stuffit':
            return '.sit';
        case 'application/x-sv4cpio':
            return '.sv4cpio';
        case 'application/x-sv4crc':
            return '.sv4crc';
        case 'application/x-tar':
            return '.tar';
        case 'application/x-tcl':
            return '.tcl';
        case 'application/x-tex':
            return '.tex';
        case 'application/x-texinfo':
            return '.texinfo';
        case 'application/x-texinfo':
            return '.texi';
        case 'application/x-troff':
            return '.t';
        case 'application/x-troff':
            return '.tr';
        case 'application/x-troff':
            return '.roff';
        case 'application/x-troff-man':
            return '.man';
        case 'application/x-troff-me':
            return '.me';
        case 'application/x-troff-ms':
            return '.ms';
        case 'application/x-ustar':
            return '.ustar';
        case 'application/x-wais-source':
            return '.src';
        case 'application/xhtml+xml':
            return '.xhtml';
        case 'application/xhtml+xml':
            return '.xht';
        case 'application/zip':
            return '.zip';
        case 'audio/basic':
            return '.au';
        case 'audio/basic':
            return '.snd';
        case 'audio/midi':
            return '.mid';
        case 'audio/midi':
            return '.midi';
        case 'audio/midi':
            return '.kar';
        case 'audio/mpeg':
            return '.mpga';
        case 'audio/mpeg':
            return '.mp2';
        case 'audio/mpeg':
            return '.mp3';
        case 'audio/x-aiff':
            return '.aif';
        case 'audio/x-aiff':
            return '.aiff';
        case 'audio/x-aiff':
            return '.aifc';
        case 'audio/x-mpegurl':
            return '.m3u';
        case 'audio/x-pn-realaudio':
            return '.ram';
        case 'audio/x-pn-realaudio':
            return '.rm';
        case 'audio/x-pn-realaudio-plugin':
            return '.rpm';
        case 'audio/x-realaudio':
            return '.ra';
        case 'audio/x-wav':
            return '.wav';
        case 'chemical/x-pdb':
            return '.pdb';
        case 'chemical/x-xyz':
            return '.xyz';
        case 'image/bmp':
            return '.bmp';
        case 'image/gif':
            return '.gif';
        case 'image/ief':
            return '.ief';
        case 'image/jpeg':
            return '.jpeg';
        case 'image/jpeg':
            return '.jpg';
        case 'image/jpeg':
            return '.jpe';
        case 'image/png':
            return '.png';
        case 'image/tiff':
            return '.tiff';
        case 'image/tif':
            return '.tif';
        case 'image/vnd.djvu':
            return '.djvu';
        case 'image/vnd.djvu':
            return '.djv';
        case 'image/vnd.wap.wbmp':
            return '.wbmp';
        case 'image/x-cmu-raster':
            return '.ras';
        case 'image/x-portable-anymap':
            return '.pnm';
        case 'image/x-portable-bitmap':
            return '.pbm';
        case 'image/x-portable-graymap':
            return '.pgm';
        case 'image/x-portable-pixmap':
            return '.ppm';
        case 'image/x-rgb':
            return '.rgb';
        case 'image/x-xbitmap':
            return '.xbm';
        case 'image/x-xpixmap':
            return '.xpm';
        case 'image/x-windowdump':
            return '.xwd';
        case 'model/iges':
            return '.igs';
        case 'model/iges':
            return '.iges';
        case 'model/mesh':
            return '.msh';
        case 'model/mesh':
            return '.mesh';
        case 'model/mesh':
            return '.silo';
        case 'model/vrml':
            return '.wrl';
        case 'model/vrml':
            return '.vrml';
        case 'text/css':
            return '.css';
        case 'text/html':
            return '.html';
        case 'text/html':
            return '.htm';
        case 'text/plain':
            return '.asc';
        case 'text/plain':
            return '.txt';
        case 'text/richtext':
            return '.rtx';
        case 'text/rtf':
            return '.rtf';
        case 'text/sgml':
            return '.sgml';
        case 'text/sgml':
            return '.sgm';
        case 'text/tab-seperated-values':
            return '.tsv';
        case 'text/vnd.wap.wml':
            return '.wml';
        case 'text/vnd.wap.wmlscript':
            return '.wmls';
        case 'text/x-setext':
            return '.etx';
        case 'text/xml':
            return '.xml';
        case 'text/xml':
            return '.xsl';
        case 'video/mpeg':
            return '.mpeg';
        case 'video/mpeg':
            return '.mpg';
        case 'video/mpeg':
            return '.mpe';
        case 'video/quicktime':
            return '.qt';
        case 'video/quicktime':
            return '.mov';
        case 'video/vnd.mpegurl':
            return '.mxu';
        case 'video/x-msvideo':
            return '.avi';
        case 'video/x-sgi-movie':
            return '.movie';
        case 'x-conference-xcooltalk':
            return '.ice';
        default:
            return "";
    }
}
