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
 * Renders the latest SDOH assessment with Edit/New buttons and
 * shows missing domains + status badges.
 */

$srcdir = dirname(__FILE__, 4) . "/library";
require_once(dirname(__FILE__, 3) . "/globals.php");
require_once($srcdir . "/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\SDOH\HistorySdohService;

$logger = new SystemLogger();

/** Lookup a list option title by (list_id, option_id). */
function hs_lo_title(string $listId, ?string $value): string
{
    if ($value === null || $value === '') {
        return text('—');
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
    $s ??= '';
    if (mb_strlen($s) <= $len) {
        return $s;
    }
    return mb_substr($s, 0, $len - 1) . '…';
}

/** Badge class for a domain value */
function hs_badge_class(?string $val): string
{
    if ($val === null || $val === '') {
        return 'badge-secondary';
    }
    // Same “positive” set your form’s JS uses
    static $positive = [
        'yes', 'at_risk', 'positive', 'often', 'sometimes', 'yes_med', 'yes_nonmed',
        'already_off', 'very_hard', 'hard', 'somewhat_hard'
    ];
    if (in_array($val, $positive, true)) {
        return 'badge-danger';
    }
    if (in_array($val, ['no', 'none', 'negative'], true)) {
        return 'badge-success';
    }
    return 'badge-warning';
}

$authorized = AclMain::aclCheckCore('patients', 'med');
$self_form = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh.php";
$list_url  = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_list.php";

$info = [];
if ($authorized && !empty($pid)) {
    // Latest assessment by updated_at DESC, id DESC
    $info = sqlQuery(
        "SELECT * FROM form_history_sdoh WHERE pid = ? ORDER BY updated_at DESC, id DESC LIMIT 1",
        [$pid]
    ) ?: [];
}

$goals_arr = json_decode($info['goals'] ?? '', true) ?? [];
if (!is_array($goals_arr)) {
    $logger->warning("Dropping goals json because it is not an array.");
    $goals_arr = [];
}
$goals_text = HistorySdohService::goalsToText($goals_arr, [
    'include_category' => true,
    'include_measure'  => true,
    'include_due'      => true
]);

$interventions_arr = json_decode($info['interventions'] ?? '[]', true) ?? [];
if (!is_array($interventions_arr)) {
    $logger->warning("Dropping interventions json because it is not an array.");
    $interventions_arr = [];
}
$interventions_text = HistorySdohService::interventionsToText($interventions_arr, [
    'include_category' => true,
    'include_measure'  => true,
    'include_due'      => true
]);

// Domain → list_id mapping (keep in sync with form)
$map = HistorySdohService::getListMapForDomains();

// Convenience values for header meta
$assessment_date = $info['assessment_date'] ?? $info['sdoh_assessment_date'] ?? '';
$screening_tool  = $info['screening_tool']  ?? $info['sdoh_screening_tool']  ?? '';
$assessor        = $info['assessor']        ?? $info['sdoh_assessor']        ?? '';
$updated_at      = $info['updated_at']      ?? '';

// Build “missing” list + quick counts
$missingDomains = [];
$positiveCount  = 0;

foreach ($map as $col => $listId) {
    $val = $info[$col] ?? $info['sdoh_' . $col] ?? '';
    if ($val === '' || $val === null) {
        // Label exactly as displayed in table header
        $missingDomains[] = xlt(ucwords(str_replace('_', ' ', $col)));
    } else {
        // match the same positive set as hs_badge_class()
        if (
            in_array($val, [
            'yes','at_risk','positive','often','sometimes','yes_med','yes_nonmed',
            'already_off','very_hard','hard','somewhat_hard'
            ], true)
        ) {
            $positiveCount++;
        }
    }
}

// Hunger VS summary
$hungerScore  = (int)($info['hunger_score'] ?? 0);
$foodRiskId   = $info['food_insecurity'] ?? '';
$foodRiskText = $foodRiskId !== '' ? hs_lo_title('sdoh_food_insecurity_risk', $foodRiskId) : '';
$foodBadge    = ($hungerScore >= 1 || $foodRiskId === 'at_risk') ? 'badge-danger' : ($foodRiskId ? 'badge-success' : 'badge-secondary');

// Disability summary
$disabId    = $info['disability_status'] ?? '';
$disabText  = $disabId !== '' ? hs_lo_title('disability_status', $disabId) : '';
$disabBadge = $disabId ? 'badge-info' : 'badge-secondary';

// Score: prefer stored instrument_score, else computed positives
$totalScore = (string) (($info['instrument_score'] ?? '') !== '' ? (int)$info['instrument_score'] : $positiveCount);

?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("SDOH Assessment"); ?></title>
</head>
<body class="body_top">
    <div id="container_div" class="container mt-3">
        <h4><?php echo xlt("Social Determinants of Health (SDOH)"); ?></h4>
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
            <div class="alert alert-warning mb-2"><?php echo xlt("Not authorized"); ?></div>
        <?php elseif (empty($pid)) : ?>
            <div class="alert alert-info mb-2"><?php echo xlt("No patient selected."); ?></div>
        <?php else : ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold"><?php echo xlt("SDOH (USCDI v3)"); ?></span>
                    <span class="btn-group btn-group-sm">
                    <?php
                    $newUrl  = $self_form . '?' . http_build_query(['pid' => $pid, 'new' => 1]);
                    $listUrl = $list_url  . '?' . http_build_query(['pid' => $pid]);
                    ?>
                    <a class="btn btn-outline-primary" href="<?php echo attr($newUrl); ?>"><?php echo xlt("New Assessment"); ?></a>
                    <?php if (!empty($info['id'])) :
                        $editUrl = $self_form . '?' . http_build_query(['pid' => $pid, 'id' => (int)$info['id']]);
                        ?>
                        <a class="btn btn-primary" href="<?php echo attr($editUrl); ?>"><?php echo xlt("Edit"); ?></a>
                    <?php endif; ?>
                    <a class="btn btn-secondary" href="<?php echo attr($listUrl); ?>"><?php echo xlt("View All"); ?></a>
                </span>
                </div>

                <div class="card-body">
                    <?php if (empty($info)) : ?>
                        <div class="text-muted"><?php echo xlt("No SDOH assessments found."); ?></div>
                    <?php else : ?>
                        <!-- Meta line -->
                        <div class="mb-2 small text-muted">
                            <?php echo xlt("Assessment Date"); ?>:
                            <?php echo text($assessment_date ?: '—'); ?> &nbsp;|&nbsp;
                            <?php echo xlt("Tool"); ?>:
                            <?php echo text($screening_tool ?: '—'); ?> &nbsp;|&nbsp;
                            <?php echo xlt("Assessor"); ?>:
                            <?php echo text($assessor ?: '—'); ?> &nbsp;|&nbsp;
                            <?php echo xlt("Updated"); ?>:
                            <?php echo text($updated_at ?: '—'); ?>
                        </div>

                        <!-- Quick status badges -->
                        <div class="mb-2">
                        <span class="badge badge-primary mr-1">
                            <?php echo xlt("Total Positives"); ?>: <?php echo text($totalScore); ?>
                        </span>
                            <span class="badge <?php echo attr($foodBadge); ?> mr-1">
                            <?php echo xlt("Hunger VS"); ?>:
                            <?php echo text($foodRiskText !== '' ? $foodRiskText : ($hungerScore >= 1 ? xlt('At risk') : xlt('No risk'))); ?>
                        </span>
                            <span class="badge <?php echo attr($disabBadge); ?>">
                            <?php echo xlt("Disability"); ?>:
                            <?php echo text($disabText !== '' ? $disabText : xlt('—')); ?>
                        </span>
                        </div>

                        <!-- Missing domains -->
                        <?php if (!empty($missingDomains)) : ?>
                            <div class="alert alert-warning py-1 px-2 mb-2">
                                <strong><?php echo xlt("Missing"); ?>:</strong>
                                <?php echo text(implode(", ", $missingDomains)); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Domain table -->
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-2">
                                <thead>
                                <tr>
                                    <th><?php echo xlt("Domain"); ?></th>
                                    <th><?php echo xlt("Status"); ?></th>
                                    <th><?php echo xlt("Notes"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($map as $col => $listId) : ?>
                                    <?php
                                    $val   = $info[$col] ?? $info['sdoh_' . $col] ?? '';
                                    $notes = $info[$col . '_notes'] ?? $info['sdoh_' . $col . '_notes'] ?? '';
                                    $valText = ($val === '' ? xlt('Not recorded') : hs_lo_title($listId, $val));
                                    $badge = hs_badge_class($val);
                                    ?>
                                    <tr>
                                        <td><?php echo xlt(ucwords(str_replace('_', ' ', $col))); ?></td>
                                        <td>
                                            <?php if ($val === '' || $val === null) : ?>
                                                <span class="text-muted"><?php echo text($valText); ?></span>
                                            <?php else : ?>
                                                <span class="badge <?php echo attr($badge); ?>"><?php echo text($valText); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $notes !== '' ? text(hs_clip($notes, 90)) : "<span class='text-muted'>" . xlt("None") . "</span>"; ?></td>
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
