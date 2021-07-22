<?php

/**
 * Note this may be included by CLI scripts, so don't do anything web-specific here!
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// The SQL returned by this function is an expression that computes the duplication
// score between two patient_data table rows p1 and p2.
//
function getDupScoreSQL()
{
    return
    //  5 First name
    "5 * (SOUNDEX(p1.fname) = SOUNDEX(p2.fname)) + " .
    //  3 Last name
    "3 * (SOUNDEX(p1.lname) = SOUNDEX(p2.lname)) + " .
    //  4 Any phone number
    "4 * (" .
      "(TRIM(p1.phone_home) != '' AND ( " .
      "REPLACE(REPLACE(p1.phone_home, '-', ''), ' ', '') IN ( " .
      "REPLACE(REPLACE(p2.phone_home, '-', ''), ' ', ''), " .
      "REPLACE(REPLACE(p2.phone_biz , '-', ''), ' ', ''), " .
      "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', '')))) " .
    "OR (TRIM(p1.phone_biz) != '' AND ( " .
      "REPLACE(REPLACE(p1.phone_biz , '-', ''), ' ', '') IN ( " .
      "REPLACE(REPLACE(p2.phone_biz , '-', ''), ' ', ''), " .
      "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', '')))) " .
    "OR (TRIM(p1.phone_cell) != '' AND ( " .
      "REPLACE(REPLACE(p1.phone_cell, '-', ''), ' ', '') = " .
      "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', ''))) " .
    ") + " .
    //  6 Birth date
    "6 * (p1.DOB IS NOT NULL AND p2.DOB IS NOT NULL AND p1.DOB = p2.DOB) + " .
    //  7 Email
    "7 * (TRIM(p1.email) != '' AND TRIM(p1.email) = TRIM(p2.email)) + " .
    // 15 Government ID
    "15 * (TRIM(p1.ss) != '' AND TRIM(p1.ss) = TRIM(p2.ss))";
}
