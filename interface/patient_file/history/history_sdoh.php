<?php

/**
 * SDOH (USCDI v3) – new/edit form
 * Production-ready version with proper data handling
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\ListService;
use OpenEMR\Services\Sdoh\HistorySdohService;

$pid = (int)($_GET['pid'] ?? 0);
$rec_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = isset($_GET['new']) ? (int)$_GET['new'] : 0;

if (!AclMain::aclCheckCore('patients', 'med', '', ['write', 'addonly'])) {
    die(xlt("Not authorized"));
}

$csrf = CsrfUtils::collectCsrfToken();

// Fetch record
if ($is_new) {
    $info = [];
} elseif ($rec_id) {
    $info = sqlQuery("SELECT * FROM form_history_sdoh WHERE id = ? AND pid = ?", [$rec_id, $pid]) ?: [];
} else {
    $info = HistorySdohService::getCurrentAssessment($pid) ?: [];
}

// Process goals and interventions
$goals_arr = [];
$interventions_arr = [];
if (!empty($info)) {
    $goals_arr = HistorySdohService::buildGoals($info, $pid);
    $interventions_arr = HistorySdohService::buildInterventions($info, $pid, ['include_manual' => false]);
}

$goals_text = HistorySdohService::goalsToText($goals_arr);
$interventions_text = HistorySdohService::interventionsToText($interventions_arr);

// Helper function
function v($info, $field, $default = '')
{
    return $info[$field] ?? $default;
}

function render_list_select($field, $list_id, $current, $placeholder = 'Select...', $extra_attrs = ''): void
{
    $opts = (new ListService())->getOptionsByListName($list_id);
    echo "<select class='form-control' name='" . attr($field) . "' data-list='" . attr($list_id) . "' $extra_attrs>";
    echo "<option value=''>" . text(xl($placeholder)) . "</option>";
    foreach ($opts as $o) {
        $sel = ($current === $o['option_id']) ? " selected" : "";
        $codes = $o['codes'] ?? '';
        // Handle both JSON and prefix:code formats
        if (substr($codes, 0, 1) === '{') {
            $code_data = json_decode($codes, true) ?: [];
            $code = $code_data['code'] ?? '';
            $system = $code_data['system'] ?? '';
        } elseif (strpos($codes, ':') !== false) {
            list($system, $code) = explode(':', $codes, 2);
        } else {
            $code = '';
            $system = '';
        }
        echo "<option value='" . attr($o['option_id']) . "'" .
            " data-code='" . attr($code) . "' data-system='" . attr($system) . "'" .
            "{$sel}>" . text($o['title']) . "</option>";
    }
    echo "</select>";
}

$self = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt("SDOH Assessment (USCDI v3)"); ?></title>
    <style>
      .card-header {
        padding: .5rem .75rem;
      }

      .card-body {
        padding: .75rem;
      }

      .form-group {
        margin-bottom: .75rem;
      }

      label {
        margin-bottom: .25rem;
        font-weight: 500;
      }

      textarea.form-control {
        min-height: 2.5rem;
      }

      .domain-card {
        height: 100%;
      }

      .text-muted {
        font-size: 0.875rem;
      }

      .disability-question {
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
      }
    </style>
</head>
<body class="body_top">
    <div class="container-xl mb-3">
        <form method="post" action="history_sdoh_save.php?pid=<?php echo attr_url($pid); ?>" onsubmit="top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrf); ?>">
            <input type="hidden" name="history_sdoh_id" value="<?php echo attr($info['id'] ?? 0); ?>">

            <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
                <h4 class="m-0"><?php echo xlt("Social Determinants of Health Assessment"); ?></h4>
                <div>
                    <?php if (!$is_new && !empty($info)) : ?>
                        <span class="text-muted mr-3">
                            <?php echo xlt("Last Updated") . ": " . text(oeFormatShortDate($info['updated_at'] ?? '')); ?>
                        </span>
                    <?php endif; ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?php echo attr($self . '?pid=' . $pid . '&new=1'); ?>">
                        <?php echo xlt("New Assessment"); ?>
                    </a>
                </div>
            </div>

            <!-- Assessment Information -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold"><?php echo xlt("Assessment Information"); ?></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Assessment Date"); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" name="assessment_date" required
                                value="<?php echo attr(v($info, 'assessment_date', date('Y-m-d'))); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo xlt("Screening Tool"); ?></label>
                            <?php render_list_select('screening_tool', 'sdoh_instruments', v($info, 'screening_tool')); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Assessor"); ?></label>
                            <select class="form-control" name="assessor">
                                <option value=""><?php echo xlt("Select Assessor"); ?></option>
                                <?php
                                $current_user = $_SESSION['authUser'] ?? '';
                                $res = sqlStatement("SELECT id, username, CONCAT(fname, ' ', lname) as name FROM users WHERE authorized=1 ORDER BY lname, fname");
                                while ($row = sqlFetchArray($res)) {
                                    $selected = '';
                                    if (!empty($info['assessor']) && $info['assessor'] == $row['name']) {
                                        $selected = ' selected';
                                    } elseif (empty($info['assessor']) && $row['username'] == $current_user) {
                                        $selected = ' selected';
                                    }
                                    echo "<option value='" . attr($row['name']) . "'$selected>" . text($row['name']) . "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label><?php echo xlt("Score"); ?></label>
                            <input type="number" class="form-control" name="instrument_score" id="total_score" readonly
                                value="<?php echo attr(v($info, 'instrument_score', 0)); ?>">
                            <small class="text-muted"><?php echo xlt("Auto-calculated"); ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hunger Vital Signs (Required for ONC) -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold">
                    <?php echo xlt("Hunger Vital Signs"); ?>
                    <small class="text-muted ml-2">LOINC 88121-9 (Required)</small>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?php echo xlt("Within the past 12 months, we worried whether our food would run out before we got money to buy more"); ?></label>
                        <small class="text-muted d-block mb-1">LOINC 88122-7</small>
                        <?php render_list_select('hunger_q1', 'vital_signs_answers', v($info, 'hunger_q1')); ?>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt("Within the past 12 months, the food we bought just didn't last and we didn't have money to get more"); ?></label>
                        <small class="text-muted d-block mb-1">LOINC 88123-5</small>
                        <?php render_list_select('hunger_q2', 'vital_signs_answers', v($info, 'hunger_q2')); ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Hunger Score"); ?></label>
                            <input type="number" class="form-control" name="hunger_score" id="hunger_score" readonly
                                value="<?php echo attr(v($info, 'hunger_score', 0)); ?>">
                            <small class="text-muted">0 = No risk, ≥1 = At risk</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo xlt("Food Insecurity Status"); ?></label>
                            <?php render_list_select(
                                'food_insecurity',
                                'sdoh_food_insecurity_risk',
                                v($info, 'food_insecurity'),
                                'Auto-determined',
                                'readonly'
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SDOH Domains Grid -->
            <div class="row">
                <?php
                $domains = [
                    ['key' => 'housing_instability', 'label' => 'Housing Instability', 'list' => 'sdoh_housing_worry'],
                    ['key' => 'transportation_insecurity', 'label' => 'Transportation Insecurity', 'list' => 'sdoh_transportation_barrier'],
                    ['key' => 'utilities_insecurity', 'label' => 'Utilities Insecurity', 'list' => 'sdoh_utilities_shutoff'],
                    ['key' => 'interpersonal_safety', 'label' => 'Interpersonal Safety', 'list' => 'sdoh_ipv_yesno'],
                    ['key' => 'financial_strain', 'label' => 'Financial Strain', 'list' => 'sdoh_financial_strain'],
                    ['key' => 'social_isolation', 'label' => 'Social Isolation', 'list' => 'sdoh_social_isolation_freq'],
                    ['key' => 'childcare_needs', 'label' => 'Childcare Needs', 'list' => 'sdoh_childcare_needs'],
                    ['key' => 'digital_access', 'label' => 'Digital Access', 'list' => 'sdoh_digital_access'],
                ];

                foreach ($domains as $d) : ?>
                    <div class="col-12 col-md-6 mb-3">
                        <div class="card domain-card">
                            <div class="card-header font-weight-bold"><?php echo xlt($d['label']); ?></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label><?php echo xlt("Status"); ?></label>
                                    <?php render_list_select($d['key'], $d['list'], v($info, $d['key'])); ?>
                                </div>
                                <div class="form-group mb-0">
                                    <label><?php echo xlt("Notes"); ?></label>
                                    <textarea class="form-control" rows="2" name="<?php echo attr($d['key']); ?>_notes"><?php echo text(v($info, $d['key'] . '_notes')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Context -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold"><?php echo xlt("Social Context"); ?></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Employment Status"); ?></label>
                            <?php render_list_select('employment_status', 'sdoh_employment_status', v($info, 'employment_status')); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Education Level"); ?></label>
                            <?php render_list_select('education_level', 'sdoh_education_level', v($info, 'education_level')); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Caregiver Status"); ?></label>
                            <select class="form-control" name="caregiver_status">
                                <option value=""><?php echo xlt("Select"); ?></option>
                                <option value="yes" <?php echo v($info, 'caregiver_status') == 'yes' ? 'selected' : ''; ?>><?php echo xlt("Yes"); ?></option>
                                <option value="no" <?php echo v($info, 'caregiver_status') == 'no' ? 'selected' : ''; ?>><?php echo xlt("No"); ?></option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Veteran Status"); ?></label>
                            <select class="form-control" name="veteran_status">
                                <option value=""><?php echo xlt("Select"); ?></option>
                                <option value="yes" <?php echo v($info, 'veteran_status') == 'yes' ? 'selected' : ''; ?>><?php echo xlt("Yes"); ?></option>
                                <option value="no" <?php echo v($info, 'veteran_status') == 'no' ? 'selected' : ''; ?>><?php echo xlt("No"); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disability Status -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold">
                    <?php echo xlt("Disability Status"); ?>
                    <small class="text-muted ml-2">ACS 6-item set</small>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?php echo xlt("Overall Disability Status"); ?></label>
                        <?php render_list_select('disability_status', 'disability_status', v($info, 'disability_status')); ?>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt("Additional Notes"); ?></label>
                        <textarea class="form-control" rows="2" name="disability_status_notes"><?php echo text(v($info, 'disability_status_notes')); ?></textarea>
                    </div>
                    <?php
                    // If you prefer a different list id than 'loinc_yes_no' (Yes/No), change here.
                    $yesNoList = 'sdoh_ipv_yesno';
                    // Preload JSON scale answers so we can re-fill controls
                    $scale = json_decode($info['disability_scale'] ?? '[]', true) ?: [];
                    // helper to read saved answer code (LA33-6, LA32-8, etc.)
                    $get = function ($key) use ($scale) {
                        return $scale[$key]['code'] ?? '';
                    };
                    // Renders one row: label + yes/no/select + optional notes
                    function fn_row($fieldKey, $label, $yesNoList, $get)
                    {
                        echo "<div class='form-row align-items-end mb-2'>";
                        echo "  <div class='form-group col-md'>";
                        echo "    <label>" . text($label) . "</label>";
                        render_list_select("dscale[$fieldKey][code]", $yesNoList, call_user_func($get, $fieldKey), 'Select...');
                        echo "  </div>";
                        echo "</div>";
                    }
                    // LOINC codes (display only; selection is just Yes/No answers)
                    fn_row('walk_climb', 'Do you have serious difficulty walking or climbing stairs? (LOINC 69859-7)', $yesNoList, $get);
                    fn_row('seeing', 'Do you have serious difficulty seeing, even when wearing glasses? (LOINC 69861-3)', $yesNoList, $get);
                    fn_row('hearing', 'Do you have serious difficulty hearing? (LOINC 69860-5)', $yesNoList, $get);
                    fn_row('cognitive', 'Do you have serious difficulty concentrating, remembering, or making decisions? (LOINC 69862-1)', $yesNoList, $get);
                    fn_row('dressing_bathing', 'Do you have difficulty dressing or bathing? (LOINC 69863-9)', $yesNoList, $get);
                    fn_row('errands', 'Do you have difficulty doing errands alone? (LOINC 69864-7)', $yesNoList, $get);
                    ?>
                </div>
            </div>
            <!-- Pregnancy/Postpartum -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold"><?php echo xlt("Pregnancy / Postpartum Status"); ?></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Pregnancy Status"); ?></label>
                            <?php render_list_select('pregnancy_status', 'pregnancy_status', v($info, 'pregnancy_status')); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Estimated Due Date"); ?></label>
                            <input type="text" class="form-control datepicker" name="pregnancy_edd"
                                value="<?php echo attr(v($info, 'pregnancy_edd')); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Postpartum Status"); ?></label>
                            <?php render_list_select('postpartum_status', 'postpartum_status', v($info, 'postpartum_status')); ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label><?php echo xlt("Postpartum End Date"); ?></label>
                            <input type="text" class="form-control datepicker" name="postpartum_end"
                                value="<?php echo attr(v($info, 'postpartum_end')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals and Interventions -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold"><?php echo xlt("Care Planning"); ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?php echo xlt("Generated Goals"); ?></label>
                        <textarea class="form-control" rows="3" readonly><?php echo text($goals_text); ?></textarea>
                        <small class="text-muted"><?php echo xlt("Goals are automatically generated based on positive SDOH findings"); ?></small>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt("Generated Interventions"); ?></label>
                        <textarea class="form-control" rows="3" readonly><?php echo text($interventions_text); ?></textarea>
                        <small class="text-muted"><?php echo xlt("Interventions are automatically generated based on positive SDOH findings"); ?></small>
                    </div>
                    <div class="form-group">
                        <label><?php echo xlt("Additional Interventions (Manual)"); ?></label>
                        <textarea class="form-control" rows="3" name="interventions"
                            placeholder="<?php echo xla("Enter any additional interventions, one per line"); ?>"><?php echo text(v($info, 'interventions')); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Extended Domains -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold"><?php echo xlt("Additional Assessment Domains"); ?></div>
                <div class="card-body">
                    <div id="ext-domain-list"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-ext-domain">
                        <?php echo xlt("Add Domain"); ?>
                    </button>
                    <input type="hidden" name="extended_domains" id="extended_domains"
                        value="<?php echo attr(v($info, 'extended_domains', '[]')); ?>">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mb-4">
                <button type="submit" class="btn btn-primary"><?php echo xlt("Save Assessment"); ?></button>
                <a class="btn btn-secondary" href="history_sdoh_widget.php?pid=<?php echo attr_url($pid); ?>">
                    <?php echo xlt("Cancel"); ?>
                </a>
            </div>
        </form>
    </div>

    <script>
        jQuery(function ($) {
            // Initialize datepickers
            $('.datepicker').datetimepicker({
                timepicker: false,
                format: 'Y-m-d',
                scrollInput: false,
                scrollMonth: false
            });

            // Hunger Vital Signs scoring
            function calculateHungerScore() {
                var score = 0;
                var q1 = $('[name="hunger_q1"]').val();
                var q2 = $('[name="hunger_q2"]').val();

                // Score if "Often true" (LA28397-0) or "Sometimes true" (LA28398-8)
                if (q1 === 'LA28397-0' || q1 === 'LA28398-8') score++;
                if (q2 === 'LA28397-0' || q2 === 'LA28398-8') score++;

                $('#hunger_score').val(score);

                // Update food insecurity status
                var foodStatus = $('[name="food_insecurity"]');
                foodStatus.prop('readonly', false);
                if (score >= 1) {
                    foodStatus.val('at_risk');
                } else if (q1 && q2) {
                    foodStatus.val('no_risk');
                }
                foodStatus.prop('readonly', true);

                updateTotalScore();
            }

            $('[name="hunger_q1"], [name="hunger_q2"]').on('change', calculateHungerScore);

            // Calculate total positive domains
            function updateTotalScore() {
                var count = 0;

                // Check standard domains
                var positiveValues = ['yes', 'at_risk', 'positive', 'often', 'sometimes', 'yes_med', 'yes_nonmed',
                    'already_off', 'very_hard', 'hard', 'somewhat_hard'];

                $('select[data-list]').each(function () {
                    var val = $(this).val();
                    if (val && positiveValues.indexOf(val) >= 0) {
                        count++;
                    }
                });

                $('#total_score').val(count);
            }

            $('select[data-list]').on('change', updateTotalScore);

            // Extended domains management
            var extDomains = <?php echo json_encode(json_decode(v($info, 'extended_domains', '[]'), true) ?: []); ?>;

            function renderExtDomains() {
                var $list = $('#ext-domain-list');
                $list.empty();

                extDomains.forEach(function (d, idx) {
                    var html = `
                    <div class="border rounded p-2 mb-2">
                        <div class="form-row">
                            <div class="col-md-4">
                                <label><?php echo xlt("Domain"); ?></label>
                                <input type="text" class="form-control ext-domain" data-idx="${idx}"
                                    value="${d.domain || ''}" placeholder="<?php echo xla("Domain name"); ?>">
                            </div>
                            <div class="col-md-3">
                                <label><?php echo xlt("Status"); ?></label>
                                <select class="form-control ext-status" data-idx="${idx}">
                                    <option value=""><?php echo xlt("Select"); ?></option>
                                    <option value="yes" ${d.status === 'yes' ? 'selected' : ''}><?php echo xlt("Yes/Positive"); ?></option>
                                    <option value="no" ${d.status === 'no' ? 'selected' : ''}><?php echo xlt("No/Negative"); ?></option>
                                    <option value="unknown" ${d.status === 'unknown' ? 'selected' : ''}><?php echo xlt("Unknown"); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label><?php echo xlt("Notes"); ?></label>
                                <input type="text" class="form-control ext-notes" data-idx="${idx}"
                                    value="${d.notes || ''}" placeholder="<?php echo xla("Notes"); ?>">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-danger d-block remove-ext" data-idx="${idx}">
                                    <?php echo xlt("Remove"); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                    $list.append(html);
                });
            }

            $('#add-ext-domain').on('click', function () {
                extDomains.push({domain: '', status: '', notes: ''});
                renderExtDomains();
            });

            $(document).on('click', '.remove-ext', function () {
                var idx = $(this).data('idx');
                extDomains.splice(idx, 1);
                renderExtDomains();
            });

            $(document).on('change', '.ext-domain, .ext-status, .ext-notes', function () {
                var idx = $(this).data('idx');
                var field = $(this).hasClass('ext-domain') ? 'domain' :
                    $(this).hasClass('ext-status') ? 'status' : 'notes';
                extDomains[idx][field] = $(this).val();
            });

            // Save extended domains before submit
            $('form').on('submit', function () {
                $('#extended_domains').val(JSON.stringify(extDomains));
            });

            // Initialize
            renderExtDomains();
            calculateHungerScore();
            updateTotalScore();
        });
    </script>
</body>
</html>