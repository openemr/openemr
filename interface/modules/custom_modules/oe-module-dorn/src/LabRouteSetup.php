<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

class LabRouteSetup
{
    public static function createUpdateProcedureProviders($ppid, $labName, $npi, $labGuid, $uuid, $labAccountNumber)
    {
        if ($ppid > 0) {
            LabRouteSetup::updateProcedureProviders($ppid, $labName, $npi, $labGuid, $uuid, $labAccountNumber);
        } else {
            $ppid = LabRouteSetup::createProcedureProviders($labName, $npi, $labGuid, $uuid, $labAccountNumber);
        }
        return $ppid;
    }

    public static function updateProcedureProviders($ppid, $labName, $npi, $labGuid, $uuid, $labAccountNumber)
    {
        $send_app_id = "OEMR";
        $send_fac_id = $labAccountNumber ?? '';
        $recv_app_id = "DORN";
        $recv_fac_id = "LAB";
        $DorP = "P";
        $direction = "B";
        $protocol = "DORN";
        $remote_host = "";
        $orders_path = "";
        $results_path = "";
        $notes = "created automatically - LabGuid:" . $labGuid;
        $lab_director = $uuid;
        $active = 1;
        $type = null;

        // Update the existing procedure provider
        $sql = "UPDATE procedure_providers SET
            name = ?, npi = ?, send_app_id = ?, send_fac_id = ?, recv_app_id = ?
            ,recv_fac_id = ?, DorP = ?, direction = ?, protocol = ?, remote_host = ?,orders_path = ?,results_path = ?,notes = ?
            ,lab_director = ?, active = ?,type = ?
        WHERE ppid = ?";
        $sqlarr = [
            $labName, $npi, $send_app_id, $send_fac_id, $recv_app_id,
            $recv_fac_id, $DorP, $direction, $protocol, $remote_host, $orders_path, $results_path, $notes,
            $lab_director, $active, $type, $ppid];
        sqlStatement($sql, $sqlarr);
    }
    public static function createProcedureProviders($labName, $npi, $labGuid, $uuid, $labAccountNumber)
    {
        $ppid = null;

        $send_app_id = "OEMR";
        $send_fac_id = $labAccountNumber ?? '';
        $recv_app_id = "DORN";
        $recv_fac_id = "LAB";
        $DorP = "P";
        $direction = "B";
        $protocol = "DORN";
        $remote_host = "";
        $orders_path = "";
        $results_path = "";
        $notes = "created automatically - LabGuid:" . $labGuid;
        $lab_director = $uuid;
        $active = 1;
        $type = null;

        $ppid = LabRouteSetup::findProcedureProvider($npi, $active, $notes);
        if ($ppid == null) {
            $sql_pp_insert = "INSERT INTO procedure_providers (name, npi, send_app_id, send_fac_id, recv_app_id
            ,recv_fac_id, DorP, direction, protocol, remote_host,orders_path,results_path,notes
            ,lab_director, active,type)
            VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $sql_pp_insert_sqlarr = [
                $labName, $npi, $send_app_id, $send_fac_id, $recv_app_id,
                $recv_fac_id, $DorP, $direction, $protocol, $remote_host, $orders_path, $results_path, $notes,
                $lab_director, $active, $type
            ];

            $result = sqlStatement($sql_pp_insert, $sql_pp_insert_sqlarr);

            if (sqlNumRows($result) <= 0) {
                $ppid = LabRouteSetup::findProcedureProvider($npi, $active, $notes);
            }
        }
        return $ppid;
    }
    public static function getProcedureIdProviderByLabGuid($labGuid)
    {
        $sql = "SELECT DISTINCT dr.ppid
                FROM `mod_dorn_routes` dr
            WHERE dr.lab_guid = ?";
        $records = sqlStatement($sql, [$labGuid]);
        return $records;
    }
    public static function getRouteSetup($labGuid, $routeGuid)
    {
        $sql = "SELECT dr.lab_guid, dr.route_guid, dr.ppid, dr.uid, dr.lab_name, dr.text_line_break_character
                FROM `mod_dorn_routes` dr
                INNER JOIN procedure_providers pp on
                    pp.ppid = dr.ppid
                INNER JOIN users u on
                    u.id = dr.uid
            WHERE dr.lab_guid = ? AND dr.route_guid = ?";
        $record = sqlQuery($sql, [$labGuid, $routeGuid]);
        return $record;
    }
    public static function findProcedureProvider($npi, $active, $notes)
    {
        $sql_pp_search = "SELECT ppid FROM procedure_providers
        WHERE npi = ? AND active = ? AND notes LIKE CONCAT('%', ?, '%') LIMIT 1";
        $record = sqlQuery($sql_pp_search, [$npi, $active, $notes]);
        return $record['ppid'];
    }
    public static function createDornRoute($labName, $routeGuid, $labGuid, $ppid, $uid, $lineBreakChar, $labAccountNumber)
    {
        $sql = "INSERT INTO mod_dorn_routes (lab_guid, lab_name, ppid, route_guid, uid, text_line_break_character, lab_account_number)
                VALUES (?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE lab_name = VALUES(lab_name), ppid = VALUES(ppid),
                uid = VALUES(uid), text_line_break_character = VALUES(text_line_break_character), lab_account_number = VALUES(lab_account_number)";

        $sqlarr = [$labGuid, $labName, $ppid, $routeGuid, $uid, $lineBreakChar, $labAccountNumber];
        $result = sqlStatement($sql, $sqlarr);

        if (sqlNumRows($result) <= 0) {
            return true;
        }

        return false;
    }
}
