<?php
/**
* Functions to support parsing and saving hl7 results.
*
* Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

function rhl7InsertRow(&$arr, $tablename) {
  if (empty($arr)) return;

  // echo "<!-- ";   // debugging
  // print_r($arr);
  // echo " -->\n";

  $query = "INSERT INTO $tablename SET";
  $binds = array();
  $sep = '';
  foreach ($arr as $key => $value) {
    $query .= "$sep `$key` = ?";
    $sep = ',';
    $binds[] = $value;
  }
  $arr = array();
  return sqlInsert($query, $binds);
}

function rhl7FlushResult(&$ares) {
  return rhl7InsertRow($ares, 'procedure_result');
}

function rhl7FlushReport(&$arep) {
  return rhl7InsertRow($arep, 'procedure_report');
}

function rhl7Text($s) {
  $s = str_replace('\\S\\'  ,'^' , $s);
  $s = str_replace('\\F\\'  ,'|' , $s);
  $s = str_replace('\\R\\'  ,'~' , $s);
  $s = str_replace('\\T\\'  ,'&' , $s);
  $s = str_replace('\\X0d\\',"\r", $s);
  $s = str_replace('\\E\\'  ,'\\', $s);
  return $s;
}

function rhl7DateTime($s) {
  if (empty($s)) return '0000-00-00 00:00:00';
  $ret = substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2);
  if (strlen($s) > 8) $ret .= ' ' . substr($s, 8, 2) . ':' . substr($s, 10, 2) . ':';
  if (strlen($s) > 12) {
    $ret .= substr($s, 12, 2);
  } else {
    $ret .= '00';
  }
  return $ret;
}

function rhl7Abnormal($s) {
  if ($s == ''  ) return 'no';
  if ($s == 'A' ) return 'yes';
  if ($s == 'H' ) return 'high';
  if ($s == 'L' ) return 'low';
  if ($s == 'HH') return 'vhigh';
  if ($s == 'LL') return 'vlow';
  return rhl7Text($s);
}

function rhl7ReportStatus($s) {
  if ($s == 'F') return 'final';
  if ($s == 'P') return 'prelim';
  if ($s == 'C') return 'correct';
  return rhl7Text($s);
}

/**
 * Parse and save.
 *
 * @param  string  &$pprow   A row from the procedure_providers table.
 * @param  string  &$hl7     The input HL7 text.
 * @return string            Error text, or empty if no errors.
 */
function receive_hl7_results(&$hl7) {
  if (substr($hl7, 0, 3) != 'MSH') {
    return xl('Input does not begin with a MSH segment');
  }

  // End-of-line delimiter for text in procedure_result.comments
  $commentdelim = "\n";

  $today = time();

  $in_message_id = '';
  $in_ssn = '';
  $in_dob = '';
  $in_lname = '';
  $in_fname = '';
  $in_orderid = 0;
  $in_procedure_code = '';
  $in_report_status = '';
  $in_encounter = 0;

  $porow = false;
  $pcrow = false;
  $procedure_report_id = 0;
  $arep = array(); // holding area for OBR and its NTE data
  $ares = array(); // holding area for OBX and its NTE data

  // This is so we know where we are if a segment like NTE that can appear in
  // different places is encountered.
  $context = '';

  // Delimiters
  $d0 = "\r";
  $d1 = substr($hl7, 3, 1); // typically |
  $d2 = substr($hl7, 4, 1); // typically ^
  $d3 = substr($hl7, 5, 1); // typically ~

  $segs = explode($d0, $hl7);

  foreach ($segs as $seg) {
    if (empty($seg)) continue;

    $a = explode($d1, $seg);

    if ($a[0] == 'MSH') {
      $context = $a[0];
      if ($a[8] != 'ORU^R01') {
        return xl('Message type') . " '${a[8]}' " . xl('does not seem valid');
      }
      $in_message_id = $a[9];
    }

    else if ($a[0] == 'PID') {
      $context = $a[0];
      rhl7FlushResult($ares);
      // Next line will do something only if there was a report with no results.
      rhl7FlushReport($arep);
      $in_ssn = $a[4];
      $in_dob = $a[7]; // yyyymmdd format
      $tmp = explode($d2, $a[5]);
      $in_lname = $tmp[0];
      $in_fname = $tmp[1];
    }

    else if ($a[0] == 'PV1') {
      // Save placer encounter number if present.
      if (!empty($a[19])) {
        $tmp = explode($d2, $a[19]);
        $in_encounter = intval($tmp[0]);
      }
    }

    else if ($a[0] == 'ORC') {
      $context = $a[0];
      rhl7FlushResult($ares);
      // Next line will do something only if there was a report with no results.
      rhl7FlushReport($arep);
      $porow = false;
      $pcrow = false;
      if ($a[2]) $in_orderid = intval($a[2]);
    }

    else if ($a[0] == 'NTE' && $context == 'ORC') {
      // TBD? Is this ever used?
    }

    else if ($a[0] == 'OBR') {
      $context = $a[0];
      rhl7FlushResult($ares);
      // Next line will do something only if there was a report with no results.
      rhl7FlushReport($arep);
      $procedure_report_id = 0;
      if ($a[2]) $in_orderid = intval($a[2]);
      $tmp = explode($d2, $a[4]);
      $in_procedure_code = $tmp[0];
      $in_procedure_name = $tmp[1];
      $in_report_status = rhl7ReportStatus($a[25]);
      if (empty($porow)) {
        $porow = sqlQuery("SELECT * FROM procedure_order WHERE " .
          "procedure_order_id = ?", array($in_orderid));
        // The order must already exist. Currently we do not handle electronic
        // results returned for manual orders.
        if (empty($porow)) {
          return xl('Procedure order') . " '$in_orderid' " . xl('was not found');
        }
        if ($in_encounter) {
          if ($porow['encounter_id'] != $in_encounter) {
            return xl('Encounter ID') .
              " '" . $porow['encounter_id'] . "' " .
              xl('for OBR placer order number') .
              " '$in_orderid' " .
              xl('does not match the PV1 encounter number') .
              " '$in_encounter'";
          }
        }
        else {
          // They did not return an encounter number to verify, so more checking
          // might be done here to make sure the patient seems to match.
        }
      }
      // Find the order line item (procedure code) that matches this result.
      $pcquery = "SELECT pc.* FROM procedure_order_code AS pc " .
        "WHERE pc.procedure_order_id = ? AND pc.procedure_code = ? " .
        "ORDER BY procedure_order_seq LIMIT 1";
      $pcrow = sqlQuery($pcquery, array($in_orderid, $in_procedure_code));
      if (empty($pcrow)) {
        // There is no matching procedure in the order, so it must have been
        // added after the original order was sent, either as a manual request
        // from the physician or as a "reflex" from the lab.
        // procedure_source = '2' indicates this.
        sqlInsert("INSERT INTO procedure_order_code SET " .
          "procedure_order_id = ?, " .
          "procedure_code = ?, " .
          "procedure_name = ?, " .
          "procedure_source = '2'",
          array($in_orderid, $in_procedure_code, $in_procedure_name));
        $pcrow = sqlQuery($pcquery, array($in_orderid, $in_procedure_code));
      }
      $arep = array();
      $arep['procedure_order_id'] = $in_orderid;
      $arep['procedure_order_seq'] = $pcrow['procedure_order_seq'];
      $arep['date_collected'] = rhl7DateTime($a[7]);
      $arep['date_report'] = substr(rhl7DateTime($a[22]), 0, 10);
      $arep['report_status'] = $in_report_status;
      $arep['report_notes'] = '';
    }

    else if ($a[0] == 'NTE' && $context == 'OBR') {
      $arep['report_notes'] .= rhl7Text($a[3]) . "\n";
    }

    else if ($a[0] == 'OBX') {
      $context = $a[0];
      rhl7FlushResult($ares);
      if (!$procedure_report_id) {
        $procedure_report_id = rhl7FlushReport($arep);
      }
      $ares = array();
      $ares['procedure_report_id'] = $procedure_report_id;
      // OBX-5 can be a very long string of text with "~" as line separators.
      // The first line of comments is reserved for such things.
      if (strlen($a[5]) > 200) {
        $ares['result_data_type'] = 'L';
        $ares['result'] = '';
        $ares['comments'] = rhl7Text($a[5]) . $commentdelim;
      }
      else {
        $ares['result_data_type'] = substr($a[2], 0, 1); // N, S or F
        $ares['result'] = rhl7Text($a[5]);
        $ares['comments'] = $commentdelim;
      }
      $tmp = explode($d2, $a[3]);
      $ares['result_code'] = rhl7Text($tmp[0]);
      $ares['result_text'] = rhl7Text($tmp[1]);
      $ares['date'] = rhl7DateTime($a[14]);
      $ares['facility'] = rhl7Text($a[15]);
      $ares['units'] = rhl7Text($a[6]);
      $ares['range'] = rhl7Text($a[7]);
      $ares['abnormal'] = rhl7Abnormal($a[8]); // values are lab dependent
      $ares['result_status'] = rhl7ReportStatus($a[11]);
    }

    else if ($a[0] == 'NTE' && $context == 'OBX') {
      $ares['comments'] .= rhl7Text($a[3]) . $commentdelim;
    }

    // Add code here for any other segment types that may be present.

    else {
      return xl('Segment name') . " '${a[0]}' " . xl('is misplaced or unknown');
    }
  }

  rhl7FlushResult($ares);
  // Next line will do something only if there was a report with no results.
  rhl7FlushReport($arep);
  return '';
}

/**
 * Poll all eligible labs for new results and store them in the database.
 *
 * @param  array   &$messages  Receives messages of interest.
 * @return string  Error text, or empty if no errors.
 */
function poll_hl7_results(&$messages) {
  global $srcdir;

  $messages = array();
  $filecount = 0;
  $badcount = 0;

  $ppres = sqlStatement("SELECT * FROM procedure_providers ORDER BY name");

  while ($pprow = sqlFetchArray($ppres)) {
    $protocol = $pprow['protocol'];
    $remote_host = $pprow['remote_host'];
    $hl7 = '';

    if ($protocol == 'SFTP') {
      ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "$srcdir/phpseclib");
      require_once("$srcdir/phpseclib/Net/SFTP.php");
      // Compute the target path name.
      $pathname = '.';
      if ($pprow['results_path']) $pathname = $pprow['results_path'] . '/' . $pathname;
      // Connect to the server and enumerate files to process.
      $sftp = new Net_SFTP($remote_host);
      if (!$sftp->login($pprow['login'], $pprow['password'])) {
        return xl('Login to remote host') . " '$remote_host' " . xl('failed');
      }
      $files = $sftp->nlist($pathname);
      foreach ($files as $file) {
        if (substr($file, 0, 1) == '.') continue;
        ++$filecount;
        $hl7 = $sftp->get("$pathname/$file");
        // Archive the results file.
        $prpath = $GLOBALS['OE_SITE_DIR'] . "/procedure_results";
        if (!file_exists($prpath)) mkdir($prpath);
        $prpath .= '/' . $pprow['ppid'];
        if (!file_exists($prpath)) mkdir($prpath);
        $fh = fopen("$prpath/$file", 'w');
        if ($fh) {
          fwrite($fh, $hl7);
          fclose($fh);
        }
        else {
          $messages[] = xl('File') . " '$file' " . xl('cannot be archived, ignored');
          ++$badcount;
          continue;
        }
        // Now delete it from its ftp directory.
        if (!$sftp->delete("$pathname/$file")) {
          $messages[] = xl('File') . " '$file' " . xl('cannot be deleted, ignored');
          ++$badcount;
          continue;
        }
        // Parse and process its contents.
        $msg = receive_hl7_results($hl7);
        if ($msg) {
          $messages[] = xl('Error processing file') . " '$file': " . $msg;
          ++$badcount;
          continue;
        }
        $messages[] = xl('New file') . " '$file' " . xl('processed successfully');
      }
    }

    // TBD: Insert "else if ($protocol == '???') {...}" to support other protocols.

  }

  if ($badcount) return "$badcount " . xl('error(s) encountered from new results');

  return '';
}
?>
