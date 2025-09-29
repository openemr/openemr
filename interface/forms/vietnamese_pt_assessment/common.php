<?php

/**
 * Vietnamese PT Assessment Form - common.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("pt_assessments_bilingual", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Assessment'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        .bilingual-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .bilingual-group h4 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .vietnamese-field {
            background-color: #fff3cd;
        }
        .english-field {
            background-color: #d1ecf1;
        }
        .pain-level-indicator {
            font-size: 2em;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .pain-low { background-color: #d4edda; color: #155724; }
        .pain-moderate { background-color: #fff3cd; color: #856404; }
        .pain-high { background-color: #f8d7da; color: #721c24; }
    </style>
    <script>
        function updatePainIndicator() {
            const painLevel = document.getElementById('pain_level').value;
            const indicator = document.getElementById('pain_indicator');

            if (painLevel >= 0 && painLevel <= 10) {
                indicator.textContent = painLevel;

                if (painLevel <= 3) {
                    indicator.className = 'pain-level-indicator pain-low';
                } else if (painLevel <= 6) {
                    indicator.className = 'pain-level-indicator pain-moderate';
                } else {
                    indicator.className = 'pain-level-indicator pain-high';
                }
            }
        }

        function translateField(fromField, toField) {
            // Placeholder for future translation API integration
            alert(<?php echo js_escape(xl('Translation feature requires REST API configuration')); ?>);
        }
    </script>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Assessment'); ?> - <?php echo xlt($viewmode == 'update' ? 'Update' : 'New'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_assessment/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>" name="assessment_form">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="therapist_id" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <!-- Language Preference -->
            <div class="form-group row">
                <label class="col-sm-2 col-form-label"><?php echo xlt('Language Preference'); ?>:</label>
                <div class="col-sm-10">
                    <select name="language_preference" class="form-control">
                        <option value="vi" <?php echo ($obj && $obj['language_preference'] == 'vi') ? 'selected' : ''; ?>><?php echo xlt('Vietnamese'); ?></option>
                        <option value="en" <?php echo ($obj && $obj['language_preference'] == 'en') ? 'selected' : ''; ?>><?php echo xlt('English'); ?></option>
                        <option value="both" <?php echo ($obj && $obj['language_preference'] == 'both') ? 'selected' : 'selected'; ?>><?php echo xlt('Both'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Chief Complaint -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Chief Complaint'); ?> / Triệu chứng chính</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="chief_complaint_vi" class="form-control vietnamese-field" rows="3" placeholder="Ví dụ: Đau lưng mãn tính từ 6 tháng"><?php echo text($obj['chief_complaint_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="chief_complaint_en" class="form-control english-field" rows="3" placeholder="Example: Chronic back pain for 6 months"><?php echo text($obj['chief_complaint_en'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Pain Assessment -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Pain Assessment'); ?> / Đánh giá đau</h4>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"><?php echo xlt('Pain Level (0-10)'); ?>:</label>
                    <div class="col-sm-3">
                        <input type="number" id="pain_level" name="pain_level" class="form-control" min="0" max="10"
                               value="<?php echo attr($obj['pain_level'] ?? ''); ?>"
                               onchange="updatePainIndicator()">
                    </div>
                    <div class="col-sm-6">
                        <div id="pain_indicator" class="pain-level-indicator"><?php echo text($obj['pain_level'] ?? '0'); ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Pain Location (Vietnamese)'); ?>:</label>
                    <input type="text" name="pain_location_vi" class="form-control vietnamese-field"
                           placeholder="Ví dụ: Lưng dưới, bên phải"
                           value="<?php echo attr($obj['pain_location_vi'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Pain Location (English)'); ?>:</label>
                    <input type="text" name="pain_location_en" class="form-control english-field"
                           placeholder="Example: Lower back, right side"
                           value="<?php echo attr($obj['pain_location_en'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Pain Description (Vietnamese)'); ?>:</label>
                    <textarea name="pain_description_vi" class="form-control vietnamese-field" rows="2"
                              placeholder="Ví dụ: Đau nhói, tăng khi ngồi lâu"><?php echo text($obj['pain_description_vi'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Pain Description (English)'); ?>:</label>
                    <textarea name="pain_description_en" class="form-control english-field" rows="2"
                              placeholder="Example: Sharp pain, increases with prolonged sitting"><?php echo text($obj['pain_description_en'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Functional Goals -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Functional Goals'); ?> / Mục tiêu chức năng</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="functional_goals_vi" class="form-control vietnamese-field" rows="3"
                              placeholder="Ví dụ: Có thể đi bộ 30 phút không đau"><?php echo text($obj['functional_goals_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="functional_goals_en" class="form-control english-field" rows="3"
                              placeholder="Example: Able to walk 30 minutes without pain"><?php echo text($obj['functional_goals_en'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Treatment Plan -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Treatment Plan'); ?> / Kế hoạch điều trị</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="treatment_plan_vi" class="form-control vietnamese-field" rows="4"
                              placeholder="Ví dụ: Liệu pháp vật lý 3 lần/tuần, bài tập kéo giãn hàng ngày"><?php echo text($obj['treatment_plan_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="treatment_plan_en" class="form-control english-field" rows="4"
                              placeholder="Example: Physical therapy 3x/week, daily stretching exercises"><?php echo text($obj['treatment_plan_en'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Status -->
            <div class="form-group row">
                <label class="col-sm-2 col-form-label"><?php echo xlt('Status'); ?>:</label>
                <div class="col-sm-10">
                    <select name="status" class="form-control">
                        <option value="draft" <?php echo ($obj && $obj['status'] == 'draft') ? 'selected' : ''; ?>><?php echo xlt('Draft'); ?></option>
                        <option value="completed" <?php echo ($obj && $obj['status'] == 'completed') ? 'selected' : 'selected'; ?>><?php echo xlt('Completed'); ?></option>
                        <option value="reviewed" <?php echo ($obj && $obj['status'] == 'reviewed') ? 'selected' : ''; ?>><?php echo xlt('Reviewed'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-save">
                    <?php echo xlt('Save Assessment'); ?>
                </button>
                <button type="button" class="btn btn-secondary btn-cancel"
                        onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'">
                    <?php echo xlt('Cancel'); ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Initialize pain indicator on page load
        <?php if ($obj && isset($obj['pain_level'])) { ?>
        updatePainIndicator();
        <?php } ?>
    </script>
</body>
</html>