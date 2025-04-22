<?php

namespace OpenEMR\Reports\Encounter;

class EncounterReportFormatter
{
    public function formatEncounters(array $encounters): array
    {
        $formattedEncounters = [];
        foreach ($encounters as $encounter) {
            $formattedEncounters[] = $this->formatEncounterRow($encounter);
        }
        return $formattedEncounters;
    }

    public function formatEncounterRow(array $encounter): array
    {
        // Example formatting. Adapt this based on the specific fields and formatting needs.
        return [
            'provider' => $encounter['provider'], // Assuming a method to get provider name
            'date' => date('Y-m-d', strtotime($encounter['date'])),
            'patient' => $encounter['patient'], // Assuming a method to get patient name
            'id' => $encounter['id'],
            'pid' => $encounter['pid'],
            'status' => $encounter['status'],
            'encounter' => $encounter['encounter'],
            'category' => $encounter['pc_catdesc'],
            'forms' => $encounter['forms'],
            'encounter_number' => $encounter['encounter_nr'],
            'form' => $encounter['form_id'], // Assuming form_id represents the form
            'coding' => $encounter['pc_cid'], // Assuming pc_cid represents coding
        ];
    }

    public function formatSummary(array $summaryData): array
    {
        $formattedSummary = [];
        $totalEncounters = 0;

        foreach ($summaryData as $row) {
            $formattedSummary[] = [
                'provider_id' => $row['provider_id'],
                'provider_name' => $row['provider_name'],
                'encounter_count' => $row['encounter_count']
            ];
            $totalEncounters += $row['encounter_count'];
        }

        return [
            'providers' => $formattedSummary,
            'total_encounters' => $totalEncounters
        ];
    }

    private function getProviderName($providerId)
    {
        // Implement logic to get provider name from the database based on providerId
        // Example:
        $providerName = sqlQuery("SELECT CONCAT(lname, ', ', fname) AS name FROM users WHERE id = ?", [$providerId]);
        if ($providerName) {
            return $providerName['name'];
        }
        return '';
    }

    private function getPatientName($patientId)
    {
        // Implement logic to get patient name from the database based on patientId
        // Example:
        $patientName = sqlQuery("SELECT CONCAT(lname, ', ', fname) FROM patient_data WHERE pid = ?", [$patientId]);
        if ($patientName) {
            return sqlFetchArray($patientName)[0];
        }
        return '';
    }
}
