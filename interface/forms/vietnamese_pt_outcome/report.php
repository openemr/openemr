<?php

/**
 * Vietnamese PT Outcome Measures - report.php
 *
 * Summary view for encounter reports
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-GENERATED CODE - START
 * Generated with Claude Code (Anthropic) on 2025-11-22
 * This report template follows OpenEMR form report patterns
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

/**
 * Display Vietnamese PT Outcome Measures summary in encounter report
 *
 * @param int $pid Patient ID
 * @param int $encounter Encounter ID
 * @param int $cols Number of columns (legacy parameter)
 * @param int $id Form ID
 * @return void
 */
function vietnamese_pt_outcome_report($pid, $encounter, $cols, $id): void
{
    $data = formFetch("pt_outcome_measures", $id);

    if (!$data) {
        return;
    }

    // Measure type labels
    $measureTypes = [
        'ROM' => xlt('ROM'),
        'Strength' => xlt('Strength'),
        'Pain' => xlt('Pain'),
        'Function' => xlt('Function'),
        'Balance' => xlt('Balance')
    ];

    echo "<table class='table table-sm table-bordered'>";
    echo "<tr>";

    // Measure Type
    if (!empty($data['measure_type'])) {
        $measureType = $data['measure_type'];
        echo "<td><span class='font-weight-bold'>" . xlt('Measure Type') . ": </span>";
        echo "<span class='text'>" . text($measureTypes[$measureType] ?? $measureType) . "</span></td>";
    }

    // Current Value with unit
    if (isset($data['current_value'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Current') . ": </span>";
        echo "<span class='text'>" . text($data['current_value']);
        if (!empty($data['unit'])) {
            echo " " . text($data['unit']);
        }
        echo "</span></td>";
    }

    echo "</tr><tr>";

    // Baseline (if available)
    if (!empty($data['baseline_value'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Baseline') . ": </span>";
        echo "<span class='text'>" . text($data['baseline_value']);
        if (!empty($data['unit'])) {
            echo " " . text($data['unit']);
        }
        echo "</span></td>";
    }

    // Target (if available)
    if (!empty($data['target_value'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Target') . ": </span>";
        echo "<span class='text'>" . text($data['target_value']);
        if (!empty($data['unit'])) {
            echo " " . text($data['unit']);
        }
        echo "</span></td>";
    }

    echo "</tr>";

    // Progress calculation
    if (!empty($data['baseline_value']) && !empty($data['current_value']) && !empty($data['target_value'])) {
        $baseline = floatval($data['baseline_value']);
        $current = floatval($data['current_value']);
        $target = floatval($data['target_value']);

        if ($target != $baseline) {
            $progressPercent = (($current - $baseline) / ($target - $baseline)) * 100;
            $progressPercent = max(0, min(100, $progressPercent));

            echo "<tr>";
            echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Progress') . ": </span>";

            // Progress badge with color based on percentage
            $badgeClass = $progressPercent >= 75 ? 'success' : ($progressPercent >= 50 ? 'info' : ($progressPercent >= 25 ? 'warning' : 'danger'));

            echo "<span class='badge badge-" . $badgeClass . "'>" . number_format($progressPercent, 1) . "%</span>";

            // Show improvement amount
            $improvement = $current - $baseline;
            if ($improvement != 0) {
                echo " <small>(" . ($improvement > 0 ? "+" : "") . number_format($improvement, 1);
                if (!empty($data['unit'])) {
                    echo " " . text($data['unit']);
                }
                echo ")</small>";
            }
            echo "</td>";
            echo "</tr>";
        }
    }

    // Measurement date
    if (!empty($data['measurement_date'])) {
        echo "<tr>";
        echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Measured') . ": </span>";
        echo "<span class='text'>" . text($data['measurement_date']) . "</span></td>";
        echo "</tr>";
    }

    echo "</table>";
}

/* AI-GENERATED CODE - END */
