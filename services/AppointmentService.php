<?php
/**
 * AppointmentService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

class AppointmentService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function getAppointmentsForPatient($pid)
    {
        $sql = "SELECT pce.pc_eid,
                       pd.fname,
                       pd.lname,
                       pd.DOB,
                       pce.pc_apptstatus,
                       pce.pc_eventDate,
                       pce.pc_startTime,
                       pce.pc_endTime,
              	       pce.pc_facility,
                       pce.pc_billing_location,
                       f1.name as facility_name,
                       f2.name as billing_location_name
                       FROM openemr_postcalendar_events as pce
                       LEFT JOIN facility as f1 ON pce.pc_facility = f1.id
                       LEFT JOIN facility as f2 ON pce.pc_billing_location = f2.id
                       LEFT JOIN patient_data as pd ON pd.pid = pce.pc_pid";

        if ($pid) {
            $sql .= " WHERE pd.pid = " . add_escape_custom($pid);
        }

        $statementResults = sqlStatement($sql);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getAppointment($eid)
    {
        $sql = "SELECT pce.pc_eid,
                       pd.fname,
                       pd.lname,
                       pd.DOB,
                       pce.pc_apptstatus,
                       pce.pc_eventDate,
                       pce.pc_startTime,
                       pce.pc_endTime,
              	       pce.pc_facility,
                       pce.pc_billing_location,
                       f1.name as facility_name,
                       f2.name as billing_location_name
                       FROM openemr_postcalendar_events as pce
                       LEFT JOIN facility as f1 ON pce.pc_facility = f1.id
                       LEFT JOIN facility as f2 ON pce.pc_billing_location = f2.id
                       LEFT JOIN patient_data as pd ON pd.pid = pce.pc_pid
                       WHERE pce.pc_eid = ?";

        return sqlQuery($sql, array($eid));
    }

    public function insert($pid, $data)
    {
        $startTime = date("H:i:s", strtotime($data['pc_startTime']));
        $endTime = $startTime + $data['pc_duration'];

        $sql  = " INSERT INTO openemr_postcalendar_events SET";
        $sql .= "     pc_pid='" . add_escape_custom($pid) . "',";
        $sql .= "     pc_catid='" . add_escape_custom($data["pc_catid"]) . "',";
        $sql .= "     pc_title='" . add_escape_custom($data["pc_title"]) . "',";
        $sql .= "     pc_duration='" . add_escape_custom($data["pc_duration"]) . "',";
        $sql .= "     pc_hometext='" . add_escape_custom($data["pc_hometext"]) . "',";
        $sql .= "     pc_eventDate='" . add_escape_custom($data["pc_eventDate"]) . "',";
        $sql .= "     pc_apptstatus='" . add_escape_custom($data['pc_apptstatus']) . "',";
        $sql .= "     pc_startTime='" . add_escape_custom($startTime) . "',";
        $sql .= "     pc_endTime='" . add_escape_custom($endTime) . "',";
        $sql .= "     pc_facility='" . add_escape_custom($data["pc_facility"]) . "',";
        $sql .= "     pc_billing_location='" . add_escape_custom($data["pc_billing_location"]) . "',";
        $sql .= "     pc_informant=1,";
        $sql .= "     pc_eventstatus=1,";
        $sql .= "     pc_sharing=1";

        $results = sqlInsert($sql);

        return $results;
    }
}
