<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

class PatientFixtureManager
{
    private $patients = [];

    /**
     * Create a test patient
     *
     * @param array $data Patient data
     * @return array Created patient data
     */
    public function createPatient($data = [])
    {
        $sql = "INSERT INTO patient_data SET 
                pid = ?,
                pubpid = ?,
                lname = ?,
                fname = ?,
                DOB = ?,
                sex = ?,
                status = ?,
                providerID = ?,
                uuid = ?";

        $uuid = UuidRegistry::uuidToString((new UuidRegistry(['table_name' => 'patient_data']))->createUuid());
        $pid = $data['pid'] ?? $this->getUnusedPid();
        
        $params = [
            $pid,
            $data['pubpid'] ?? 'TEST' . $pid,
            $data['lname'] ?? 'Test' . $pid,
            $data['fname'] ?? 'Patient' . $pid,
            $data['DOB'] ?? '1980-01-01',
            $data['sex'] ?? 'Male',
            $data['status'] ?? 'active',
            $data['providerID'] ?? 1,
            $uuid
        ];

        QueryUtils::sqlInsert($sql, $params);
        
        $patient = [
            'id' => $pid,
            'uuid' => $uuid,
            'pid' => $pid,
            'lname' => $params[2],
            'fname' => $params[3]
        ];
        
        $this->patients[] = $patient;
        return $patient;
    }

    /**
     * Get a single test patient
     * 
     * @return array
     */
    public function getSinglePatient()
    {
        if (empty($this->patients)) {
            return $this->createPatient();
        }
        
        return $this->patients[0];
    }

    /**
     * Get an unused patient ID
     *
     * @return int
     */
    private function getUnusedPid()
    {
        $result = QueryUtils::fetchRecordsNoLog(
            "SELECT MAX(pid) as max_pid FROM patient_data"
        );
        
        return (int)($result[0]['max_pid'] ?? 0) + 1;
    }

    /**
     * Remove all test patients
     */
    public function removePatientFixtures()
    {
        if (empty($this->patients)) {
            return;
        }

        $pids = array_column($this->patients, 'pid');
        $placeholders = str_repeat('?,', count($pids) - 1) . '?';
        
        // Delete from patient_data
        $sql = "DELETE FROM patient_data WHERE pid IN ($placeholders)";
        QueryUtils::sqlStatementNoLog($sql, $pids);
        
        // Clear the patients array
        $this->patients = [];
    }
}
