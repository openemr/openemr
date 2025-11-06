<?php

/**
 * package   OpenEMR
 * Written with Warp Terminal
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * author-AI Gemini, Cascade, and ChatGPT
 * All rights reserved
 * Copyright (c) 2025.
 */

namespace OpenEMR\Reports\Encounter;

class EncounterReportFormatter
{
    //Gemini & Cascade & Sherwin -AI GENERATED CODE
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
            'encounter' => $encounter['encounter'],
            'category' => $encounter['category'],
            'forms' => $encounter['forms'],
            'encounter_number' => $encounter['encounter_nr'],
            'form' => $encounter['form_id'], // Assuming form_id represents the form
            'coding' => $encounter['coding'], // Assuming pc_cid represents coding
            'encounter_signer' => $encounter['encounter_signer'] ?? null,
            'form_signer' => $encounter['form_signer'] ?? null,
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
}
