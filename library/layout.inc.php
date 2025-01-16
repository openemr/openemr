<?php

/**
 * Sql functions/classes for OpenEMR.
 *
 * Things related to layout based forms in general.
 *
 * Copyright (C) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * Copyright (c) 2022 Stephen Nielson <snielson@discoverandchange.com>
 * Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 */

// array of the data_types of the fields
// TODO: Move these all to a statically typed class with constants that can be referenced throughout the codebase!
$datatypes = array(
    "1"  => xl("List box"),
    "2"  => xl("Textbox"),
    "3"  => xl("Textarea"),
    "4"  => xl("Text-date"),
    "10" => xl("Providers"),
    "11" => xl("Providers NPI"),
    "12" => xl("Pharmacies"),
    "13" => xl("Squads"),
    "14" => xl("Organizations"),
    "15" => xl("Billing codes"),
    "16" => xl("Insurances"),
    "18" => xl("Visit Categories"),
    "21" => xl("Checkbox(es)"),
    "22" => xl("Textbox list"),
    "23" => xl("Exam results"),
    "24" => xl("Patient allergies"),
    "25" => xl("Checkboxes w/text"),
    "26" => xl("List box w/add"),
    "27" => xl("Radio buttons"),
    "28" => xl("Lifestyle status"),
    "31" => xl("Static Text"),
    "32" => xl("Smoking Status"),
    "33" => xl("Race/Ethnicity"),
    "34" => xl("NationNotes"),
    "35" => xl("Facilities"),
    "36" => xl("Multiple Select List"),
    "37" => xl("Lab Results"),
    "40" => xl("Image canvas"),
    "41" => xl("Patient Signature"),
    "42" => xl("User Signature"),
    "43" => xl("List box w/search"),
    "44" => xl("Multi-Select Facilties"),
    "45" => xl("Multi-Select Provider"),
    "46" => xl("List box w/comment"),
    "51" => xl("Patient"),
    "52" => xl("Previous Names"),
    "53" => xl("Patient Encounters List"),
    "54" => xl("Address List")
);

// These are the data types that can reference a list.
$typesUsingList = array(1, 21, 22, 23, 25, 26, 27, 32, 33, 34, 36, 37, 43, 46);

$sources = array(
    'F' => xl('Form'),
    'D' => xl('Patient'),
    'H' => xl('History'),
    'E' => xl('Visit'),
    'V' => xl('VisForm'),
);

$UOR = array(
    0 => xl('Unused'),
    1 => xl('Optional'),
    2 => xl('Required'),
);

$reservedColumnNames = [
    'patient_data' => [
        'id',
        'DOB',
        'title',
        'language',
        'fname',
        'lname',
        'mname',
        'street',
        'postal_code',
        'city',
        'state',
        'ss',
        'phone_home',
        'phone_cell',
        'date',
        'sex',
        'providerID',
        'email',
        'pubpid',
        'pid',
        'squad',
        'home_facility',
        'deceased_date',
        'deceased_reason',
        'allow_patient_portal',
        'soap_import_status',
        'email_direct',
        'dupscore',
        'cmsportal_login',
        'care_team_provider',
        'care_team_status',
        'billing_note',
        'uuid',
        'care_team_facility',
        'name_history',
        'care_team_status',
        'patient_groups',
        'additional_addresses',
    ],
    'history_data' => [
        'id',
        'date',
        'pid',
    ],
];

function isColumnReserved($table_name, $field_id) {
    global $reservedColumnNames;

    return in_array($field_id, $reservedColumnNames[$table_name] ?? []);
}

function getTableNameFromLayoutId($layout_id) {
    $prefix = substr($layout_id, 0, 3);

    // Skip LBF, LBT, FACUSR
    // Because they store data in vertical tables.
    if (in_array([$prefix, $layout_id], ['LBF', 'LBT', 'FACUSR'])) {
        return '';
    }

    return match ($prefix) {
        'DEM' => 'patient_data',
        'HIS' => 'history_data',
        'SRH' => 'lists_ippf_srh',
        'CON' => 'lists_ippf_con',
        'GCA' => 'lists_ippf_gcac',
        default => die(xlt('Internal error in getTableNameFromLayoutId') . '(' . text($layout_id) . ')'),
    };
}

function addColumn($layout_id, $field_id): void {
    $tablename = getTableNameFromLayoutId($layout_id);
    if (empty($tablename)) {
        return;
    }

    $column_tmp = sqlQuery("SHOW COLUMNS FROM `" . escape_table_name($tablename) . "` LIKE'" . add_escape_custom($field_id) . "'");
    // Column already exists.
    if (!empty($column_tmp)) {
        return;
    }

    sqlQuery("ALTER TABLE `" . escape_table_name($tablename) . "` ADD COLUMN `" . add_escape_custom($field_id) . "` TEXT");
}


function setLayoutTimestamp($layout_id)
{
    $query = "UPDATE layout_group_properties SET grp_last_update = CURRENT_TIMESTAMP " .
        "WHERE grp_form_id = ? AND grp_group_id = ''";
    sqlStatement($query, array($layout_id));
}
// Test options array for save
function encodeModifier($jsonArray)
{
    return $jsonArray !== null ? json_encode($jsonArray) : "";
}

function addField($layout_id, $data): void {
    $data_type = trim($data['newdatatype']);
    $max_length = $data_type == 3 ? 3 : 255;
    $listval = $data_type == 34 ? trim($data['contextName']) : trim($data['newlistid']);
    sqlStatement("INSERT INTO layout_options (" .
        " form_id, source, field_id, title, group_id, seq, uor, fld_length, fld_rows" .
        ", titlecols, datacols, data_type, edit_options, default_value, codes, description" .
        ", max_length, list_id, list_backup_id " .
        ") VALUES ( " .
        "'"  . add_escape_custom(trim($data['layout_id'])) . "'" .
        ",'" . add_escape_custom(trim($data['newsource'])) . "'" .
        ",'" . add_escape_custom(trim($data['newid'])) . "'" .
        ",'" . add_escape_custom($data['newtitle']) . "'" .
        ",'" . add_escape_custom(trim($data['newfieldgroupid'])) . "'" .
        ",'" . add_escape_custom(trim($data['newseq'])) . "'" .
        ",'" . add_escape_custom(trim($data['newuor'])) . "'" .
        ",'" . add_escape_custom(trim($data['newlengthWidth'])) . "'" .
        ",'" . add_escape_custom(trim($data['newlengthHeight'])) . "'" .
        ",'" . add_escape_custom(trim($data['newtitlecols'])) . "'" .
        ",'" . add_escape_custom(trim($data['newdatacols'])) . "'" .
        ",'" . add_escape_custom($data_type) . "'"                                  .
        ",'" . add_escape_custom(encodeModifier($data['newedit_options'] ?? null)) . "'" .
        ",'" . add_escape_custom(trim($data['newdefault'])) . "'" .
        ",'" . add_escape_custom(trim($data['newcodes'])) . "'" .
        ",'" . add_escape_custom(trim($data['newdesc'])) . "'" .
        ",'"    . add_escape_custom(trim($data['newmaxSize']))    . "'"  .
        ",'" . add_escape_custom($listval) . "'" .
        ",'" . add_escape_custom(trim($data['newbackuplistid'])) . "'" .
        " )");
}

function deleteColumn($layout_id, $field_id): void {
    $table_name = getTableNameFromLayoutId($layout_id);
    if (empty($tablename)) {
        return;
    }

    $table_tmp = sqlQuery(
        "SELECT `" . escape_sql_column_name($field_id, [$tablename]) .
        "` AS field_id FROM `" . escape_table_name($tablename) . "` WHERE " .
        "`" . escape_sql_column_name($field_id, [$tablename]) . "` IS NOT NULL AND `"
        . escape_sql_column_name($field_id, [$tablename]) . "` != '' LIMIT 1"
    );

    if (isset($table_tmp['field_id']) && isColumnReserved($tablename, $field_id)) {
        return;
    }

    // Check for History layouts to not delete them.
    $history_layout_count = sqlQuery(
        "SELECT COUNT(*) AS count FROM layout_options WHERE " .
        "form_id LIKE 'HIS%' AND form_id != ? AND field_id = ?",
        array($layout_id, $field_id)
    );

    if (!empty($history_layout_count['count'])) {
        return;
    }

    sqlQuery("ALTER TABLE `" . escape_table_name($tablename) . "` DROP COLUMN `" . add_escape_custom($field_id) . "`");
}
