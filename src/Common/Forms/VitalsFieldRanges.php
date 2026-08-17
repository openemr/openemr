<?php

/**
 * VitalsFieldRanges provides clinically-informed validation ranges for vitals form fields.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Davit Mnatsakanyan
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

final class VitalsFieldRanges
{
    /**
     * @return array<string, array{label: string, min: float, max: float, warningMin: float, warningMax: float, metricMin?: float, metricMax?: float, metricWarningMin?: float, metricWarningMax?: float}>
     */
    public static function getRanges(): array
    {
        return [
            'weight' => [
                'label' => xl('Weight'),
                'min' => 0, 'max' => 2000, 'warningMin' => 0.1, 'warningMax' => 1500,
                'metricMin' => 0, 'metricMax' => 910, 'metricWarningMin' => 0.05, 'metricWarningMax' => 680,
            ],
            'height' => [
                'label' => xl('Height/Length'),
                'min' => 0, 'max' => 150, 'warningMin' => 1, 'warningMax' => 108,
                'metricMin' => 0, 'metricMax' => 381, 'metricWarningMin' => 2.5, 'metricWarningMax' => 274,
            ],
            'bps' => [
                'label' => xl('BP Systolic'),
                'min' => 0, 'max' => 400, 'warningMin' => 20, 'warningMax' => 300,
            ],
            'bpd' => [
                'label' => xl('BP Diastolic'),
                'min' => 0, 'max' => 300, 'warningMin' => 10, 'warningMax' => 200,
            ],
            'pulse' => [
                'label' => xl('Pulse'),
                'min' => 0, 'max' => 500, 'warningMin' => 10, 'warningMax' => 350,
            ],
            'respiration' => [
                'label' => xl('Respiration'),
                'min' => 0, 'max' => 150, 'warningMin' => 2, 'warningMax' => 100,
            ],
            'temperature' => [
                'label' => xl('Temperature'),
                'min' => 0, 'max' => 120, 'warningMin' => 80, 'warningMax' => 115,
                'metricMin' => 0, 'metricMax' => 48.9, 'metricWarningMin' => 26.7, 'metricWarningMax' => 46.1,
            ],
            'oxygen_saturation' => [
                'label' => xl('Oxygen Saturation'),
                'min' => 0, 'max' => 100, 'warningMin' => 70, 'warningMax' => 100,
            ],
            'oxygen_flow_rate' => [
                'label' => xl('Oxygen Flow Rate'),
                'min' => 0, 'max' => 200, 'warningMin' => 0.1, 'warningMax' => 100,
            ],
            'inhaled_oxygen_concentration' => [
                'label' => xl('Inhaled Oxygen Concentration'),
                'min' => 0, 'max' => 100, 'warningMin' => 21, 'warningMax' => 100,
            ],
            'head_circ' => [
                'label' => xl('Head Circumference'),
                'min' => 0, 'max' => 75, 'warningMin' => 0.1, 'warningMax' => 50,
                'metricMin' => 0, 'metricMax' => 190, 'metricWarningMin' => 0.25, 'metricWarningMax' => 127,
            ],
            'waist_circ' => [
                'label' => xl('Waist Circumference'),
                'min' => 0, 'max' => 150, 'warningMin' => 0.1, 'warningMax' => 100,
                'metricMin' => 0, 'metricMax' => 381, 'metricWarningMin' => 0.25, 'metricWarningMax' => 254,
            ],
            'ped_weight_height' => [
                'label' => xl('Pediatric Weight Height Percentile'),
                'min' => 0, 'max' => 100, 'warningMin' => 3, 'warningMax' => 97,
            ],
            'ped_bmi' => [
                'label' => xl('Pediatric BMI Percentile'),
                'min' => 0, 'max' => 100, 'warningMin' => 3, 'warningMax' => 97,
            ],
            'ped_head_circ' => [
                'label' => xl('Pediatric Head Circumference Percentile'),
                'min' => 0, 'max' => 100, 'warningMin' => 3, 'warningMax' => 97,
            ],
        ];
    }

    /**
     * @return array{label: string, min: float, max: float, warningMin: float, warningMax: float, metricMin?: float, metricMax?: float, metricWarningMin?: float, metricWarningMax?: float}|null
     */
    public static function getRangeForField(string $fieldName): ?array
    {
        return self::getRanges()[$fieldName] ?? null;
    }
}
