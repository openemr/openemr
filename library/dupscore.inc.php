<?php

/**
 * Note this may be included by CLI scripts, so don't do anything web-specific here!
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// The SQL returned by this function is an expression that computes the duplication
// score between two patient_data table rows p1 and p2.
//
function getDupScoreSQL(): string
{
    return
        "6 * (TRIM(p1.email) != '' AND TRIM(p1.email) = TRIM(p2.email)) + " .
        "6 * (p1.DOB IS NOT NULL AND p2.DOB IS NOT NULL AND p1.DOB = p2.DOB) + " .
        "6 * (LOWER(CONCAT(TRIM(p1.fname), '', TRIM(p1.lname))) = LOWER(CONCAT(TRIM(p2.fname), '', TRIM(p2.lname)))) + " .
        "6 * (TRIM(p1.sex) != '' AND TRIM(p1.sex) = TRIM(p2.sex)) + " .
        "2 * (SOUNDEX(p1.lname) = SOUNDEX(p2.lname)) + " .
        "1 * (" .
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
        ")";
}
