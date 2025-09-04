<?php

/**
 * SDOH (USCDI v3) – new/edit form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Loads the latest row by updated_at DESC (or empty if ?new=1).
 *
 * Implements a form for Social Determinants of Health (SDOH) assessment
 * using USCDI v3 standards. It handles both new assessments and editing existing ones.
 * The form includes sections for basic assessment info, various SDOH domains, pregnancy
 * details, and care planning. It uses list_options for standardized dropdowns.
 * Includes "New Assessment" button.
 *
 *  This file has been enhanced with assistance from ChatGPT to ensure code quality and maintainability.
 *  All generated code has been reviewed and tested for compliance with project standards.
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\Sdoh\HistorySdohService;

$pid    = (int)($_GET['pid'] ?? 0);
$rec_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = isset($_GET['new']) ? (int)$_GET['new'] : 0;

if (!AclMain::aclCheckCore('patients', 'med', '', ['write','addonly'])) {
    die(xlt("Not authorized"));
}

$csrf = CsrfUtils::collectCsrfToken();

// Fetch record: explicit id, else latest by updated_at; if ?new=1 start empty
if ($is_new) {
    $info = [];
} elseif ($rec_id) {
    $info = sqlQuery("SELECT * FROM form_history_sdoh WHERE id = ? AND pid = ?", [$rec_id, $pid]) ?: [];
} else {
    $info = HistorySdohService::getCurrentAssessment($rec_id) ?: [];
}

$goals_arr = json_decode($info['goals'] ?? '[]', true);
$goals_text = HistorySdohService::goalsToText($goals_arr, [
    'include_category' => true,
    'include_measure'  => true,
    'include_due'      => true
]);

// Helper to read either new or legacy column name (so you can transition DB safely)
function v($info, $new, $old = null)
{
    if ($old === null) {
        $old = 'sdoh_' . $new;
    }
    return $info[$new] ?? $info[$old] ?? '';
}

// Small wrapper so we don't repeat attributes everywhere
function render_list_select($field, $list_id, $current, $placeholder = 'Select...'): void
{
    echo generate_select_list(
        $field,
        $list_id,
        $current,
        xl($placeholder),
        xl($placeholder), // allow empty option
        '', // show id
        '', // allow add new
        '', // style
        ['class' => 'form-control']
    );
}

$self = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt("SDOH (USCDI v3)"); ?></title>
    <style>
      .card-header { padding:.5rem .75rem; }
      .card-body   { padding:.75rem; }
      .form-group  { margin-bottom:.5rem; }
      label        { margin-bottom:.25rem; }
      textarea.form-control { min-height:2.25rem; }
    </style>
</head>
<body class="body_top">
    <div class="container-xl mb-3">
        <form method="post" action="history_sdoh_save.php?pid=<?php echo ($pid); ?>" onsubmit="top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrf); ?>">
            <input type="hidden" name="history_sdoh_id" value="<?php echo attr($info['id'] ?? 0); ?>">

            <div class="">
                <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
                    <h4 class="m-0"><?php echo xlt("SDOH (USCDI v3)"); ?></h4>
                    <a class="btn btn-outline-primary btn-sm"  href="<?php echo ($self . '?pid=' . urlencode($pid) . '&new=1'); ?>">
                        <?php echo xlt("New Assessment"); ?>
                    </a>
                </div>

                <div class="card mb-3">
                    <div class="card-header font-weight-bold"><?php echo xlt("Assessment"); ?></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label><?php echo xlt("Assessment Date"); ?></label>
                                <input type="text" class="form-control datepicker" name="assessment_date"
                                    value="<?php echo attr(v($info, 'assessment_date')); ?>" data-date-format="Y-m-d">
                            </div>
                            <div class="form-group col-md-5">
                                <label><?php echo xlt("Screening Instrument/Tool"); ?></label>
                                <input type="text" class="form-control" name="screening_tool"
                                    value="<?php echo attr(v($info, 'screening_tool')); ?>">
                        <?php render_list_select('screening_tool', 'sdoh_instruments', v($info, 'screening_tool')); ?>
                            </div>
                            <div class="form-group col-md-4">
                                <label><?php echo xlt("Assessor"); ?></label>
                                <select class="form-control" name='assessor'>
                                    <option value=''><?php echo xlt("Select Assessor"); ?></option>
                                    <?php
                                    $res = sqlStatement("SELECT `id`, `username`, CONCAT(`fname`, ' ', `lname`) as name FROM `users` WHERE `authorized`=1");
                                    while ($orow = sqlFetchArray($res)) {
                                        echo " <option value='" . attr($orow['name']) . "'";
                                        if ($info['assessor'] == $orow['name']) {
                                            echo " selected";
                                        }
                                        echo ">" . text($orow['name']) . "</option>\n";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Domains + Context in 2-col grid -->
                <div class="row">
                    <?php
                    $domains = [
                        ['key' => 'food_insecurity',           'label' => xlt('Food Insecurity'),           'list' => 'sdoh_food_insecurity_risk'],
                        ['key' => 'housing_instability',       'label' => xlt('Housing Instability'),       'list' => 'sdoh_housing_worry'],
                        ['key' => 'transportation_insecurity', 'label' => xlt('Transportation Insecurity'), 'list' => 'sdoh_transportation_barrier'],
                        ['key' => 'utilities_insecurity',      'label' => xlt('Utilities Insecurity'),      'list' => 'sdoh_utilities_shutoff'],
                        ['key' => 'interpersonal_safety',      'label' => xlt('Interpersonal Safety'),      'list' => 'sdoh_ipv_yesno'],
                        ['key' => 'financial_strain',          'label' => xlt('Financial Strain'),          'list' => 'sdoh_financial_strain'],
                        ['key' => 'social_isolation',          'label' => xlt('Social Connections / Isolation'),'list' => 'sdoh_social_isolation_freq'],
                        ['key' => 'childcare_needs',           'label' => xlt('Childcare Needs'),           'list' => 'sdoh_childcare_needs'],
                        ['key' => 'digital_access',            'label' => xlt('Digital Access'),            'list' => 'sdoh_digital_access'],
                    ];
                    foreach ($domains as $d) :
                        $k       = $d['key'];
                        $label   = $d['label'];
                        $list_id = $d['list'];
                        $val     = v($info, $k);
                        $notes   = v($info, $k . '_notes');
                        ?>
                        <div class="col-12 col-md-6">
                            <div class="card h-100 mb-2">
                                <div class="card-header font-weight-bold"><?php echo $label; ?></div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-sm-5">
                                            <label><?php echo xlt("Status"); ?></label>
                                            <?php render_list_select($k, $list_id, $val); ?>
                                        </div>
                                        <div class="form-group col-sm-7">
                                            <label><?php echo xlt("Notes"); ?></label>
                                            <textarea class="form-control" rows="2" name="<?php echo attr($k); ?>_notes"><?php echo text($notes); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Context -->
                    <div class="col-12 col-md-6">
                        <div class="card h-100 mb-2">
                            <div class="card-header font-weight-bold"><?php echo xlt("Context"); ?></div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label><?php echo xlt("Employment Status"); ?></label>
                                        <?php render_list_select('employment_status', 'sdoh_employment_status', v($info, 'employment_status')); ?>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label><?php echo xlt("Education Level"); ?></label>
                                        <?php render_list_select('education_level', 'sdoh_education_level', v($info, 'education_level')); ?>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label><?php echo xlt("Caregiver"); ?></label>
                                        <?php render_list_select('caregiver_status', 'sdoh_ipv_yesno', v($info, 'caregiver_status'));  ?>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label><?php echo xlt("Veteran"); ?></label>
                                        <?php render_list_select('veteran_status', 'sdoh_ipv_yesno', v($info, 'veteran_status')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /row -->

                <!-- Pregnancy block -->
                <div class="card mb-2">
                    <div class="card-header font-weight-bold"><?php echo xlt("Pregnancy / Postpartum"); ?></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-6 col-md-2">
                                <label><?php echo xlt("Pregnant"); ?></label>
                                <?php render_list_select('pregnancy_status', 'pregnancy_status', v($info, 'pregnancy_status')); ?>
                            </div>
                            <div class="form-group col-6 col-md-3">
                                <label><?php echo xlt("Estimated Due Date"); ?></label>
                                <input type="text" class="form-control datepicker" name="pregnancy_edd"
                                    value="<?php echo attr(v($info, 'pregnancy_edd')); ?>" data-date-format="Y-m-d">
                            </div>
                            <div class="form-group col-6 col-md-2">
                                <label><?php echo xlt("Gravida"); ?></label>
                                <input type="number" min="0" class="form-control" name="pregnancy_gravida"
                                    value="<?php echo attr(v($info, 'pregnancy_gravida')); ?>">
                            </div>
                            <div class="form-group col-6 col-md-2">
                                <label><?php echo xlt("Para"); ?></label>
                                <input type="number" min="0" class="form-control" name="pregnancy_para"
                                    value="<?php echo attr(v($info, 'pregnancy_para')); ?>">
                            </div>
                            <div class="form-group col-6 col-md-1">
                                <label><?php echo xlt("Postpartum"); ?></label>
                                <?php render_list_select('postpartum_status', 'postpartum_status', v($info, 'postpartum_status')); ?>
                            </div>
                            <div class="form-group col-6 col-md-2">
                                <label><?php echo xlt("Postpartum End"); ?></label>
                                <input type="text" class="form-control datepicker" name="postpartum_end"
                                    value="<?php echo attr(v($info, 'postpartum_end')); ?>" data-date-format="Y-m-d">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Care plan -->
                <div class="card mb-3">
                    <div class="card-header font-weight-bold"><?php echo xlt("Care Plan"); ?></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><?php echo xlt("Patient Goals (SDOH)"); ?></label>
                            <textarea class="form-control" rows="3" name="goals" placeholder="<?php echo xla('Assessment calculated Treatment Plan Goal resources display here after assessment is saved.') ?>" readonly><?php echo text($goals_text); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php echo xlt("Interventions / Referrals"); ?></label>
                            <textarea class="form-control" rows="3" name="interventions"><?php echo text(v($info, 'interventions')); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-primary"><?php echo xlt("Save"); ?></button>
                    <a class="btn btn-secondary" href="<?php echo ($GLOBALS['webroot'] . '/interface/patient_file/history/history_sdoh_widget.php?pid=' . urlencode($pid)); ?>">
                        <?php echo xlt("Cancel"); ?>
                    </a>
                    <a class="btn btn-link"
                        href="<?php echo ($GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode($pid)); ?>">
                        &larr; <?php echo xlt("Back to Summary"); ?>
                    </a>
                </div>
            </div><!-- /container-fluid -->
        </form>
    </div><!-- /container -->

    <script>
        $(function () {
            $('.datepicker').datetimepicker({ timepicker:false, formatDate:'Y-m-d', format:'Y-m-d' });
        });
    </script>
</body>
</html>
