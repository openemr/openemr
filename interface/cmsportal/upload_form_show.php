<?php
/**
 * View a file upload from the CMS Patient Portal.
 *
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 */




require_once("../globals.php");
require_once("portal.inc.php");

$uploadid = $_REQUEST['id'];

if (!empty($_REQUEST['messageid'])) {
    $result = cms_portal_call(array('action' => 'getmsgup', 'uploadid' => $uploadid));
} else {
    $result = cms_portal_call(array('action' => 'getupload', 'uploadid' => $uploadid));
}

if ($result['errmsg']) {
    die(text($result['errmsg']));
}

$filesize = strlen($result['contents']);

header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header("Content-Disposition: attachment; filename=\"{$result['filename']}\"");
header("Content-Type: {$result['mimetype']}");
header("Content-Length: $filesize");

// With JSON-over-HTTP we would need to base64_decode the contents.
echo $result['contents'];
