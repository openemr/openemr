<?php

/**
 * SDOH (USCDI v3) widget view page.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 *
 * Renders the latest SDOH assessment with Edit/New buttons.
 *
 *  This file has been enhanced with assistance from ChatGPT to ensure code quality and maintainability.
 *  All generated code has been reviewed and tested for compliance with project standards.
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once(dirname(__FILE__, 3) . "/globals.php");
require_once($srcdir . "/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\Sdoh\HistorySdohService;

/** Lookup a list option title by (list_id, option_id). */
function hs_lo_title(string $listId, ?string $value): string
{
    if ($value === null || $value === '') {
        return xl('—');
    }
    static $cache = [];
    $key = $listId . '|' . $value;
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $row = sqlQuery("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", [$listId, $value]);
    if (!empty($row['title'])) {
        return $cache[$key] = $row['title'];
    }
    // Fallback: prettify the slug
    $pretty = ucwords(str_replace('_', ' ', $value));
    return $cache[$key] = $pretty;
}

/** Clip a string to N chars (UI-friendly), preserving plain text. */
function hs_clip(?string $s, int $len = 80): string
{
    $s = $s ?? '';
    if (mb_strlen($s) <= $len) {
        return $s;
    }
    return mb_substr($s, 0, $len - 1) . '…';
}

$authorized = AclMain::aclCheckCore('patients', 'med');
$self_form = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh.php";
$list_url = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_list.php";

$info = [];
if ($authorized && !empty($pid)) {
    // Latest assessment by updated_at DESC, id DESC
    $info = sqlQuery(
        "SELECT * FROM form_history_sdoh WHERE pid = ? ORDER BY updated_at DESC, id DESC LIMIT 1",
        [$pid]
    ) ?: [];
}

$goals_arr = json_decode($info['goals'] ?? '[]', true);
$goals_text = HistorySdohService::goalsToText($goals_arr, [
    'include_category' => true,
    'include_measure' => true,
    'include_due' => true
]);
$interventions_arr = json_decode($info['interventions'] ?? '[]', true);
$interventions_text = HistorySdohService::interventionsToText($interventions_arr, [
    'include_category' => true,
    'include_measure'  => true,
    'include_due'      => true
]);

// Domain → list_id mapping (match your form)
$map = [
    'food_insecurity' => 'sdoh_food_insecurity_risk',
    'housing_instability' => 'sdoh_housing_worry',
    'transportation_insecurity' => 'sdoh_transportation_barrier',
    'utilities_insecurity' => 'sdoh_utilities_shutoff',
    'interpersonal_safety' => 'sdoh_ipv_yesno',
    'financial_strain' => 'sdoh_financial_strain',
    'social_isolation' => 'sdoh_social_isolation_freq',
    'childcare_needs' => 'sdoh_childcare_needs',
    'digital_access' => 'sdoh_digital_access',
];

// Convenience values for header meta
$assessment_date = $info['assessment_date'] ?? $info['sdoh_assessment_date'] ?? '';
$screening_tool = $info['screening_tool'] ?? $info['sdoh_screening_tool'] ?? '';
$assessor = $info['assessor'] ?? $info['sdoh_assessor'] ?? '';
$updated_at = $info['updated_at'] ?? '';
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?= xlt("SDOH Assessment"); ?></title>
</head>
<body class="body_top">
    <div id="container_div" class="container mt-3">
        <h4><?= xlt("Social Determinants of Health (SDOH)"); ?></h4>
        <div class="row">
            <div class="col-sm-12">
                <?php
                // highlight nav tab
                $list_id = "sdoh";
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
        <?php if (!$authorized) : ?>
            <div class="alert alert-warning mb-2"><?= xlt("Not authorized"); ?></div>
        <?php elseif (empty($pid)) : ?>
            <div class="alert alert-info mb-2"><?= xlt("No patient selected."); ?></div>
        <?php else : ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold"><?= xlt("SDOH (USCDI v3)"); ?></span>
                    <span class="btn-group btn-group-sm">
                        <?php
                        $newUrl = $self_form . '?' . http_build_query(['pid' => $pid, 'new' => 1]);
                        $listUrl = $list_url . '?' . http_build_query(['pid' => $pid]);
                        ?>
                        <a class="btn btn-outline-primary" href="<?= $newUrl; ?>"><?= xlt("New Assessment"); ?></a>
                        <?php if (!empty($info['id'])) :
                            $editUrl = $self_form . '?' . http_build_query(['pid' => $pid, 'id' => (int)$info['id']]);
                            ?>
                            <a class="btn btn-primary" href="<?= $editUrl; ?>"><?= xlt("Edit"); ?></a>
                        <?php endif; ?>

                        <a class="btn btn-secondary" href="<?= $listUrl; ?>"><?= xlt("View All"); ?></a>
                    </span>
                </div>

                <div class="card-body">
                    <?php if (empty($info)) : ?>
                        <div class="text-muted"><?= xlt("No SDOH assessments found."); ?></div>
                    <?php else : ?>
                        <div class="mb-2 small text-muted">
                            <?= text(xl("Assessment Date")); ?>: <?= text($assessment_date ?: '—'); ?> &nbsp;|&nbsp;
                            <?= text(xl("Tool")); ?>: <?= text($screening_tool ?: '—'); ?> &nbsp;|&nbsp;
                            <?= text(xl("Assessor")); ?>: <?= text($assessor ?: '—'); ?> &nbsp;|&nbsp;
                            <?= text(xl("Updated")); ?>: <?= text($updated_at ?: '—'); ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-2">
                                <thead>
                                <tr>
                                    <th><?= xlt("Domain"); ?></th>
                                    <th><?= xlt("Status"); ?></th>
                                    <th><?= xlt("Notes"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($map as $col => $listId) : ?>
                                    <?php
                                    $val = $info[$col] ?? $info['sdoh_' . $col] ?? '';
                                    $notes = $info[$col . '_notes'] ?? $info['sdoh_' . $col . '_notes'] ?? '';
                                    ?>
                                    <tr>
                                        <td><?= text(xl(ucwords(str_replace('_', ' ', $col)))); ?></td>
                                        <td><?= text(hs_lo_title($listId, $val)); ?></td>
                                        <td><?= text(hs_clip($notes, 90)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($goals_text || $interventions_text) : ?>
                            <div class="row small">
                                <div class="form-group col-md-12 mb-2">
                                    <label><?php echo xlt("Patient Goals (SDOH)"); ?></label>
                                    <textarea class="form-control" rows="5" readonly><?php echo text($goals_text); ?></textarea>
                                </div>
                                <div class="form-group col-md-12 mb-2">
                                    <label><?php echo xlt("Interventions / Referrals"); ?></label>
                                    <textarea class="form-control" rows="5" readonly><?php echo text($interventions_text); ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div> <!-- /card-body -->
            </div> <!-- /card -->
        <?php endif; ?>
    </div>
</body>
</html>
