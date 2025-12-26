<?php

/**
 * Vietnamese PT Outcome Measures - print.php
 *
 * Printer-friendly version of PT Outcome Measures form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-GENERATED CODE - START
 * Generated with Claude Code (Anthropic) on 2025-11-22
 * This print template follows OpenEMR form print patterns
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Core\Header;

// Get form data
$obj = formFetch("pt_outcome_measures", $_GET["id"]);

// Get patient data
$patient_data = getPatientData($GLOBALS['pid']);

// Get therapist info
$therapist = null;
if (!empty($obj['therapist_id'])) {
    $therapist = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", array($obj['therapist_id']));
}

// Calculate progress if possible
$progress = null;
$progressPercent = null;
if (!empty($obj['baseline_value']) && !empty($obj['current_value']) && !empty($obj['target_value'])) {
    $baseline = floatval($obj['baseline_value']);
    $current = floatval($obj['current_value']);
    $target = floatval($obj['target_value']);

    if ($target != $baseline) {
        $progressPercent = (($current - $baseline) / ($target - $baseline)) * 100;
        $progressPercent = max(0, min(100, $progressPercent)); // Clamp between 0-100
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Outcome Measures - Print'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12pt;
            }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header-section {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .clinic-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #6c757d;
        }
        .patient-info {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .patient-info table {
            width: 100%;
        }
        .patient-info td {
            padding: 5px;
        }
        .outcome-header {
            background-color: #e9ecef;
            border: 2px solid #6c757d;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .outcome-header h2 {
            margin: 0 0 10px 0;
            color: #6c757d;
        }
        .measure-type {
            font-size: 18pt;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }
        .values-display {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .value-box {
            flex: 1;
            padding: 15px;
            margin: 0 10px;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            text-align: center;
            background-color: white;
        }
        .value-box.baseline {
            border-color: #ffc107;
            background-color: #fff3cd;
        }
        .value-box.current {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .value-box.target {
            border-color: #17a2b8;
            background-color: #d1ecf1;
        }
        .value-box strong {
            display: block;
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }
        .value-box .value {
            font-size: 24pt;
            font-weight: bold;
        }
        .value-box .unit {
            font-size: 12pt;
            color: #666;
        }
        .progress-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .progress-bar-container {
            width: 100%;
            height: 30px;
            background-color: #e9ecef;
            border: 1px solid #adb5bd;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #28a745;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            background-color: #6c757d;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
        }
        .field-group {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .field-value {
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #fafafa;
            min-height: 30px;
        }
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 300px;
            margin-top: 50px;
            padding-top: 5px;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <!-- Print/Back Buttons -->
    <div class="button-group no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fa fa-print"></i> <?php echo xlt('Print'); ?>
        </button>
        <button onclick="window.close();" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> <?php echo xlt('Back'); ?>
        </button>
    </div>

    <!-- Header -->
    <div class="header-section">
        <div class="clinic-name"><?php echo text($GLOBALS['openemr_name'] ?? 'OpenEMR'); ?></div>
        <div><?php echo text($GLOBALS['practice_return_email_path'] ?? ''); ?></div>
    </div>

    <!-- Form Title -->
    <div class="form-title">
        <?php echo xlt('Vietnamese PT Outcome Measures'); ?><br>
        Đo Lường Kết Quả Vật Lý Trị Liệu
    </div>

    <!-- Patient Information -->
    <div class="patient-info">
        <table>
            <tr>
                <td><strong><?php echo xlt('Patient Name'); ?>:</strong> <?php echo text($patient_data['fname'] . ' ' . $patient_data['lname']); ?></td>
                <td><strong><?php echo xlt('DOB'); ?>:</strong> <?php echo text($patient_data['DOB']); ?></td>
            </tr>
            <tr>
                <td><strong><?php echo xlt('Patient ID'); ?>:</strong> <?php echo text($patient_data['pid']); ?></td>
                <td><strong><?php echo xlt('Measurement Date'); ?>:</strong> <?php echo text($obj['measurement_date'] ?? date('Y-m-d')); ?></td>
            </tr>
            <?php if ($therapist): ?>
            <tr>
                <td colspan="2"><strong><?php echo xlt('Therapist'); ?>:</strong> <?php echo text($therapist['fname'] . ' ' . $therapist['lname']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Outcome Header -->
    <div class="outcome-header">
        <div class="measure-type">
            <?php
            $measureTypes = [
                'ROM' => xlt('Range of Motion (ROM)'),
                'Strength' => xlt('Strength'),
                'Pain' => xlt('Pain Level'),
                'Function' => xlt('Functional Status'),
                'Balance' => xlt('Balance')
            ];
            $measureType = $obj['measure_type'] ?? '';
            echo text($measureTypes[$measureType] ?? $measureType);
            ?>
        </div>

        <!-- Values Display -->
        <div class="values-display">
            <?php if (!empty($obj['baseline_value'])): ?>
            <div class="value-box baseline">
                <strong><?php echo xlt('Baseline'); ?> / Khởi Điểm</strong>
                <div class="value"><?php echo text($obj['baseline_value']); ?></div>
                <?php if (!empty($obj['unit'])): ?>
                <div class="unit"><?php echo text($obj['unit']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="value-box current">
                <strong><?php echo xlt('Current'); ?> / Hiện Tại</strong>
                <div class="value"><?php echo text($obj['current_value'] ?? 'N/A'); ?></div>
                <?php if (!empty($obj['unit'])): ?>
                <div class="unit"><?php echo text($obj['unit']); ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($obj['target_value'])): ?>
            <div class="value-box target">
                <strong><?php echo xlt('Target'); ?> / Mục Tiêu</strong>
                <div class="value"><?php echo text($obj['target_value']); ?></div>
                <?php if (!empty($obj['unit'])): ?>
                <div class="unit"><?php echo text($obj['unit']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Section -->
    <?php if ($progressPercent !== null): ?>
    <div class="progress-section">
        <h3 style="margin-top: 0;"><?php echo xlt('Progress Toward Goal'); ?> / Tiến Triển Đạt Mục Tiêu</h3>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: <?php echo number_format($progressPercent, 1); ?>%;">
                <?php echo number_format($progressPercent, 1); ?>%
            </div>
        </div>
        <div style="text-align: center; margin-top: 10px; font-size: 12pt;">
            <?php
            $improvement = floatval($obj['current_value']) - floatval($obj['baseline_value']);
            $improvementText = $improvement >= 0 ? xlt('Improvement') : xlt('Decline');
            ?>
            <strong><?php echo $improvementText; ?>:</strong>
            <?php echo text(abs($improvement)); ?> <?php echo text($obj['unit'] ?? ''); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if (!empty($obj['notes'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Notes'); ?> / Ghi Chú</div>
        <div class="field-value"><?php echo nl2br(text($obj['notes'])); ?></div>
    </div>
    <?php endif; ?>

    <!-- Measurement Details -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Measurement Details'); ?> / Chi Tiết Đo Lường</div>

        <div class="field-group">
            <div class="field-label"><?php echo xlt('Measure Type'); ?> / Loại Đo Lường:</div>
            <div class="field-value"><?php echo text($measureTypes[$measureType] ?? $measureType); ?></div>
        </div>

        <div class="field-group">
            <div class="field-label"><?php echo xlt('Unit of Measurement'); ?> / Đơn Vị Đo:</div>
            <div class="field-value"><?php echo text($obj['unit'] ?? 'N/A'); ?></div>
        </div>

        <div class="field-group">
            <div class="field-label"><?php echo xlt('Measurement Date'); ?> / Ngày Đo:</div>
            <div class="field-value"><?php echo text($obj['measurement_date'] ?? date('Y-m-d')); ?></div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div><?php echo xlt('Therapist Signature'); ?>:</div>
        <div class="signature-line">
            <?php if ($therapist): ?>
                <?php echo text($therapist['fname'] . ' ' . $therapist['lname']); ?>
            <?php endif; ?>
        </div>
        <div style="margin-top: 20px;"><?php echo xlt('Date'); ?>: <?php echo text(date('Y-m-d')); ?></div>
    </div>

    <!-- Print/Back Buttons (bottom) -->
    <div class="button-group no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fa fa-print"></i> <?php echo xlt('Print'); ?>
        </button>
        <button onclick="window.close();" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> <?php echo xlt('Back'); ?>
        </button>
    </div>

    <script>
        $(function () {
            var win = top.printLogPrint ? top : opener.top;
            if (win && typeof win.printLogPrint === 'function') {
                win.printLogPrint(window);
            }
        });
    </script>
</body>
</html>
<?php
/* AI-GENERATED CODE - END */
