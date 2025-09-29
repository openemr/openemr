<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("pt_outcome_measures", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Outcome Measures'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        .bilingual-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Outcome Measures'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_outcome/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="therapist_id" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Measure Type'); ?>:</label>
                        <select name="measure_type" class="form-control" required>
                            <option value="ROM" <?php echo ($obj && $obj['measure_type'] == 'ROM') ? 'selected' : ''; ?>>Range of Motion (ROM)</option>
                            <option value="Strength" <?php echo ($obj && $obj['measure_type'] == 'Strength') ? 'selected' : ''; ?>>Strength</option>
                            <option value="Pain" <?php echo ($obj && $obj['measure_type'] == 'Pain') ? 'selected' : ''; ?>>Pain Level</option>
                            <option value="Function" <?php echo ($obj && $obj['measure_type'] == 'Function') ? 'selected' : ''; ?>>Functional Status</option>
                            <option value="Balance" <?php echo ($obj && $obj['measure_type'] == 'Balance') ? 'selected' : ''; ?>>Balance</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Measurement Date'); ?>:</label>
                        <input type="date" name="measurement_date" class="form-control" required
                               value="<?php echo attr($obj['measurement_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Baseline Value'); ?>:</label>
                        <input type="number" step="0.1" name="baseline_value" class="form-control"
                               value="<?php echo attr($obj['baseline_value'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Current Value'); ?>:</label>
                        <input type="number" step="0.1" name="current_value" class="form-control" required
                               value="<?php echo attr($obj['current_value'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Target Value'); ?>:</label>
                        <input type="number" step="0.1" name="target_value" class="form-control"
                               value="<?php echo attr($obj['target_value'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><?php echo xlt('Unit of Measurement'); ?>:</label>
                <input type="text" name="unit" class="form-control" placeholder="degrees, kg, 0-10 scale, etc."
                       value="<?php echo attr($obj['unit'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><?php echo xlt('Notes'); ?>:</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo text($obj['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo xlt('Save Outcome Measure'); ?></button>
                <button type="button" class="btn btn-secondary" onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'"><?php echo xlt('Cancel'); ?></button>
            </div>
        </form>
    </div>
</body>
</html>
