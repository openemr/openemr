<?php

/**
 * Vietnamese PT Exercise Prescription - common.php
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
    $obj = formFetch("pt_exercise_prescriptions", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Exercise Prescription'); ?></title>
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
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .vietnamese-field {
            background-color: #fff3cd;
        }
        .english-field {
            background-color: #d1ecf1;
        }
        .prescription-summary {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Exercise Prescription'); ?> - <?php echo xlt($viewmode == 'update' ? 'Update' : 'New'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_exercise/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>" name="exercise_form">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="prescribed_by" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <!-- Exercise Name -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Exercise Name'); ?> / Tên bài tập</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <input type="text" name="exercise_name_vi" class="form-control vietnamese-field" required
                           placeholder="Ví dụ: Động tác mèo-bò (Cat-Cow)"
                           value="<?php echo attr($obj['exercise_name_vi'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <input type="text" name="exercise_name" class="form-control english-field" required
                           placeholder="Example: Cat-Cow Stretch"
                           value="<?php echo attr($obj['exercise_name'] ?? ''); ?>">
                </div>
            </div>

            <!-- Description -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Description'); ?> / Mô tả</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="description_vi" class="form-control vietnamese-field" rows="3"
                              placeholder="Mô tả cách thực hiện bài tập bằng tiếng Việt"><?php echo text($obj['description_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="description" class="form-control english-field" rows="3"
                              placeholder="Describe how to perform the exercise in English"><?php echo text($obj['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Prescription Details -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Prescription Details'); ?> / Chi tiết kê đơn</h4>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo xlt('Sets'); ?> / Số hiệp:</label>
                            <input type="number" name="sets_prescribed" class="form-control" min="1" max="10" required
                                   value="<?php echo attr($obj['sets_prescribed'] ?? '3'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo xlt('Reps'); ?> / Số lần:</label>
                            <input type="number" name="reps_prescribed" class="form-control" min="1" max="50"
                                   value="<?php echo attr($obj['reps_prescribed'] ?? '10'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo xlt('Duration (minutes)'); ?> / Thời gian (phút):</label>
                            <input type="number" name="duration_minutes" class="form-control" min="1" max="60"
                                   value="<?php echo attr($obj['duration_minutes'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo xlt('Frequency Per Week'); ?> / Tần suất mỗi tuần:</label>
                            <input type="number" name="frequency_per_week" class="form-control" min="1" max="7" required
                                   value="<?php echo attr($obj['frequency_per_week'] ?? '5'); ?>">
                            <small class="form-text text-muted"><?php echo xlt('Days per week (1-7)'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo xlt('Intensity Level'); ?> / Mức độ:</label>
                            <select name="intensity_level" class="form-control">
                                <option value="low" <?php echo ($obj && $obj['intensity_level'] == 'low') ? 'selected' : ''; ?>><?php echo xlt('Low'); ?> / Nhẹ</option>
                                <option value="moderate" <?php echo ($obj && $obj['intensity_level'] == 'moderate') ? 'selected' : 'selected'; ?>><?php echo xlt('Moderate'); ?> / Trung bình</option>
                                <option value="high" <?php echo ($obj && $obj['intensity_level'] == 'high') ? 'selected' : ''; ?>><?php echo xlt('High'); ?> / Cao</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo xlt('Start Date'); ?>:</label>
                            <input type="date" name="start_date" class="form-control"
                                   value="<?php echo attr($obj['start_date'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo xlt('End Date'); ?> (<?php echo xlt('optional'); ?>):</label>
                            <input type="date" name="end_date" class="form-control"
                                   value="<?php echo attr($obj['end_date'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bilingual-group">
                <h4><?php echo xlt('Instructions'); ?> / Hướng dẫn</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="instructions_vi" class="form-control vietnamese-field" rows="3"
                              placeholder="Hướng dẫn bổ sung bằng tiếng Việt"><?php echo text($obj['instructions_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="instructions" class="form-control english-field" rows="3"
                              placeholder="Additional instructions in English"><?php echo text($obj['instructions'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Equipment & Precautions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Equipment Needed'); ?> / Thiết bị cần thiết:</label>
                        <input type="text" name="equipment_needed" class="form-control"
                               placeholder="Example: Yoga mat, resistance band"
                               value="<?php echo attr($obj['equipment_needed'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="bilingual-group">
                <h4><?php echo xlt('Precautions'); ?> / Lưu ý</h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <textarea name="precautions_vi" class="form-control vietnamese-field" rows="2"
                              placeholder="Lưu ý an toàn bằng tiếng Việt"><?php echo text($obj['precautions_vi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <textarea name="precautions" class="form-control english-field" rows="2"
                              placeholder="Safety precautions in English"><?php echo text($obj['precautions'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-save">
                    <?php echo xlt('Save Exercise Prescription'); ?>
                </button>
                <button type="button" class="btn btn-secondary btn-cancel"
                        onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'">
                    <?php echo xlt('Cancel'); ?>
                </button>
            </div>
        </form>
    </div>
</body>
</html>