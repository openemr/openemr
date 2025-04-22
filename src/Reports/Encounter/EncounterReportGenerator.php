<?php

namespace OpenEMR\Reports\Encounter;

class EncounterReportGenerator
{
    public function generateReport(array $formattedEncounters, array $reportConfiguration = []): string
    {
        // Basic report generation logic. Will be improved when we start using Twig.

        $reportOutput = "<h1>Encounter Report</h1>";

        if (empty($formattedEncounters)) {
            $reportOutput .= "<p>No encounters found.</p>";
        } else {
            $reportOutput .= "<table>";
            $reportOutput .= "<thead><tr>";
            $reportOutput .= "<th>Encounter ID</th>";
            $reportOutput .= "<th>Patient ID</th>";
            $reportOutput .= "<th>Encounter Date</th>";
            $reportOutput .= "<th>Facility ID</th>";
            $reportOutput .= "<th>Provider ID</th>";
            $reportOutput .= "<th>Encounter Type</th>";
            $reportOutput .= "<th>Reason</th>";
            $reportOutput .= "</tr></thead>";
            $reportOutput .= "<tbody>";

            foreach ($formattedEncounters as $encounter) {
                $reportOutput .= "<tr>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['encounter_id']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['patient_id']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['encounter_date']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['facility_id']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['provider_id']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['encounter_type']) . "</td>";
                $reportOutput .= "<td>" . htmlspecialchars($encounter['reason']) . "</td>";
                $reportOutput .= "</tr>";
            }

            $reportOutput .= "</tbody></table>";
        }

        return $reportOutput;
    }
}
