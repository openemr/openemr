<?php

/**
 * AppointmentService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class AppointmentService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function validate($appointment)
    {
        $validator = new Validator();

        $validator->required('pc_eid')->numeric();
        $validator->required('pc_catid')->numeric();
        $validator->required('pc_title')->lengthBetween(2, 150);
        $validator->required('pc_duration')->numeric();
        $validator->required('pc_hometext')->string();
        $validator->required('pc_apptstatus')->string();
        $validator->required('pc_eventDate')->datetime('Y-m-d');
        $validator->required('pc_startTime')->length(5); // HH:MM is 5 chars
        $validator->required('pc_facility')->numeric();
        $validator->required('pc_billing_location')->numeric();

        return $validator->validate($appointment);
    }

    public function getAppointmentsForPatient($pid)
    {
        $sqlBindArray = array();

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
            $sql .= " WHERE pd.pid = ?";
            array_push($sqlBindArray, $pid);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

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
        $sql .= "     pc_pid=?,";
        $sql .= "     pc_catid=?,";
        $sql .= "     pc_title=?,";
        $sql .= "     pc_duration=?,";
        $sql .= "     pc_hometext=?,";
        $sql .= "     pc_eventDate=?,";
        $sql .= "     pc_apptstatus=?,";
        $sql .= "     pc_startTime=?,";
        $sql .= "     pc_endTime=?,";
        $sql .= "     pc_facility=?,";
        $sql .= "     pc_billing_location=?,";
        $sql .= "     pc_informant=1,";
        $sql .= "     pc_eventstatus=1,";
        $sql .= "     pc_sharing=1";

        $results = sqlInsert(
            $sql,
            array(
                $pid,
                $data["pc_catid"],
                $data["pc_title"],
                $data["pc_duration"],
                $data["pc_hometext"],
                $data["pc_eventDate"],
                $data['pc_apptstatus'],
                $startTime,
                $endTime,
                $data["pc_facility"],
                $data["pc_billing_location"]
            )
        );

        return $results;
    }

    public function delete($eid)
    {
        return sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", $eid);
    }
}
