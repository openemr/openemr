<?php
/**
 * Patient Service
 *
 * Copyright (C) 2017 Victor Kofia <victor.kofia@gmail.com>
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
 * @author  Victor Kofia <victor.kofia@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

class PatientService
{

  /**
   * In the case where a patient doesn't have a picture uploaded,
   * this value will be returned so that the document controller
   * can return an empty response.
   */
    private $patient_picture_fallback_id = -1;

    private $pid;

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
    }

  /**
   * TODO: This should go in the ChartTrackerService and doesn't have to be static.
   * @param $pid unique patient id
   * @return recordset
   */
    public static function getChartTrackerInformationActivity($pid)
    {
        $sql = "SELECT ct.ct_when,
                   ct.ct_userid,
                   ct.ct_location,
                   u.username,
                   u.fname,
                   u.mname,
                   u.lname
            FROM chart_tracker AS ct
            LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid
            WHERE ct.ct_pid = ?
            ORDER BY ct.ct_when DESC";
        return sqlStatement($sql, array($pid));
    }

  /**
   * TODO: This should go in the ChartTrackerService and doesn't have to be static.
   * @return recordset
   */
    public static function getChartTrackerInformation()
    {
        $sql = "SELECT ct.ct_when,
                   u.username,
                   u.fname AS ufname,
                   u.mname AS umname,
                   u.lname AS ulname,
                   p.pubpid,
                   p.fname,
                   p.mname,
                   p.lname
            FROM chart_tracker AS ct
            JOIN cttemp ON cttemp.ct_pid = ct.ct_pid AND cttemp.ct_when = ct.ct_when
            LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid
            LEFT OUTER JOIN patient_data AS p ON p.pid = ct.ct_pid
            WHERE ct.ct_userid != 0
            ORDER BY p.pubpid";
        return sqlStatement($sql);
    }

    public function getAll()
    {
        $sql = "SELECT id,
                   pid,
                   pubpid,
                   title, 
                   fname,
                   mname,
                   lname,
                   street, 
                   postal_code, 
                   city, 
                   state, 
                   country_code, 
                   phone_contact,
                   email
                   dob,
                   sex,
                   race,
                   ethnicity
                FROM patient_data";

        $statementResults = sqlStatement($sql);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOne()
    {
        $sql = "SELECT id,
                   pid,
                   pubpid,
                   title, 
                   fname,
                   mname,
                   lname,
                   street, 
                   postal_code, 
                   city, 
                   state, 
                   country_code, 
                   phone_contact,
                   email
                   dob,
                   sex,
                   race,
                   ethnicity
                FROM patient_data
                WHERE pid = ?";

        return sqlQuery($sql, $this->pid);
    }

    public function getEncounters()
    {
        $sql = "SELECT fe.*,
                       opc.pc_catname,
                       fa.name AS billing_facility_name
                       FROM form_encounter as fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility
                       WHERE pid=?
                       ORDER BY fe.id
                       DESC";

        $statementResults = sqlStatement($sql, $this->pid);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getEncounter($eid)
    {
        $sql = "SELECT fe.*,
                       opc.pc_catname,
                       fa.name AS billing_facility_name
                       FROM form_encounter as fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN facility as fa ON fa.id = fe.billing_facility
                       WHERE pid=? and fe.id=?
                       ORDER BY fe.id
                       DESC";

        $statementResults = sqlStatement($sql, array($this->pid, $eid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

  /**
   * @return number
   */
    public function getPatientPictureDocumentId()
    {
        $sql = "SELECT doc.id AS id
                 FROM documents doc
                 JOIN categories_to_documents cate_to_doc
                   ON doc.id = cate_to_doc.document_id
                 JOIN categories cate
                   ON cate.id = cate_to_doc.category_id
                WHERE cate.name LIKE ? and doc.foreign_id = ?";

        $result = sqlQuery($sql, array($GLOBALS['patient_photo_category_name'], $this->pid));

        if (empty($result) || empty($result['id'])) {
            return $this->patient_picture_fallback_id;
        }

        return $result['id'];
    }
}
