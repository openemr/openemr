<?php

/**
 * Encounter Report Formatter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Encounter\Formatter;

use OpenEMR\Common\Logging\SystemLogger;

class EncounterReportFormatter
{
    private $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
    }

    /**
     * Format encounter data for display
     *
     * @param array $encounter
     * @return array
     * @throws \Exception
     */
    public function formatEncounter(array $encounter): array
    {
        try {
            return [
                'id' => $encounter['id'] ?? null,
                'date' => $this->formatDate($encounter['date'] ?? null),
                'patient_name' => $this->formatPatientName($encounter),
                'patient_dob' => $this->formatDate($encounter['patient_dob'] ?? null, 'm/d/Y'),
                'provider' => $encounter['provider_name'] ?? 'N/A',
                'facility' => $encounter['facility_name'] ?? 'N/A',
                'reason' => $encounter['reason'] ?? '',
                'actions' => $this->getActionButtons($encounter)
            ];
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error formatting encounter: ' . $e->getMessage(),
                ['encounter' => $encounter, 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Format multiple encounters
     *
     * @param array $encounters
     * @return array
     */
    public function formatEncounters(array $encounters): array
    {
        return array_map([$this, 'formatEncounter'], $encounters);
    }

    /**
     * Format date string
     *
     * @param string|null $date
     * @param string $format
     * @return string
     */
    private function formatDate(?string $date, string $format = 'm/d/Y H:i:s'): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format($format);
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('Error formatting date: ' . $e->getMessage(),
                ['date' => $date, 'format' => $format]);
            return $date; // Return original if formatting fails
        }
    }

    /**
     * Format patient name
     *
     * @param array $encounter
     * @return string
     */
    private function formatPatientName(array $encounter): string
    {
        $name = [];

        if (!empty($encounter['patient_lname'])) {
            $name[] = $encounter['patient_lname'];
        }

        if (!empty($encounter['patient_fname'])) {
            $name[] = $encounter['patient_fname'];
        }

        return !empty($name) ? implode(', ', $name) : 'Unknown Patient';
    }

    /**
     * Generate action buttons for each encounter
     *
     * @param array $encounter
     * @return string
     */
    private function getActionButtons(array $encounter): string
    {
        if (empty($encounter['id']) || empty($encounter['pid']) || empty($encounter['encounter'])) {
            return '';
        }

        $buttons = [
            $this->createButton('View', 'fa-eye',
                "top.restoreSession('patient_file/encounter/encounter_top.php?set_encounter=" .
                attr_url($encounter['encounter']) . "&pid=" . attr_url($encounter['pid']) . "')",
                'btn-primary', 'View encounter details'),

            $this->createButton('Summary', 'fa-file-text',
                "top.restoreSession('patient_file/encounter/encounter_top.php?set_encounter=" .
                attr_url($encounter['encounter']) . "&pid=" . attr_url($encounter['pid']) . "&content=enc')",
                'btn-info', 'View encounter summary')
        ];

        return '<div class="btn-group" role="group">' . implode('', $buttons) . '</div>';
    }

    /**
     * Create a button HTML
     *
     * @param string $label
     * @param string $icon
     * @param string $onclick
     * @param string $class
     * @param string $title
     * @return string
     */
    private function createButton(string $label, string $icon, string $onclick, string $class, string $title): string
    {
        return sprintf(
            '<button type="button" class="btn btn-sm %s me-1" onclick="%s" title="%s">' .
            '<i class="fa %s"></i> <span class="d-none d-md-inline">%s</span></button>',
            attr($class),
            attr($onclick),
            attr($title),
            attr($icon),
            text($label)
        );
    }

    /**
     * Format statistics for display
     *
     * @param array $stats
     * @return array
     */
    public function formatStatistics(array $stats): array
    {
        return [
            'total_encounters' => number_format($stats['total_encounters'] ?? 0),
            'unique_patients' => number_format($stats['unique_patients'] ?? 0),
            'unique_providers' => number_format($stats['unique_providers'] ?? 0),
            'first_encounter' => $this->formatDate($stats['first_encounter_date'] ?? null, 'm/d/Y'),
            'last_encounter' => $this->formatDate($stats['last_encounter_date'] ?? null, 'm/d/Y')
        ];
    }

    /**
     * Format data for export
     *
     * @param array $encounters
     * @param string $format
     * @return array
     */
    public function formatForExport(array $encounters, string $format = 'csv'): array
    {
        $formatted = [];

        // Add headers
        $headers = [
            'Date', 'Patient Name', 'Date of Birth', 'Provider',
            'Facility', 'Reason', 'Encounter ID', 'Patient ID'
        ];

        $formatted[] = $headers;

        // Add data rows
        foreach ($encounters as $encounter) {
            $formatted[] = [
                $this->formatDate($encounter['date'] ?? null, 'Y-m-d H:i:s'),
                $this->formatPatientName($encounter),
                $this->formatDate($encounter['patient_dob'] ?? null, 'Y-m-d'),
                $encounter['provider_name'] ?? 'N/A',
                $encounter['facility_name'] ?? 'N/A',
                $encounter['reason'] ?? '',
                $encounter['encounter'] ?? '',
                $encounter['pid'] ?? ''
            ];
        }

        return $formatted;
    }
}
