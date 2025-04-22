<?php

/**
 * package   OpenEMR
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * author-AI Gemini, Cascade
 * All rights reserved
 * Copyright (c) 2025.
 */

namespace OpenEMR\Reports\Encounter;

class EncounterReportData
{
    public function getEncounters(array $filters = []): array
    {
        $sql = "
        SELECT
            e.id,
            e.date,
            e.encounter,
            e.pid,
            e.provider_id,
            pc.pc_catdesc,
            CONCAT(u.lname, ', ', u.fname) AS provider,
            CONCAT(p.lname, ', ', p.fname) AS patient,
            CASE WHEN es.tid IS NOT NULL THEN 'Signed' ELSE 'Not Signed' END AS status,
            GROUP_CONCAT(DISTINCT f.form_name SEPARATOR ', ') AS form_list,
            SUM(CASE WHEN esf.tid IS NOT NULL THEN 1 ELSE 0 END) AS signed_forms_count,
            (
                SELECT GROUP_CONCAT(b.code SEPARATOR ', ')
                FROM billing b
                WHERE b.pid = e.pid AND b.encounter = e.encounter
                GROUP BY b.pid, b.encounter
            ) AS coding
        FROM form_encounter e
        JOIN users u ON u.id = e.provider_id
        JOIN patient_data p ON p.pid = e.pid
        LEFT JOIN openemr_postcalendar_categories pc ON pc.pc_catid = e.pc_catid
        LEFT JOIN forms f ON f.encounter = e.encounter AND f.pid = e.pid
        LEFT JOIN esign_signatures es ON es.tid = e.encounter AND es.table = 'form_encounter'
        LEFT JOIN esign_signatures esf ON esf.tid = f.form_id AND esf.table = CONCAT('form_', f.formdir)
        WHERE 1 = 1
    ";

        $params = [];

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND e.date <= ?";
            $params[] = $filters['date_to'];
        }

        // Apply facility filter
        if (!empty($filters['facility']) && $filters['facility'] !== 'all') {
            $sql .= " AND e.facility_id = ?";
            $params[] = $filters['facility'];
        }

        // Apply provider filter
        if (!empty($filters['provider']) && $filters['provider'] !== 'all') {
            $sql .= " AND e.provider_id = ?";
            $params[] = $filters['provider'];
        }

        $sql .= " GROUP BY e.id ORDER BY e.date DESC";

        $results = sqlStatement($sql, $params);
        $encounters = [];

        while ($row = sqlFetchArray($results)) {
            $encounters[] = [
                'id' => $row['id'] ?? '',
                'date' => $row['date'] ?? '',
                'encounter' => $row['encounter'] ?? '',
                'pid' => $row['pid'] ?? '',
                'provider' => $row['provider'] ?? '',
                'patient' => $row['patient'] ?? '',
                'status' => $row['status'] ?? 'Not Signed',
                'category' => $row['pc_catdesc'] ?? '',
                'encounter_number' => '',
                'pc_catdesc' => $row['pc_catdesc'] ?? '',
                'forms' => $row['form_list'] ?? '',
                'signed_forms_count' => $row['signed_forms_count'] ?? 0,
                'coding' => $row['coding'] ?? ''
            ];
        }

        return $encounters;
    }

    public function getEncounterCount(array $filters = [])
    {
        $sql = "SELECT count(*) AS encounter_count FROM form_encounter WHERE 1";
        $params = [];

        if (isset($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        if (isset($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }

        if (isset($filters['facility']) && is_numeric($filters['facility'])) {
            $sql .= " AND facility_id = ?";
            $params[] = $filters['facility'];
        }

        if (isset($filters['provider']) && is_numeric($filters['provider'])) {
            $sql .= " AND provider_id = ?";
            $params[] = $filters['provider'];
        }
        // ... Add other filters ...

        if (!empty($params)) {
            $result = sqlStatement($sql, $params);
        } else {
            $result = sqlStatement($sql);
        }

        if ($result) {
            $row = sqlFetchArray($result);
            return $row; // Corrected: Return the first element of the row
        } else {
            return 0;
        }
    }

    public function getEncounterSummary(array $filters): array
    {
        $sqlBindArray = array();

        $query = "SELECT u.id as provider_id,
                        CONCAT(u.lname, ', ', u.fname) AS provider,
                        COUNT(fe.encounter) as encounter_count,
                        fe.date
                 FROM form_encounter AS fe
                 LEFT JOIN users AS u ON u.id = fe.provider_id
                 WHERE 1=1 ";

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query .= "AND fe.date >= ? AND fe.date <= ? ";
            array_push($sqlBindArray, $filters['date_from'] . '%', $filters['date_to'] . '%');
        }

        if ($filters['provider'] !== 'all') {
            $query .= "AND fe.provider_id = ? ";
            array_push($sqlBindArray, $filters['provider']);
        }

        if ($filters['facility'] !== 'all') {
            $query .= "AND fe.facility_id = ? ";
            array_push($sqlBindArray, $filters['facility']);
        }

        $query .= "GROUP BY provider ORDER BY fe.date";

        $result = sqlStatement($query, $sqlBindArray);
        $summary = [];

        while ($row = sqlFetchArray($result)) {
            $summary[] = [
                'provider_id' => $row['provider_id'],
                'provider_name' => trim($row['provider']),
                'encounter_count' => $row['encounter_count']
            ];
        }

        return $summary;
    }
}
