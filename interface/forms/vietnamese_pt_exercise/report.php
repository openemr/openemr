<?php

/**
 * Vietnamese PT Exercise Prescription - report.php
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
 * Display Vietnamese PT Exercise Prescription summary in encounter report
 *
 * @param int $pid Patient ID
 * @param int $encounter Encounter ID
 * @param int $cols Number of columns (legacy parameter)
 * @param int $id Form ID
 * @return void
 */
function vietnamese_pt_exercise_report($pid, $encounter, $cols, $id): void
{
    $data = formFetch("pt_exercise_prescriptions", $id);

    if (!$data) {
        return;
    }

    echo "<table class='table table-sm table-bordered'>";
    echo "<tr>";

    // Exercise Name
    $exerciseName = $data['exercise_name_vi'] ?? $data['exercise_name'] ?? '';
    if ($exerciseName) {
        echo "<td><span class='font-weight-bold'>" . xlt('Exercise') . ": </span>";
        echo "<span class='text'>" . text($exerciseName) . "</span></td>";
    }

    // Prescription summary: Sets x Reps
    $prescription = '';
    if (!empty($data['sets_prescribed'])) {
        $prescription .= text($data['sets_prescribed']) . ' ' . xlt('sets');
    }
    if (!empty($data['reps_prescribed'])) {
        $prescription .= ' × ' . text($data['reps_prescribed']) . ' ' . xlt('reps');
    } elseif (!empty($data['duration_minutes'])) {
        $prescription .= ' × ' . text($data['duration_minutes']) . ' ' . xlt('min');
    }

    if ($prescription) {
        echo "<td><span class='font-weight-bold'>" . xlt('Prescription') . ": </span>";
        echo "<span class='text'>" . $prescription . "</span></td>";
    }

    echo "</tr><tr>";

    // Frequency
    if (!empty($data['frequency_per_week'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Frequency') . ": </span>";
        echo "<span class='text'>" . text($data['frequency_per_week']) . "x/" . xlt('week') . "</span></td>";
    }

    // Intensity with badge
    if (!empty($data['intensity_level'])) {
        $intensity = $data['intensity_level'];
        $badgeClass = $intensity == 'low' ? 'success' : ($intensity == 'moderate' ? 'warning' : 'danger');

        echo "<td><span class='font-weight-bold'>" . xlt('Intensity') . ": </span>";
        echo "<span class='badge badge-" . $badgeClass . "'>" . text(ucfirst($intensity)) . "</span></td>";
    }

    echo "</tr>";

    // Status/Dates
    if (!empty($data['start_date']) || !empty($data['end_date'])) {
        echo "<tr>";
        if (!empty($data['start_date'])) {
            echo "<td><span class='font-weight-bold'>" . xlt('Start') . ": </span>";
            echo "<span class='text'>" . text($data['start_date']) . "</span></td>";
        }
        if (!empty($data['end_date'])) {
            echo "<td><span class='font-weight-bold'>" . xlt('End') . ": </span>";
            echo "<span class='text'>" . text($data['end_date']) . "</span></td>";
        }
        echo "</tr>";
    }

    // Active status
    if (isset($data['is_active'])) {
        echo "<tr>";
        echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Status') . ": </span>";
        $statusBadge = $data['is_active'] == 1 ? 'success' : 'secondary';
        $statusText = $data['is_active'] == 1 ? xlt('Active') : xlt('Inactive');
        echo "<span class='badge badge-" . $statusBadge . "'>" . $statusText . "</span></td>";
        echo "</tr>";
    }

    echo "</table>";
}

/* AI-GENERATED CODE - END */
