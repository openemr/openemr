<?php
/**
 * Remote access to a WordPress Patient Portal.
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

// Note: In Ubuntu this requires the php5-curl package.
// http://www.php.net/manual/en/function.curl-setopt.php has many comments and examples.

if (!$GLOBALS['gbl_portal_cms_enable']) die(xlt('CMS Portal not enabled!'));

function cms_portal_call($args) {
  $portal_url = $GLOBALS['gbl_portal_cms_address'] . "/wp-content/plugins/sunset-patient-portal/webserve.php";
  $args['login'   ] = $GLOBALS['gbl_portal_cms_username'];
  $args['password'] = $GLOBALS['gbl_portal_cms_password'];

  if (($phandle = curl_init($portal_url)) === FALSE) {
    die(text(xl('Unable to access URL') . " '$portal_url'"));
  }
  curl_setopt($phandle, CURLOPT_POST          , TRUE);
  curl_setopt($phandle, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($phandle, CURLOPT_POSTFIELDS    , $args);
  if (($presult = curl_exec($phandle)) === FALSE) {
    die(text('curl_exec ' . xl('failed') . ': ' . curl_error($phandle)));
  }
  curl_close($phandle);
  // With JSON-over-HTTP we would use json_decode($presult,TRUE) here.
  return unserialize($presult);
}

// Look up the OpenEMR patient matching this request. More or less than 1 is an error.
function lookup_openemr_patient($wp_login) {
  if (empty($wp_login)) die(xlt('The patient was not logged in when submitting this form'));
  $ptres = sqlStatement("SELECT pid FROM patient_data WHERE cmsportal_login = ?", array($wp_login));
  if (sqlNumRows($ptres) < 1) die(xlt('There is no patient with portal login') . " '$wp_login'");
  if (sqlNumRows($ptres) > 1) die(xlt('There are multiple patients with portal login') . " '$wp_login'");
  $ptrow = sqlFetchArray($ptres);
  return $ptrow['pid'];
}

// This constructs a LBF field value string from form data provided by the portal.
//
function cms_field_to_lbf($data_type, $field_id, &$fldarr) {
  $newvalue = '';
  if ($data_type == '23') {
    // Type Exam Results is special, pieced together from multiple CMS fields.
    // For example layout field "exams" might find CMS fields "exams:brs" = 1
    // and "exams:cec" = 2 and aggregate them into the value "brs:1|cec:2".
    foreach ($fldarr as $key => $value) {
      if (preg_match('/^' . $field_id . ':(\w+)/', $key, $matches)) {
        if ($newvalue !== '') $newvalue .= '|';
        $newvalue .= $matches[1] . ":$value:";
      }
    }
  }
  else {
    if (isset($fldarr[$field_id])) $newvalue = $fldarr[$field_id];
    if ($newvalue !== '') {
      // Lifestyle Status.
      if ($data_type == '28') {
        $newvalue = "|$newvalue$field_id|";
      }
      // Smoking Status.
      else if ($data_type == '32') {
        // See the smoking_status list for these array values:
        $ssarr = array('current' => 1, 'quit' => 3, 'never' => 4, 'not_applicable' => 9);
        $ssindex = isset($ssarr[$newvalue]) ? $ssarr[$newvalue] : 0;
        $newvalue = "|$newvalue$field_id||$ssindex";
      }
      // Checkbox list.
      else if (is_array($newvalue)) {
        $tmp = '';
        foreach ($newvalue as $value) {
          if ($tmp !== '') $tmp .= '|';
          $tmp .= $value;
        }
        $newvalue = $tmp;
      }
    }
  }
  return $newvalue;
}
?>
