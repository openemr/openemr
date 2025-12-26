<?php

/**
 * Vietnamese PT Treatment Plan - print.php
 *
 * Printer-friendly version of PT Treatment Plan form
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
$obj = formFetch("pt_treatment_plans", $_GET["id"]);

// Get patient data
$patient_data = getPatientData($GLOBALS['pid']);

// Get creator info
$creator = null;
if (!empty($obj['created_by'])) {
    $creator = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", array($obj['created_by']));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Treatment Plan - Print'); ?></title>
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
            color: #17a2b8;
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
        .plan-header {
            background-color: #e7f5ff;
            border: 2px solid #17a2b8;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .plan-header h2 {
            margin: 0 0 10px 0;
            color: #17a2b8;
        }
        .plan-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .plan-meta-item {
            flex: 1;
            padding: 10px;
            background-color: white;
            margin: 0 5px;
            border: 1px solid #17a2b8;
            border-radius: 3px;
            text-align: center;
        }
        .plan-meta-item strong {
            display: block;
            color: #17a2b8;
            font-size: 10pt;
        }
        .plan-meta-item span {
            font-size: 14pt;
            font-weight: bold;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            background-color: #17a2b8;
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
        .vietnamese-field {
            background-color: #fff3cd;
        }
        .english-field {
            background-color: #d1ecf1;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12pt;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-on_hold { background-color: #fff3cd; color: #856404; }
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
        <?php echo xlt('Vietnamese PT Treatment Plan'); ?><br>
        Kế Hoạch Điều Trị Vật Lý Trị Liệu
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
                <td><strong><?php echo xlt('Plan Created'); ?>:</strong> <?php echo text($obj['created_at'] ?? date('Y-m-d')); ?></td>
            </tr>
            <?php if ($creator): ?>
            <tr>
                <td colspan="2"><strong><?php echo xlt('Created By'); ?>:</strong> <?php echo text($creator['fname'] . ' ' . $creator['lname']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Plan Header -->
    <div class="plan-header">
        <h2><?php echo text($obj['plan_name'] ?? xlt('Treatment Plan')); ?></h2>

        <div class="plan-meta">
            <div class="plan-meta-item">
                <strong><?php echo xlt('Start Date'); ?></strong>
                <span><?php echo text($obj['start_date'] ?? 'N/A'); ?></span>
            </div>

            <?php if (!empty($obj['estimated_duration_weeks'])): ?>
            <div class="plan-meta-item">
                <strong><?php echo xlt('Duration'); ?></strong>
                <span><?php echo text($obj['estimated_duration_weeks']); ?> <?php echo xlt('weeks'); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($obj['end_date'])): ?>
            <div class="plan-meta-item">
                <strong><?php echo xlt('End Date'); ?></strong>
                <span><?php echo text($obj['end_date']); ?></span>
            </div>
            <?php endif; ?>

            <div class="plan-meta-item">
                <strong><?php echo xlt('Status'); ?></strong>
                <?php
                $status = $obj['status'] ?? 'active';
                $statusClass = 'status-' . str_replace(' ', '_', $status);
                $statusDisplay = [
                    'active' => xlt('Active'),
                    'completed' => xlt('Completed'),
                    'on_hold' => xlt('On Hold')
                ];
                ?>
                <span class="status-badge <?php echo $statusClass; ?>">
                    <?php echo text($statusDisplay[$status] ?? ucfirst($status)); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Diagnosis -->
    <?php if (!empty($obj['diagnosis_vi']) || !empty($obj['diagnosis_en'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Diagnosis'); ?> / Chẩn Đoán</div>
        <?php if (!empty($obj['diagnosis_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo text($obj['diagnosis_vi']); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['diagnosis_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo text($obj['diagnosis_en']); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Goals -->
    <?php if (!empty($obj['goals_vi']) || !empty($obj['goals_en'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Treatment Goals'); ?> / Mục Tiêu Điều Trị</div>
        <?php if (!empty($obj['goals_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['goals_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['goals_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['goals_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Interventions -->
    <?php if (!empty($obj['interventions_vi']) || !empty($obj['interventions_en'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Interventions'); ?> / Can Thiệp</div>
        <?php if (!empty($obj['interventions_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['interventions_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['interventions_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['interventions_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Progress Notes -->
    <?php if (!empty($obj['progress_notes'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Progress Notes'); ?> / Ghi Chú Tiến Triển</div>
        <div class="field-value"><?php echo nl2br(text($obj['progress_notes'])); ?></div>
    </div>
    <?php endif; ?>

    <!-- Expected Outcomes -->
    <?php if (!empty($obj['expected_outcomes_vi']) || !empty($obj['expected_outcomes_en'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Expected Outcomes'); ?> / Kết Quả Mong Đợi</div>
        <?php if (!empty($obj['expected_outcomes_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['expected_outcomes_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['expected_outcomes_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['expected_outcomes_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Signature Section -->
    <div class="signature-section">
        <div><?php echo xlt('Physical Therapist'); ?>:</div>
        <div class="signature-line">
            <?php if ($creator): ?>
                <?php echo text($creator['fname'] . ' ' . $creator['lname']); ?>
            <?php endif; ?>
        </div>
        <div style="margin-top: 20px;"><?php echo xlt('Date'); ?>: <?php echo text(date('Y-m-d')); ?></div>

        <div style="margin-top: 40px;"><?php echo xlt('Patient Signature'); ?>:</div>
        <div class="signature-line"></div>
        <div style="margin-top: 20px;"><?php echo xlt('Date'); ?>: _________________</div>
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
