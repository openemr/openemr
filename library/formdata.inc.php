<?php
/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
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
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

/**
 * Escape a parameter to prepare for a sql query.
 *
 * @param   string $s  Parameter to be escaped.
 * @return  string     Escaped parameter.
 */
function add_escape_custom($s) {
      //prepare for safe mysql insertion
      $s = mysql_real_escape_string($s);
      return $s;
}

/**
 * Escape a sql limit variable to prepare for a sql query.
 *
 * This will escape integers within the LIMIT ?, ? part of a sql query.
 * Note that there is a maximum value to these numbers, which is why
 * should only use for the LIMIT ? , ? part of the sql query and why
 * this is centralized to a function (in case need to upgrade this
 * function to support larger numbers in the future).
 *
 * @param   string $s  Limit variable to be escaped.
 * @return  string     Escaped limit variable.
 */
function escape_limit($s) {
      //prepare for safe mysql insertion
      $s = (int)$s;
      return $s;
}

/**
 * Escape/sanitize a sql sort order keyword variable to prepare for a sql query.
 *
 * This will escape/sanitize the sort order keyword. It is done by whitelisting
 * only certain keywords(asc,desc). If the keyword is illegal, then will default
 * to asc.
 *
 * @param   string $s  Sort order keyword variable to be escaped/sanitized.
 * @return  string     Escaped sort order keyword variable.
 */
function escape_sort_order($s) {
      $ok = array("asc","desc");
      $key = array_search(strtolower($s),$ok);
      return $ok[$key];
}

/**
 * Escape/sanitize a sql identifier variable to prepare for a sql query.
 *
 * This will escape/sanitize a sql identifier. There are two options provided by this funtion.
 * The first option is done by whitelisting ($whitelist_flag=true) and in this case
 * only certain identifiers (listed in the $whitelist_items array) can be used; if
 * there is no match, then it will default to the first item in the $whitelist_items array.
 * The second option is done by sanitizing ($whitelist_flag=false) and in this case
 * only US alphanumeric,'_' and '.' items are kept in the returned string. Note
 * the second option is still experimental as we figure out the ideal items to
 * filter out of the identifier. The first option is ideal if all the possible identifiers
 * are known, however we realize this may not always be the case.
 *
 * @param   string  $s                Sql identifier variable to be escaped/sanitized.
 * @param   boolean $whitelist_flag   True to use whitelisting method (See function description for details of whitelisting method).
 * @param   array   $whitelist_items  Items used in whitelisting method (See function description for details of whitelisting method).
 * @return  string                    Escaped/sanitized sql identifier variable.
 */
function escape_identifier($s,$whitelist_flag=FALSE,$whitelist_items) {
      if ($whitelist_flag) {
            // Only return an item within the whitelist_items
            //  (if no match, then it will return the first item in whitelist_items)
            $ok = $whitelist_items;
            $key = array_search($s,$ok);
            return $ok[$key];
      }
      else {
            // Return an item that has been "cleaned" up
            // (this is currently experimental)
            return preg_replace('/[^a-zA-Z0-9_.]/','',$s);
      }
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * Function to manage POST, GET, and REQUEST variables.
 *
 * @param string $name name of the variable requested.
 * @param string $type 'P', 'G' for post or get data, otherwise uses request.
 * @param bool $istrim whether to use trim() on the data.
 * @return string variable requested, or empty string
 */
function formData($name, $type='P', $isTrim=false) {
  if ($type == 'P')
    $s = isset($_POST[$name]) ? $_POST[$name] : '';
  else if ($type == 'G')
    $s = isset($_GET[$name]) ? $_GET[$name] : '';
  else
    $s = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  
  return formDataCore($s,$isTrim);
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * Core function that will be called by formData.
 * Note it can also be called directly if preparing
 * normal variables (not GET,POST, or REQUEST)
 *
 * @param string $s
 * @param bool $istrim whether to use trim() on the data.
 * @return string
 */
function formDataCore($s, $isTrim=false) {
      //trim if selected
      if ($isTrim) {$s = trim($s);}
      //strip escapes
      $s = strip_escape_custom($s);
      //add escapes for safe database insertion
      $s = add_escape_custom($s);
      return $s;
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * Will remove escapes if needed (ie magic quotes turned on) from string
 * Called by above formDataCore() function to prepare for database insertion.
 * Can also be called directly if simply need to remove escaped characters
 * from a string before processing.
 *
 * @param string $s
 * @return string
 */
function strip_escape_custom($s) {
      //strip slashes if magic quotes turned on
      if (get_magic_quotes_gpc()) {$s = stripslashes($s);}
      return $s;
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * This function is only being kept to support
 * previous functionality. If you want to trim
 * variables, this should be done using above
 * functions.
 *
 * @param string $s
 * @return string
 */
function formTrim($s) {
  return formDataCore($s,true);
}
?>
