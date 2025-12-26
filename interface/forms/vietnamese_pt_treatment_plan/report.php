<?php

/**
 * Vietnamese PT Treatment Plan - report.php
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
 * Display Vietnamese PT Treatment Plan summary in encounter report
 *
 * @param int $pid Patient ID
 * @param int $encounter Encounter ID
 * @param int $cols Number of columns (legacy parameter)
 * @param int $id Form ID
 * @return void
 */
function vietnamese_pt_treatment_plan_report($pid, $encounter, $cols, $id): void
{
    $data = formFetch("pt_treatment_plans", $id);

    if (!$data) {
        return;
    }

    echo "<table class='table table-sm table-bordered'>";
    echo "<tr>";

    // Plan Name
    if (!empty($data['plan_name'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Plan Name') . ": </span>";
        echo "<span class='text'>" . text($data['plan_name']) . "</span></td>";
    }

    // Status with badge
    if (!empty($data['status'])) {
        $status = $data['status'];
        $badgeClass = $status == 'active' ? 'success' : ($status == 'completed' ? 'info' : 'warning');
        $statusDisplay = [
            'active' => xlt('Active'),
            'completed' => xlt('Completed'),
            'on_hold' => xlt('On Hold')
        ];

        echo "<td><span class='font-weight-bold'>" . xlt('Status') . ": </span>";
        echo "<span class='badge badge-" . $badgeClass . "'>" . text($statusDisplay[$status] ?? ucfirst($status)) . "</span></td>";
    }

    echo "</tr><tr>";

    // Diagnosis (Vietnamese or English)
    $diagnosis = $data['diagnosis_vi'] ?? $data['diagnosis_en'] ?? '';
    if ($diagnosis) {
        echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Diagnosis') . ": </span>";
        echo "<span class='text'>" . text($diagnosis) . "</span></td>";
    }

    echo "</tr><tr>";

    // Start Date and Duration
    if (!empty($data['start_date'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Start Date') . ": </span>";
        echo "<span class='text'>" . text($data['start_date']) . "</span></td>";
    }

    if (!empty($data['estimated_duration_weeks'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Duration') . ": </span>";
        echo "<span class='text'>" . text($data['estimated_duration_weeks']) . " " . xlt('weeks') . "</span></td>";
    }

    echo "</tr>";

    // Goals - abbreviated
    $goals = $data['goals_vi'] ?? $data['goals_en'] ?? '';
    if ($goals) {
        echo "<tr>";
        echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Goals') . ": </span>";
        echo "<span class='text'>" . nl2br(text(substr($goals, 0, 150))) . (strlen($goals) > 150 ? '...' : '') . "</span></td>";
        echo "</tr>";
    }

    echo "</table>";
}

/* AI-GENERATED CODE - END */
