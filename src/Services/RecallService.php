<?php

/**
 * Patient Recall Service
 *
 * Core recall management functionality - vendor neutral
 * Handles CRUD operations for patient_recalls table
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@medfetch.com>
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;

class RecallService extends BaseService
{
    public function __construct()
    {
        parent::__construct('patient_recalls');
    }

    /**
     * Get list of facilities for filter dropdown
     */
    public function getFacilities()
    {
        $sql = "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * Get list of providers for filter dropdown
     */
    public function getProviders()
    {
        $sql = "SELECT id, fname, lname 
                FROM users 
                WHERE authorized = 1 AND active = 1
                ORDER BY lname, fname";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * Get recalls for display on recall board
     * 
     * @param string $fromDate Start date (YYYY-MM-DD)
     * @param string $toDate End date (YYYY-MM-DD)
     * @param int $facilityId Optional facility filter
     * @param int $providerId Optional provider filter
     * @param int $patientId Optional patient ID filter
     * @param string $patientName Optional patient name search
     * @return array Array of recall records with patient data
     */
    public function getRecalls($fromDate, $toDate, $facilityId = null, $providerId = null, $patientId = null, $patientName = null)
    {
        $sql = "SELECT r.*, 
                       p.fname, p.lname, p.DOB, p.phone_cell, p.phone_home, p.email,
                       TIMESTAMPDIFF(YEAR, p.DOB, CURDATE()) as age,
                       u.fname as provider_fname, u.lname as provider_lname,
                       f.name as facility_name
                FROM patient_recalls r
                JOIN patient_data p ON p.pid = r.r_pid
                LEFT JOIN users u ON u.id = r.r_provider
                LEFT JOIN facility f ON f.id = r.r_facility
                WHERE r.r_eventDate >= ? 
                  AND r.r_eventDate <= ?
                  AND (p.deceased_date IS NULL OR p.deceased_date = '0000-00-00')";
        
        $params = [$fromDate, $toDate];
        
        // Add filters
        if ($facilityId) {
            $sql .= " AND r.r_facility = ?";
            $params[] = $facilityId;
        }
        
        if ($providerId) {
            $sql .= " AND r.r_provider = ?";
            $params[] = $providerId;
        }
        
        if ($patientId) {
            $sql .= " AND r.r_pid = ?";
            $params[] = $patientId;
        }
        
        if ($patientName) {
            $sql .= " AND (p.fname LIKE ? OR p.lname LIKE ?)";
            $params[] = "%$patientName%";
            $params[] = "%$patientName%";
        }
        
        $sql .= " ORDER BY r.r_eventDate ASC, p.lname ASC, p.fname ASC";
        
        return QueryUtils::fetchRecords($sql, $params);
    }

    /**
     * Create a new recall
     * 
     * @param array $data Recall data (pid, r_eventDate, r_reason, r_provider, r_facility)
     * @return int|false The new recall ID or false on failure
     */
    public function createRecall($data)
    {
        // Validate required fields
        if (empty($data['pid']) || empty($data['r_eventDate']) || empty($data['r_provider']) || empty($data['r_facility'])) {
            (new SystemLogger())->error("RecallService::createRecall - Missing required fields");
            return false;
        }
        
        $sql = "INSERT INTO patient_recalls 
                (r_pid, r_eventDate, r_reason, r_provider, r_facility, r_created) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        QueryUtils::sqlStatementThrowException($sql, [
            $data['pid'],
            $data['r_eventDate'],
            $data['r_reason'] ?? '',
            $data['r_provider'],
            $data['r_facility']
        ]);
        
        // Return the new recall ID
        $result = QueryUtils::fetchRecords("SELECT LAST_INSERT_ID() as id");
        return $result[0]['id'] ?? false;
    }

    /**
     * Update an existing recall
     * 
     * @param int $recallId Recall ID
     * @param array $data Updated recall data
     * @return bool Success
     */
    public function updateRecall($recallId, $data)
    {
        $sql = "UPDATE patient_recalls 
                SET r_eventDate = ?, 
                    r_reason = ?,
                    r_provider = ?,
                    r_facility = ?
                WHERE r_ID = ?";
        
        QueryUtils::sqlStatementThrowException($sql, [
            $data['r_eventDate'],
            $data['r_reason'] ?? '',
            $data['r_provider'],
            $data['r_facility'],
            $recallId
        ]);
        
        return true;
    }

    /**
     * Delete a recall
     * 
     * @param int $recallId Recall ID
     * @return bool Success
     */
    public function deleteRecall($recallId)
    {
        $sql = "DELETE FROM patient_recalls WHERE r_ID = ?";
        QueryUtils::sqlStatementThrowException($sql, [$recallId]);
        return true;
    }

    /**
     * Get patient data by PID
     * 
     * @param int $pid Patient ID
     * @return array|null Patient data
     */
    public function getPatientData($pid)
    {
        $sql = "SELECT pid, fname, lname, DOB, phone_cell, phone_home, email, 
                       TIMESTAMPDIFF(YEAR, DOB, CURDATE()) as age
                FROM patient_data 
                WHERE pid = ?";
        
        $result = QueryUtils::fetchRecords($sql, [$pid]);
        return $result[0] ?? null;
    }

    /**
     * Check if patient has any recalls
     * 
     * @param int $pid Patient ID
     * @return int Count of active recalls for patient
     */
    public function getPatientRecallCount($pid)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM patient_recalls 
                WHERE r_pid = ? AND r_eventDate >= CURDATE()";
        
        $result = QueryUtils::fetchRecords($sql, [$pid]);
        return $result[0]['count'] ?? 0;
    }
}
