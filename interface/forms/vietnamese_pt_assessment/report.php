<?php

/**
 * Vietnamese PT Assessment Form - report.php
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
 * Display Vietnamese PT Assessment summary in encounter report
 *
 * @param int $pid Patient ID
 * @param int $encounter Encounter ID
 * @param int $cols Number of columns (legacy parameter)
 * @param int $id Form ID
 * @return void
 */
function vietnamese_pt_assessment_report($pid, $encounter, $cols, $id): void
{
    $data = formFetch("pt_assessments_bilingual", $id);

    if (!$data) {
        return;
    }

    echo "<table class='table table-sm table-bordered'>";
    echo "<tr>";

    // Chief Complaint - show Vietnamese or English based on preference
    $complaint = '';
    if (!empty($data['chief_complaint_vi'])) {
        $complaint = $data['chief_complaint_vi'];
    } elseif (!empty($data['chief_complaint_en'])) {
        $complaint = $data['chief_complaint_en'];
    }

    if ($complaint) {
        echo "<td><span class='font-weight-bold'>" . xlt('Chief Complaint') . ": </span>";
        echo "<span class='text'>" . nl2br(text(substr($complaint, 0, 100))) . (strlen($complaint) > 100 ? '...' : '') . "</span></td>";
    }

    // Pain Level with visual indicator
    if (isset($data['pain_level'])) {
        $painLevel = intval($data['pain_level']);
        $badgeClass = $painLevel <= 3 ? 'success' : ($painLevel <= 6 ? 'warning' : 'danger');

        echo "<td><span class='font-weight-bold'>" . xlt('Pain Level') . ": </span>";
        echo "<span class='badge badge-" . $badgeClass . "'>" . text($painLevel) . "/10</span></td>";
    }

    echo "</tr><tr>";

    // Pain Location
    $painLocation = $data['pain_location_vi'] ?? $data['pain_location_en'] ?? '';
    if ($painLocation) {
        echo "<td><span class='font-weight-bold'>" . xlt('Pain Location') . ": </span>";
        echo "<span class='text'>" . text($painLocation) . "</span></td>";
    }

    // Status
    if (!empty($data['status'])) {
        echo "<td><span class='font-weight-bold'>" . xlt('Status') . ": </span>";
        echo "<span class='text'>" . text(ucfirst($data['status'])) . "</span></td>";
    }

    echo "</tr><tr>";

    // Functional Goals - abbreviated
    $goals = $data['functional_goals_vi'] ?? $data['functional_goals_en'] ?? '';
    if ($goals) {
        echo "<td colspan='2'><span class='font-weight-bold'>" . xlt('Functional Goals') . ": </span>";
        echo "<span class='text'>" . nl2br(text(substr($goals, 0, 150))) . (strlen($goals) > 150 ? '...' : '') . "</span></td>";
    }

    echo "</tr>";
    echo "</table>";
}

/* AI-GENERATED CODE - END */
