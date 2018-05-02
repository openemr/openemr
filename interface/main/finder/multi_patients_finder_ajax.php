<?php

/**
 * Ajax interface for popup of multi select patient.
 *
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc");

$type = $_GET['type'];
$search = $_GET['search'];

switch ($type) {
    case 'by-id':
        // load patients ids for select2.js library, expect receive 'text' and 'id'.
        $results=getPatientId("%$search%", 'pubpid as text, pid as id', 'pubpid');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'by-name':
        // load patients names for select2.js library, expect receive 'text' and 'id'.
        $results=getPatientLnames("%$search%", 'pid as id, CONCAT(lname, ", ",fname)  as text', 'lname ASC, fname ASC');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'patient-by-id':
        $results=getPatientData($search, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        //clean data using 'text' function
        $results=array_map('text', $results);
        $results['DOB'] = oeFormatShortDate($results['DOB']);
        break;
}

$output = array('results' => $results);
echo json_encode($output);
die;
