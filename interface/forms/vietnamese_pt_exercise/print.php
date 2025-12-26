<?php

/**
 * Vietnamese PT Exercise Prescription - print.php
 *
 * Printer-friendly version of PT Exercise Prescription form
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
$obj = formFetch("pt_exercise_prescriptions", $_GET["id"]);

// Get patient data
$patient_data = getPatientData($GLOBALS['pid']);

// Get prescriber info
$prescriber = null;
if (!empty($obj['prescribed_by'])) {
    $prescriber = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", array($obj['prescribed_by']));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Exercise Prescription - Print'); ?></title>
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
            color: #28a745;
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
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            background-color: #28a745;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
        }
        .prescription-box {
            border: 2px solid #28a745;
            padding: 20px;
            background-color: #f0f8f4;
            margin-bottom: 20px;
        }
        .prescription-detail {
            display: inline-block;
            margin: 10px 20px 10px 0;
            padding: 10px 15px;
            background-color: white;
            border: 1px solid #28a745;
            border-radius: 5px;
        }
        .prescription-detail strong {
            color: #28a745;
            display: block;
            font-size: 10pt;
        }
        .prescription-detail span {
            font-size: 16pt;
            font-weight: bold;
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
        .intensity-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
        }
        .intensity-low { background-color: #d4edda; color: #155724; }
        .intensity-moderate { background-color: #fff3cd; color: #856404; }
        .intensity-high { background-color: #f8d7da; color: #721c24; }
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
        <?php echo xlt('Vietnamese PT Exercise Prescription'); ?><br>
        Đơn Kê Bài Tập Vật Lý Trị Liệu
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
                <td><strong><?php echo xlt('Prescription Date'); ?>:</strong> <?php echo text($obj['prescribed_date'] ?? date('Y-m-d')); ?></td>
            </tr>
            <?php if ($prescriber): ?>
            <tr>
                <td colspan="2"><strong><?php echo xlt('Prescribed By'); ?>:</strong> <?php echo text($prescriber['fname'] . ' ' . $prescriber['lname']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Exercise Name -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Exercise Name'); ?> / Tên Bài Tập</div>
        <?php if (!empty($obj['exercise_name_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field" style="font-size: 14pt; font-weight: bold;">
                <?php echo text($obj['exercise_name_vi']); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['exercise_name'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field" style="font-size: 14pt; font-weight: bold;">
                <?php echo text($obj['exercise_name']); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Prescription Details -->
    <div class="prescription-box">
        <h3 style="margin-top: 0; color: #28a745;"><?php echo xlt('Prescription Details'); ?> / Chi Tiết Kê Đơn</h3>

        <div class="prescription-detail">
            <strong><?php echo xlt('Sets'); ?> / Số Hiệp</strong>
            <span><?php echo text($obj['sets_prescribed'] ?? 'N/A'); ?></span>
        </div>

        <?php if (!empty($obj['reps_prescribed'])): ?>
        <div class="prescription-detail">
            <strong><?php echo xlt('Reps'); ?> / Số Lần</strong>
            <span><?php echo text($obj['reps_prescribed']); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($obj['duration_minutes'])): ?>
        <div class="prescription-detail">
            <strong><?php echo xlt('Duration'); ?> / Thời Gian</strong>
            <span><?php echo text($obj['duration_minutes']); ?> <?php echo xlt('min'); ?></span>
        </div>
        <?php endif; ?>

        <div class="prescription-detail">
            <strong><?php echo xlt('Frequency'); ?> / Tần Suất</strong>
            <span><?php echo text($obj['frequency_per_week'] ?? 'N/A'); ?>x/<?php echo xlt('week'); ?></span>
        </div>

        <?php if (!empty($obj['intensity_level'])): ?>
        <div class="prescription-detail">
            <strong><?php echo xlt('Intensity'); ?> / Mức Độ</strong>
            <?php
            $intensity = $obj['intensity_level'];
            $intensityClass = $intensity == 'low' ? 'intensity-low' : ($intensity == 'moderate' ? 'intensity-moderate' : 'intensity-high');
            $intensityText = [
                'low' => xlt('Low') . ' / Nhẹ',
                'moderate' => xlt('Moderate') . ' / Trung Bình',
                'high' => xlt('High') . ' / Cao'
            ];
            ?>
            <span class="intensity-badge <?php echo $intensityClass; ?>">
                <?php echo text($intensityText[$intensity] ?? ucfirst($intensity)); ?>
            </span>
        </div>
        <?php endif; ?>

        <div style="clear: both; margin-top: 15px;">
            <?php if (!empty($obj['start_date'])): ?>
            <strong><?php echo xlt('Start Date'); ?>:</strong> <?php echo text($obj['start_date']); ?>
            <?php endif; ?>
            <?php if (!empty($obj['end_date'])): ?>
            &nbsp;&nbsp;&nbsp;<strong><?php echo xlt('End Date'); ?>:</strong> <?php echo text($obj['end_date']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Description'); ?> / Mô Tả</div>
        <?php if (!empty($obj['description_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['description_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['description'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['description'])); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Instructions -->
    <?php if (!empty($obj['instructions_vi']) || !empty($obj['instructions'])): ?>
    <div class="section">
        <div class="section-title"><?php echo xlt('Instructions'); ?> / Hướng Dẫn</div>
        <?php if (!empty($obj['instructions_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['instructions_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['instructions'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['instructions'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Equipment -->
    <?php if (!empty($obj['equipment_needed'])): ?>
    <div class="section">
        <div class="field-label"><?php echo xlt('Equipment Needed'); ?> / Thiết Bị Cần Thiết:</div>
        <div class="field-value"><?php echo text($obj['equipment_needed']); ?></div>
    </div>
    <?php endif; ?>

    <!-- Precautions -->
    <?php if (!empty($obj['precautions_vi']) || !empty($obj['precautions'])): ?>
    <div class="section">
        <div class="section-title" style="background-color: #dc3545;"><?php echo xlt('Precautions'); ?> / Lưu Ý An Toàn</div>
        <?php if (!empty($obj['precautions_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['precautions_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['precautions'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['precautions'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Signature Section -->
    <div class="signature-section">
        <div><?php echo xlt('Prescribed By'); ?>:</div>
        <div class="signature-line">
            <?php if ($prescriber): ?>
                <?php echo text($prescriber['fname'] . ' ' . $prescriber['lname']); ?>
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
