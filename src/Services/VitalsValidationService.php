<?php

/**
 * Vitals validation service.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

class VitalsValidationService
{
    private const VITAL_RANGES = [
        'bps' => [
            'warning_min' => 80,
            'warning_max' => 180,
            'label' => 'BP Systolic (mmHg)'
        ],
        'bpd' => [
            'warning_min' => 40,
            'warning_max' => 120,
            'label' => 'BP Diastolic (mmHg)'
        ],
        'pulse' => [
            'warning_min' => 40,
            'warning_max' => 200,
            'label' => 'Pulse (bpm)'
        ],
        'respiration' => [
            'warning_min' => 8,
            'warning_max' => 50,
            'label' => 'Respiration (breaths/min)'
        ],
        'temperature' => [
            'warning_min' => 95,
            'warning_max' => 105,
            'label' => 'Temperature (°F)'
        ],
        'weight' => [
            'warning_min' => 5.5,
            'warning_max' => 650,
            'label' => 'Weight (lbs)'
        ],
        'height' => [
            'warning_min' => 11,
            'warning_max' => 98,
            'label' => 'Height (in)'
        ],
        'oxygen_saturation' => [
            'warning_min' => 90,
            'warning_max' => 100,
            'label' => 'Oxygen Saturation (%)'
        ],
    ];

    private const NON_NEGATIVE_FIELDS = [
        'bps',
        'bpd',
        'pulse',
        'respiration',
        'temperature',
        'weight',
        'height',
        'oxygen_saturation',
        'oxygen_flow_rate',
        'inhaled_oxygen_concentration',
        'BMI',
        'head_circ',
        'waist_circ',
        'ped_weight_height',
        'ped_bmi',
        'ped_head_circ',
    ];

    /**
     * @param array<string, mixed> $vitals
     * @return array{errors: array<string, string>, warnings: array<string, string>}
     */
    public function validate(array $vitals): array
    {
        $rangeValidation = $this->validateVitalRanges($vitals);
        $constraintWarnings = $this->validateVitalConstraints($vitals);

        return [
            'errors' => $rangeValidation['errors'],
            'warnings' => array_merge($rangeValidation['warnings'], $constraintWarnings)
        ];
    }

    /**
     * @param array<string, mixed> $vitals
     * @return array{errors: array<string, string>, warnings: array<string, string>}
     */
    private function validateVitalRanges(array $vitals): array
    {
        $errors = [];
        $warnings = [];

        foreach ($vitals as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $hasRange = isset(self::VITAL_RANGES[$field]);
            $mustBeNonNegative = in_array($field, self::NON_NEGATIVE_FIELDS, true);
            if (!$hasRange && !$mustBeNonNegative) {
                continue;
            }

            $range = self::VITAL_RANGES[$field] ?? null;
            $fieldLabel = $range['label'] ?? ucwords(str_replace('_', ' ', $field));
            if (!is_numeric($value)) {
                $errors[$field] = $fieldLabel . ' must be numeric.';
                continue;
            }

            $numValue = (float) $value;
            if ($mustBeNonNegative && $numValue < 0) {
                $errors[$field] = $fieldLabel . ' cannot be negative.';
                continue;
            }

            if (!$hasRange || $range === null) {
                continue;
            }

            if ($numValue < $range['warning_min'] || $numValue > $range['warning_max']) {
                $warnings[$field] = $range['label'] . ' is outside typical range (' . $range['warning_min'] . '-' . $range['warning_max'] . '). You entered: ' . $value;
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * @param array<string, mixed> $vitals
     * @return array<string, string>
     */
    private function validateVitalConstraints(array $vitals): array
    {
        $warnings = [];
        $bps = null;
        $bpd = null;
        if (array_key_exists('bps', $vitals) && is_numeric($vitals['bps'])) {
            $bps = (float) $vitals['bps'];
        }
        if (array_key_exists('bpd', $vitals) && is_numeric($vitals['bpd'])) {
            $bpd = (float) $vitals['bpd'];
        }

        if ($bps !== null && $bpd !== null && $bps > 0 && $bpd > 0 && $bps < $bpd) {
            $warnings['bps'] = 'BP Systolic is less than BP Diastolic. Systolic: ' . $bps . ', Diastolic: ' . $bpd;
        }

        return $warnings;
    }
}
