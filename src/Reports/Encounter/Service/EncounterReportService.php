<?php

/**
 * Encounter Report Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Encounter\Service;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class EncounterReportService
{
    private $logger;
    private $facilityService;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->facilityService = new FacilityService();
    }

    /**
     * Get encounters based on filters
     *
     * @param array $filters
     * @return array
     */
    public function getEncounters(array $filters): array
    {
        $this->logger->debug('EncounterReportService: Getting encounters', ['filters' => $filters]);

        try {
            $query = $this->buildBaseQuery();
            $where = [];
            $params = [];

            // Add date range filter
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $where[] = 'fe.date BETWEEN ? AND ?';
                $params[] = $filters['date_from'];
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            // Add facility filter
            if (!empty($filters['facility_id'])) {
                $where[] = 'fe.facility_id = ?';
                $params[] = $filters['facility_id'];
            }

            // Add provider filter
            if (!empty($filters['provider_id'])) {
                $where[] = 'fe.provider_id = ?';
                $params[] = $filters['provider_id'];
            }

            // Add where clause if we have conditions
            if (!empty($where)) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            // Add sorting
            $query .= ' ORDER BY fe.date DESC';

            // Add pagination
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 25;
            $offset = ($page - 1) * $perPage;
            $query .= ' LIMIT ? OFFSET ?';
            $params[] = $perPage;
            $params[] = $offset;

            // Execute query
            $encounters = QueryUtils::fetchRecords($query, $params);
            
            // Get total count for pagination
            $total = $this->getTotalEncounters($filters);

            return [
                'data' => $encounters,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error fetching encounters: ' . $e->getMessage(), 
                ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Get total number of encounters for pagination
     *
     * @param array $filters
     * @return int
     */
    private function getTotalEncounters(array $filters): int
    {
        $query = 'SELECT COUNT(*) as total FROM form_encounter fe';
        $where = [];
        $params = [];

        // Add date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $where[] = 'fe.date BETWEEN ? AND ?';
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        // Add facility filter
        if (!empty($filters['facility_id'])) {
            $where[] = 'fe.facility_id = ?';
            $params[] = $filters['facility_id'];
        }

        // Add provider filter
        if (!empty($filters['provider_id'])) {
            $where[] = 'fe.provider_id = ?';
            $params[] = $filters['provider_id'];
        }

        // Add where clause if we have conditions
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $result = QueryUtils::fetchRecords($query, $params);
        return (int)($result[0]['total'] ?? 0);
    }

    /**
     * Build the base SQL query for fetching encounters
     *
     * @return string
     */
    private function buildBaseQuery(): string
    {
        return "
            SELECT 
                fe.id,
                fe.pid,
                fe.encounter,
                fe.date,
                fe.reason,
                fe.facility_id,
                f.name as facility_name,
                fe.provider_id,
                CONCAT(u.lname, ', ', u.fname) as provider_name,
                pd.lname as patient_lname,
                pd.fname as patient_fname,
                pd.DOB as patient_dob,
                pd.pid as patient_id
            FROM 
                form_encounter fe
            LEFT JOIN 
                facility f ON fe.facility_id = f.id
            LEFT JOIN 
                users u ON fe.provider_id = u.id
            LEFT JOIN
                patient_data pd ON fe.pid = pd.pid
        ";
    }

    /**
     * Get summary statistics for encounters
     *
     * @param array $filters
     * @return array
     */
    public function getEncounterStatistics(array $filters): array
    {
        $this->logger->debug('EncounterReportService: Getting encounter statistics', ['filters' => $filters]);

        try {
            $query = "
                SELECT 
                    COUNT(*) as total_encounters,
                    COUNT(DISTINCT fe.pid) as unique_patients,
                    COUNT(DISTINCT fe.provider_id) as unique_providers,
                    COUNT(DISTINCT fe.facility_id) as unique_facilities,
                    MIN(fe.date) as first_encounter_date,
                    MAX(fe.date) as last_encounter_date
                FROM 
                    form_encounter fe
                WHERE 1=1
            ";

            $params = [];

            // Add date range filter
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query .= ' AND fe.date BETWEEN ? AND ?';
                $params[] = $filters['date_from'];
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            // Add facility filter
            if (!empty($filters['facility_id'])) {
                $query .= ' AND fe.facility_id = ?';
                $params[] = $filters['facility_id'];
            }

            // Add provider filter
            if (!empty($filters['provider_id'])) {
                $query .= ' AND fe.provider_id = ?';
                $params[] = $filters['provider_id'];
            }

            $result = QueryUtils::fetchRecords($query, $params);
            return $result[0] ?? [];
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error fetching encounter statistics: ' . $e->getMessage(), 
                ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Export encounters to specified format
     *
     * @param array $filters
     * @param string $format
     * @return array
     */
    public function exportEncounters(array $filters, string $format = 'csv'): array
    {
        $this->logger->info('Exporting encounters', ['format' => $format, 'filters' => $filters]);

        try {
            // For large exports, we might want to process in chunks
            $filters['per_page'] = 1000; // Process 1000 records at a time
            $page = 1;
            $allData = [];
            $headers = null;

            do {
                $filters['page'] = $page;
                $result = $this->getEncounters($filters);
                
                // Set headers from first page
                if ($page === 1 && !empty($result['data'])) {
                    $headers = array_keys($result['data'][0]);
                    $allData[] = $headers;
                }

                // Add data rows
                foreach ($result['data'] as $row) {
                    $allData[] = array_values($row);
                }

                $page++;
            } while ($page <= ($result['total_pages'] ?? 0));

            return [
                'data' => $allData,
                'total' => $result['total'] ?? 0,
                'format' => $format
            ];
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error exporting encounters: ' . $e->getMessage(), 
                ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
