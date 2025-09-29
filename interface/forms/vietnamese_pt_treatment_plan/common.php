<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("pt_treatment_plans", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Treatment Plan'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        .bilingual-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .vietnamese-field { background-color: #fff3cd; }
        .english-field { background-color: #d1ecf1; }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Treatment Plan'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_treatment_plan/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="created_by" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <div class="form-group">
                <label><?php echo xlt('Plan Name'); ?>:</label>
                <input type="text" name="plan_name" class="form-control" required
                       value="<?php echo attr($obj['plan_name'] ?? ''); ?>">
            </div>

            <div class="bilingual-group">
                <h4><?php echo xlt('Diagnosis'); ?></h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <input type="text" name="diagnosis_vi" class="form-control vietnamese-field"
                           value="<?php echo attr($obj['diagnosis_vi'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <input type="text" name="diagnosis_en" class="form-control english-field"
                           value="<?php echo attr($obj['diagnosis_en'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Start Date'); ?>:</label>
                        <input type="date" name="start_date" class="form-control"
                               value="<?php echo attr($obj['start_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Estimated Duration (weeks)'); ?>:</label>
                        <input type="number" name="estimated_duration_weeks" class="form-control" min="1"
                               value="<?php echo attr($obj['estimated_duration_weeks'] ?? '8'); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Status'); ?>:</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo ($obj && $obj['status'] == 'active') ? 'selected' : 'selected'; ?>><?php echo xlt('Active'); ?></option>
                            <option value="completed" <?php echo ($obj && $obj['status'] == 'completed') ? 'selected' : ''; ?>><?php echo xlt('Completed'); ?></option>
                            <option value="on_hold" <?php echo ($obj && $obj['status'] == 'on_hold') ? 'selected' : ''; ?>><?php echo xlt('On Hold'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo xlt('Save Treatment Plan'); ?></button>
                <button type="button" class="btn btn-secondary" onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'"><?php echo xlt('Cancel'); ?></button>
            </div>
        </form>
    </div>
</body>
</html>
