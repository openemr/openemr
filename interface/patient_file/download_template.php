<?php
/**
 * Document Template Download Module.
 *
 * Copyright (C) 2013-2014 Rod Roark <rod@sunsetsystems.com>
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
 * @link    http://www.open-emr.org
 */

// This module downloads a specified document template to the browser after
// substituting relevant patient data into its variables.

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once('../globals.php');
require_once($GLOBALS['srcdir'] . '/acl.inc');
require_once($GLOBALS['srcdir'] . '/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'] . '/formdata.inc.php');
require_once($GLOBALS['srcdir'] . '/formatting.inc.php');
require_once($GLOBALS['srcdir'] . '/appointments.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');

$keyLocation = false;
$keyLength = 0;

function keySearch(&$s, $key) {
  global $keyLocation, $keyLength;
  $keyLength = strlen($key);
  $keyLocation = strpos($s, $key);
  return $keyLocation === false ? false : true;
}

function keyReplace(&$s, $data) {
  global $keyLocation, $keyLength;
  return substr($s, 0, $keyLocation) . $data . substr($s, $keyLocation + $keyLength);
}

function doSubs($s) {
  global $ptrow, $enrow;

  // $loopcount avoids infinite looping if we screw up.
  //
  for ($loopcount = 0; $loopcount < 500; ++$loopcount) {

    if (keySearch($s, '{PatientName}')) {
      $tmp = $ptrow['fname'];
      if ($ptrow['mname']) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['mname'];
      }
      if ($ptrow['lname']) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['lname'];
      }
      $s = keyReplace($s, $tmp);
    }

    else if (keySearch($s, '{PatientID}')) {
      $s = keyReplace($s, $ptrow['pubpid']);
    }

    else if (keySearch($s, '{Address}')) {
      $s = keyReplace($s, $ptrow['street']);
    }

    else if (keySearch($s, '{City}')) {
      $s = keyReplace($s, $ptrow['city']);
    }

    else if (keySearch($s, '{State}')) {
      $s = keyReplace($s, getListItemTitle('state', $ptrow['state']));
    }

    else if (keySearch($s, '{Zip}')) {
      $s = keyReplace($s, $ptrow['postal_code']);
    }

    else if (keySearch($s, '{PatientPhone}')) {
      $ptphone = $ptrow['phone_contact'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_home'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_cell'];
      if (empty($ptphone)) $ptphone = $ptrow['phone_biz'];
      if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp)) {
        $ptphone = '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3];
      }
      $s = keyReplace($s, $ptphone);
    }

    else if (keySearch($s, '{PatientDOB}')) {
      $s = keyReplace($s, oeFormatShortDate($ptrow['DOB']));
    }

    else if (keySearch($s, '{PatientSex}')) {
      $s = keyReplace($s, getListItemTitle('sex', $ptrow['sex']));
    }

    else if (keySearch($s, '{DOS}')) {
      $s = keyReplace($s, oeFormatShortDate(substr($enrow['date'], 0, 10)));
    }

    else if (keySearch($s, '{ChiefComplaint}')) {
      $cc = $enrow['reason'];
      $patientid = $ptrow['pid'];
      $DOS = substr($enrow['date'], 0, 10);
      // Prefer appointment comment if one is present.
      $evlist = fetchEvents($DOS, $DOS, " AND pc_pid = '$patientid' ");
      foreach ($evlist as $tmp) {
        if ($tmp['pc_pid'] == $pid && !empty($tmp['pc_hometext'])) {
          $cc = $tmp['pc_hometext'];
        }
      }
      $s = keyReplace($s, $cc);
    }

    else if (keySearch($s, '{ReferringDOC}')) {
      $tmp = empty($ptrow['ur_fname']) ? '' : $ptrow['ur_fname'];
      if (!empty($ptrow['ur_mname'])) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['ur_mname'];
      }
      if (!empty($ptrow['ur_lname'])) {
        if ($tmp) $tmp .= ' ';
        $tmp .= $ptrow['ur_lname'];
      }
      $s = keyReplace($s, $tmp);
    }

    else if (keySearch($s, '{Allergies}')) {
      $tmp = generate_plaintext_field(array('data_type'=>'24','list_id'=>''), '');
      $s = keyReplace($s, $tmp);
    }

    else if (keySearch($s, '{ProblemList}')) {
      $tmp = '';
      $query = "SELECT title FROM lists WHERE " .
        "pid = ? AND type = 'medical_problem' AND enddate IS NULL " .
        "ORDER BY begdate";
      $lres = sqlStatement($query, array($GLOBALS['pid']));
      $count = 0;
      while ($lrow = sqlFetchArray($lres)) {
        if ($count++) $tmp .= "; ";
        $tmp .= $lrow['title'];
      }
      $s = keyReplace($s, $tmp);
    }

    else {
      break;
    }

  }

  return $s;
}

// if (!acl_check('admin', 'super')) die(htmlspecialchars(xl('Not authorized')));

// Get patient demographic info.
$ptrow = sqlQuery("SELECT pd.*, " .
  "ur.fname AS ur_fname, ur.mname AS ur_mname, ur.lname AS ur_lname " .
  "FROM patient_data AS pd " .
  "LEFT JOIN users AS ur ON ur.id = pd.ref_providerID " .
  "WHERE pd.pid = ?", array($pid));
$enrow = array();
if ($encounter) {
  $enrow = sqlQuery("SELECT * FROM form_encounter WHERE pid = ? AND " .
    "encounter = ?", array($pid, $encounter));
}

$form_filename = strip_escape_custom($_REQUEST['form_filename']);
$templatedir   = "$OE_SITE_DIR/documents/doctemplates";
$templatepath  = "$templatedir/$form_filename";

// Create a temporary file to hold the output.
$fname = tempnam($GLOBALS['temporary_files_dir'], 'OED');

// Get mime type in a way that works with old and new PHP releases.
$mimetype = 'application/octet-stream';
if (substr($templatepath, -5) == '.dotx') {
  // PHP does not seem to recognize this type.
  $mimetype = 'application/msword';
}
else if (function_exists('finfo_open')) {
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mimetype = finfo_file($finfo, $templatepath);
  finfo_close($finfo);
}
else {
  $mimetype = mime_content_type($templatepath);
}

$zipin = new ZipArchive;
if ($zipin->open($templatepath) === true) {
  // Must be a zip archive.
  $zipout = new ZipArchive;
  $zipout->open($fname, ZipArchive::OVERWRITE);
  for ($i = 0; $i < $zipin->numFiles; ++$i) {
    $ename = $zipin->getNameIndex($i);
    $edata = $zipin->getFromIndex($i);
    $edata = doSubs($edata);
    $zipout->addFromString($ename, $edata);
  }
  $zipout->close();
  $zipin->close();
}
else {
  // Not a zip archive.
  $edata = file_get_contents($templatepath);
  $edata = doSubs($edata);
  file_put_contents($fname, $edata);
}

// Compute a download name like "filename_lastname_pid.odt".
$pi = pathinfo($form_filename);
$dlname = $pi['filename'] . '_' . $ptrow['lname'] . '_' . $pid;
if ($pi['extension'] !== '') $dlname .= '.' . $pi['extension'];

header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
// attachment, not inline
header("Content-Disposition: attachment; filename=\"$dlname\"");
header("Content-Type: $mimetype");
header("Content-Length: " . filesize($fname));
ob_clean();
flush();
readfile($fname);

unlink($fname);
?>
