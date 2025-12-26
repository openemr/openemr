<?php

/**
 * Vietnamese PT Assessment Form - print.php
 *
 * Printer-friendly version of PT Assessment form
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
$obj = formFetch("pt_assessments_bilingual", $_GET["id"]);

// Get patient data
$patient_data = getPatientData($GLOBALS['pid']);

// Get therapist info
$therapist = null;
if (!empty($obj['therapist_id'])) {
    $therapist = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", array($obj['therapist_id']));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Assessment - Print'); ?></title>
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
            background-color: #007bff;
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
        .pain-indicator {
            display: inline-block;
            padding: 10px 20px;
            font-size: 20pt;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 5px;
        }
        .pain-low { background-color: #d4edda; color: #155724; }
        .pain-moderate { background-color: #fff3cd; color: #856404; }
        .pain-high { background-color: #f8d7da; color: #721c24; }
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
        .button-group {
            margin: 20px 0;
            text-align: center;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <!-- Print/Back Buttons -->
    <div class="button-group no-print">
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
        <?php echo xlt('Vietnamese PT Assessment'); ?> / Đánh Giá Vật Lý Trị Liệu
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
                <td><strong><?php echo xlt('Assessment Date'); ?>:</strong> <?php echo text($obj['assessment_date'] ?? date('Y-m-d')); ?></td>
            </tr>
            <?php if ($therapist): ?>
            <tr>
                <td colspan="2"><strong><?php echo xlt('Therapist'); ?>:</strong> <?php echo text($therapist['fname'] . ' ' . $therapist['lname']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Chief Complaint -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Chief Complaint'); ?> / Triệu Chứng Chính</div>
        <?php if (!empty($obj['chief_complaint_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['chief_complaint_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['chief_complaint_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['chief_complaint_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pain Assessment -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Pain Assessment'); ?> / Đánh Giá Đau</div>

        <?php if (isset($obj['pain_level'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Pain Level (0-10)'); ?>:</div>
            <?php
            $painLevel = intval($obj['pain_level']);
            $painClass = $painLevel <= 3 ? 'pain-low' : ($painLevel <= 6 ? 'pain-moderate' : 'pain-high');
            ?>
            <div class="pain-indicator <?php echo $painClass; ?>">
                <?php echo text($painLevel); ?>/10
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($obj['pain_location_vi']) || !empty($obj['pain_location_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Pain Location'); ?> / Vị Trí Đau:</div>
            <?php if (!empty($obj['pain_location_vi'])): ?>
            <div class="field-value vietnamese-field"><?php echo text($obj['pain_location_vi']); ?></div>
            <?php endif; ?>
            <?php if (!empty($obj['pain_location_en'])): ?>
            <div class="field-value english-field"><?php echo text($obj['pain_location_en']); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($obj['pain_description_vi']) || !empty($obj['pain_description_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Pain Description'); ?> / Mô Tả Đau:</div>
            <?php if (!empty($obj['pain_description_vi'])): ?>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['pain_description_vi'])); ?></div>
            <?php endif; ?>
            <?php if (!empty($obj['pain_description_en'])): ?>
            <div class="field-value english-field"><?php echo nl2br(text($obj['pain_description_en'])); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Functional Goals -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Functional Goals'); ?> / Mục Tiêu Chức Năng</div>
        <?php if (!empty($obj['functional_goals_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['functional_goals_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['functional_goals_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['functional_goals_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Treatment Plan -->
    <div class="section">
        <div class="section-title"><?php echo xlt('Treatment Plan'); ?> / Kế Hoạch Điều Trị</div>
        <?php if (!empty($obj['treatment_plan_vi'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('Vietnamese'); ?>:</div>
            <div class="field-value vietnamese-field"><?php echo nl2br(text($obj['treatment_plan_vi'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($obj['treatment_plan_en'])): ?>
        <div class="field-group">
            <div class="field-label"><?php echo xlt('English'); ?>:</div>
            <div class="field-value english-field"><?php echo nl2br(text($obj['treatment_plan_en'])); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Status -->
    <div class="section">
        <div class="field-label"><?php echo xlt('Status'); ?>:</div>
        <div class="field-value">
            <?php echo text(ucfirst($obj['status'] ?? 'completed')); ?>
        </div>
    </div>

    <!-- Language Preference -->
    <div class="section">
        <div class="field-label"><?php echo xlt('Language Preference'); ?>:</div>
        <div class="field-value">
            <?php
            $langPref = $obj['language_preference'] ?? 'both';
            $langDisplay = [
                'vi' => xlt('Vietnamese'),
                'en' => xlt('English'),
                'both' => xlt('Both')
            ];
            echo text($langDisplay[$langPref] ?? $langPref);
            ?>
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
    <div class="button-group no-print" style="margin-top: 30px;">
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fa fa-print"></i> <?php echo xlt('Print'); ?>
        </button>
        <button onclick="window.close();" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> <?php echo xlt('Back'); ?>
        </button>
    </div>

    <script>
        // Auto-print functionality (optional)
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
