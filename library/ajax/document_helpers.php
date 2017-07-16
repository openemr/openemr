<?php
/**
 * Document Helper Functions for New Documents Module.
 *
 * Copyright (C) 2017-2018 Jerry Padgett <sjpadgett@gmail.com>
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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */


require_once(dirname(__FILE__) . "/../../interface/globals.php");

$term = isset($_GET["term"]) ? filter_input(INPUT_GET, 'term') : '';

function get_patients_list($term)
{
    $term = "%" . $term . "%";
    $clear = "- " . xl("Reset to no patient") . " -";
    $response = sqlStatement("SELECT Concat(patient_data.fname, ' ',patient_data.lname) as label, patient_data.pid as value FROM patient_data HAVING label LIKE ? ORDER BY patient_data.lname ASC", array($term));
    $resultpd[] = array(
        'label' => $clear,
        'value' => '00'
    );
    while ($row = sqlFetchArray($response)) {
        if ($GLOBALS['pid'] == $row['value']) {
            $row['value'] = "00";
            $row['label'] = xl("Locked") . "-" . xl("In Use") . ":" . $row['label'];
        }

        $resultpd[] = $row;
    }

    echo json_encode($resultpd);
}

get_patients_list($term);
