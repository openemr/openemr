<?php

/**
 * package   OpenEMR
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * author-AI Gemini, Cascade, and ChatGPT
 * All rights reserved
 * Copyright (c) 2025.
 */

namespace OpenEMR\Reports\Encounter;

use OpenEMR\Common\Database\QueryUtils;


class EncounterReportData
{
    // START AI GENERATED CODE
    public function getEncounters(array $filters = [], $paginate = true): array
    {
        $sql = "
        SELECT
            e.id,
            e.date,
            e.encounter,
            e.pid,
            e.provider_id,
            pc.pc_catname,
            CONCAT(u.lname, ', ', u.fname) AS provider,
            CONCAT(p.lname, ', ', p.fname) AS patient,
            GROUP_CONCAT(DISTINCT f.form_name SEPARATOR ', ') AS form_list,
            SUM(CASE WHEN esf.tid IS NOT NULL AND esf.is_lock = 1 THEN 1 ELSE 0 END) AS signed_forms_count,
            -- Encounter-level signer (if exists and locked)
            CASE
                WHEN MAX(CASE WHEN es.tid IS NOT NULL AND es.is_lock = 1 THEN 1 ELSE 0 END) = 1
                THEN MAX(CASE WHEN es.tid IS NOT NULL AND es.is_lock = 1 THEN CONCAT(es_signer.lname, ', ', es_signer.fname) END)
                ELSE NULL
            END AS encounter_signer,
            -- Form-level signer (if exists and locked)
            CASE
                WHEN MAX(CASE WHEN esf.tid IS NOT NULL AND esf.is_lock = 1 THEN 1 ELSE 0 END) = 1
                THEN MAX(CASE WHEN esf.tid IS NOT NULL AND esf.is_lock = 1 THEN CONCAT(form_signer.lname, ', ', form_signer.fname) END)
                ELSE NULL
            END AS form_signer,
            (
                SELECT GROUP_CONCAT(DISTINCT b.code SEPARATOR ', ')
                FROM billing b
                WHERE b.pid = e.pid AND b.encounter = e.encounter
                AND b.code IS NOT NULL AND b.code != ''
            ) AS coding
        FROM form_encounter e
        JOIN users u ON u.id = e.provider_id
        JOIN patient_data p ON p.pid = e.pid
        LEFT JOIN openemr_postcalendar_categories pc ON pc.pc_catid = e.pc_catid
        LEFT JOIN forms f ON f.encounter = e.encounter AND f.pid = e.pid AND f.deleted = 0
        -- Encounter-level signatures
        LEFT JOIN esign_signatures es ON es.tid = e.encounter AND es.`table` = 'form_encounter'
        LEFT JOIN users es_signer ON es_signer.id = es.uid
        -- Form-level signatures
        LEFT JOIN esign_signatures esf ON esf.tid = f.form_id AND esf.`table` = 'forms'
        LEFT JOIN users form_signer ON form_signer.id = esf.uid
        WHERE 1 = 1
    ";

        $params = [];

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.date >= ?";
            $params[] = $this->formatDate($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND e.date <= ?";
            $params[] = $this->formatDate($filters['date_to']);
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

        // Signature filtering: Show all signed documents (locked or unlocked)
        if (!empty($filters['signed_only'])) {
            // Show encounters with encounter-level signatures
            // This includes both:
            // 1. Encounters signed at encounter level
            // 2. Individual forms signed at form level
            $sql .= " AND (
                (es.tid IS NOT NULL)
                OR (esf.tid IS NOT NULL)
            )";
        }
        
        $sql .= " AND f.formdir != 'newpatient'";
        $sql .= " GROUP BY e.id ORDER BY e.date DESC";

        $results = QueryUtils::fetchRecords($sql, $params);
        $encounters = [];

        foreach ($results as $row) {
            $encounters[] = [
                'id' => $row['id'] ?? '',
                'date' => $row['date'] ?? '',
                'encounter' => $row['encounter'] ?? '',
                'pid' => $row['pid'] ?? '',
                'provider' => $row['provider'] ?? '',
                'patient' => $row['patient'] ?? '',
                'category' => $row['pc_catname'] ?? '',
                'encounter_number' => '',
                'forms' => $row['form_list'] ?? '',
                'signed_forms_count' => $row['signed_forms_count'] ?? 0,
                'coding' => $row['coding'] ?? '',
                'encounter_signer' => $row['encounter_signer'] ?? null,
                'form_signer' => $row['form_signer'] ?? null,
            ];
        }

        return $encounters;
    }

    public function getEncounterCount(array $filters = []): array
    {
        $sql = "SELECT count(*) AS encounter_count FROM form_encounter WHERE 1";
        $params = [];

        if (isset($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $this->formatDate($filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $this->formatDate($filters['date_to']);
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

        $result = QueryUtils::querySingleRow($sql, $params);
        
        return $result ?: ['encounter_count' => 0];
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
            array_push($sqlBindArray, $this->formatDate($filters['date_from']) . '%', $this->formatDate($filters['date_to']) . '%');
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

        $results = QueryUtils::fetchRecords($query, $sqlBindArray);
        $summary = [];

        foreach ($results as $row) {
            $summary[] = [
                'provider_id' => $row['provider_id'],
                'provider_name' => trim($row['provider']),
                'encounter_count' => $row['encounter_count']
            ];
        }

        return $summary;
    }

    //function formate date this function needs to return yyyymmdd check the date format first and return the formatted date
    public function formatDate($date): string
    {
        return date('Ymd', strtotime($date));
    }
} // END AI GENERATED CODE
