<?php

declare(strict_types=1);

namespace GeneralDermatology\Services;

use GeneralDermatology\Models\Patient;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\FacilityService;

/**
 * Patient / encounter / facility data for PDF and reports.
 */
class PatientDataService
{
    public function getPatientData(int $pid): Patient
    {
        $patientData = QueryUtils::querySingleRow(
            "SELECT p.fname, p.lname, p.DOB, p.sex, p.pid,
                    DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), p.DOB)), '%Y') + 0 AS age
             FROM patient_data p
             WHERE p.pid = ?",
            [$pid]
        ) ?: [];

        $patientData['allergies'] = $this->getPatientAllergies($pid);

        return new Patient($patientData);
    }

    private function getPatientAllergies(int $pid): string
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `title` FROM `lists` WHERE `type` = 'allergy' AND `pid` = ?",
            [$pid]
        ) ?: [];

        $allergies = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title !== '') {
                $allergies[] = $title;
            }
        }

        return $allergies !== [] ? implode(', ', $allergies) : 'None';
    }

    /**
     * @return array<string, mixed>
     */
    public function getEncounterData(int $encounterId): array
    {
        $encounterData = QueryUtils::querySingleRow(
            "SELECT fe.encounter AS id, fe.date, fe.reason, fe.provider_id,
                    u.fname AS provider_fname, u.lname AS provider_lname,
                    opc.pc_catname AS visit_description
             FROM form_encounter fe
             LEFT JOIN users u ON fe.provider_id = u.id
             LEFT JOIN openemr_postcalendar_categories opc ON fe.pc_catid = opc.pc_catid
             WHERE fe.encounter = ?",
            [$encounterId]
        ) ?: [];

        if (!empty($encounterData['provider_fname'])) {
            $encounterData['provider_name'] = trim(
                (string) $encounterData['provider_fname'] . ' ' . (string) ($encounterData['provider_lname'] ?? '')
            );
        } else {
            $encounterData['provider_name'] = 'Unknown';
        }

        if (!empty($encounterData['date'])) {
            $visitDate = explode(' ', (string) $encounterData['date']);
            $encounterData['note_date'] = date('F j, Y', strtotime($visitDate[0]));
        } else {
            $encounterData['note_date'] = date('F j, Y');
        }

        return $encounterData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFacilityData(): array
    {
        if (class_exists(FacilityService::class)) {
            $facilityService = new FacilityService();
            $facility = $facilityService->getPrimaryBusinessEntity();
            if (is_array($facility) && $facility !== []) {
                return $facility;
            }
        }

        return QueryUtils::querySingleRow(
            'SELECT * FROM facility WHERE primary_business_entity = 1 LIMIT 1',
            []
        ) ?: [];
    }
}
