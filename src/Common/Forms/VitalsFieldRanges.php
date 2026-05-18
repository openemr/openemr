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
     * @return array<string, array{min: float, max: float, warningMin: float, warningMax: float, metricMin?: float, metricMax?: float, metricWarningMin?: float, metricWarningMax?: float}>
     */
    public static function getRanges(): array
    {
        return [
            'weight' => [
                'min' => 0, 'max' => 2000, 'warningMin' => 0.1, 'warningMax' => 1500,
                'metricMin' => 0, 'metricMax' => 910, 'metricWarningMin' => 0.05, 'metricWarningMax' => 680,
            ],
            'height' => [
                'min' => 0, 'max' => 150, 'warningMin' => 1, 'warningMax' => 108,
                'metricMin' => 0, 'metricMax' => 381, 'metricWarningMin' => 2.5, 'metricWarningMax' => 274,
            ],
            'bps' => [
                'min' => 0, 'max' => 400, 'warningMin' => 20, 'warningMax' => 300,
            ],
            'bpd' => [
                'min' => 0, 'max' => 300, 'warningMin' => 10, 'warningMax' => 200,
            ],
            'pulse' => [
                'min' => 0, 'max' => 500, 'warningMin' => 10, 'warningMax' => 350,
            ],
            'respiration' => [
                'min' => 0, 'max' => 150, 'warningMin' => 2, 'warningMax' => 100,
            ],
            'temperature' => [
                'min' => 0, 'max' => 120, 'warningMin' => 80, 'warningMax' => 115,
                'metricMin' => 0, 'metricMax' => 48.9, 'metricWarningMin' => 26.7, 'metricWarningMax' => 46.1,
            ],
            'oxygen_saturation' => [
                'min' => 0, 'max' => 100, 'warningMin' => 0, 'warningMax' => 100,
            ],
            'oxygen_flow_rate' => [
                'min' => 0, 'max' => 200, 'warningMin' => 0, 'warningMax' => 100,
            ],
            'inhaled_oxygen_concentration' => [
                'min' => 0, 'max' => 100, 'warningMin' => 0, 'warningMax' => 100,
            ],
            'head_circ' => [
                'min' => 0, 'max' => 75, 'warningMin' => 0, 'warningMax' => 50,
                'metricMin' => 0, 'metricMax' => 190, 'metricWarningMin' => 0, 'metricWarningMax' => 127,
            ],
            'waist_circ' => [
                'min' => 0, 'max' => 150, 'warningMin' => 0, 'warningMax' => 100,
                'metricMin' => 0, 'metricMax' => 381, 'metricWarningMin' => 0, 'metricWarningMax' => 254,
            ],
            'ped_weight_height' => [
                'min' => 0, 'max' => 100, 'warningMin' => 0, 'warningMax' => 100,
            ],
            'ped_bmi' => [
                'min' => 0, 'max' => 100, 'warningMin' => 0, 'warningMax' => 100,
            ],
            'ped_head_circ' => [
                'min' => 0, 'max' => 100, 'warningMin' => 0, 'warningMax' => 100,
            ],
        ];
    }

    /**
     * @return array{min: float, max: float, warningMin: float, warningMax: float, metricMin?: float, metricMax?: float, metricWarningMin?: float, metricWarningMax?: float}|null
     */
    public static function getRangeForField(string $fieldName): ?array
    {
        return self::getRanges()[$fieldName] ?? null;
    }
}
