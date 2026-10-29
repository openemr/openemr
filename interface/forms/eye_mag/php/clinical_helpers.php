<?php

/**
 * Clinical display, preferences and patient materials for the Eye Form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016- Raymond Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag\Clinical;

use CategoryTree;
use Document;
use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\EyeFormData;
use OpenEMR\Forms\EyeMag\PatientHandout;
use OpenEMR\Forms\EyeMag\PatientMaterials;

/**
 * Render historical Neuro findings without editable current-exam controls.
 * @param array<string,mixed> $exam
 */
function eye_mag_neuro_prior(array $exam, string $motilityDisplay): string
{
    $motilityDisplay = $motilityDisplay === 'H' ? 'H' : 'X';
    /** Read a prior exam field as text without accepting nested or object values. */
    $value = (static fn(string $field): string => is_scalar($exam[$field] ?? '') ? EyeFormData::text($exam[$field] ?? '') : '');
    /** Render a disabled prior-exam input with its field name and value escaped. */
    $input = (static fn(string $field): string => '<input disabled type="text" id="PRIOR_' . attr($field) . '" value="' . attr($value($field)) . '">');
    /** Render multiline alternate-cover-test findings as escaped, read-only prior values. */
    $actInput = (static fn(string $field): string => '<textarea disabled rows="2" id="PRIOR_' . attr($field) . '">' . text($value($field)) . '</textarea>');
    /** Restore a prior checkbox only for the stored checked values. */
    $checked = (static fn(string $field): string => in_array($value($field), ['1', 'on'], true) ? ' checked' : '');
    /** Build one SVG stroke for the prior motility chart. */
    $line = (static fn(float $x1, float $y1, float $x2, float $y2): string => sprintf('<line x1="%.3f" y1="%.3f" x2="%.3f" y2="%.3f"/>', $x1, $y1, $x2, $y2));
    /** Render one eye's prior motility diagram using the selected notation and recorded limitations. */
    $chart = static function (string $eye) use ($motilityDisplay, $value, $line): string {
        $fields = $eye === 'OD'
            ? ['MOTILITY_RS', 'MOTILITY_RI', 'MOTILITY_RR', 'MOTILITY_RL', 'MOTILITY_RRSO', 'MOTILITY_RRIO', 'MOTILITY_RLSO', 'MOTILITY_RLIO']
            : ['MOTILITY_LS', 'MOTILITY_LI', 'MOTILITY_LR', 'MOTILITY_LL', 'MOTILITY_LRSO', 'MOTILITY_LRIO', 'MOTILITY_LLSO', 'MOTILITY_LLIO'];
        $html = '<svg viewBox="0 0 74 68" role="img" data-display="' . $motilityDisplay . '" aria-label="' . attr($eye . ' ' . $motilityDisplay) . '">';
        if ($motilityDisplay === 'H') {
            $html .= $line(8, 5, 8, 57) . $line(66, 5, 66, 57) . $line(8, 31, 66, 31);
            $rays = [[8, 31, 0, -1, 4, 22, 5], [37, 31, -1, 0, 2, 16, 4], [8, 31, 0, 1, 5, 22, 5],
                [66, 31, 0, -1, 6, 22, 5], [37, 31, 1, 0, 3, 16, 4], [66, 31, 0, 1, 7, 22, 5]];
        } else {
            $d = 0.70710678;
            $rays = [];
            foreach ([[0, -1], [0, 1], [-1, 0], [1, 0], [-$d, -$d], [-$d, $d], [$d, -$d], [$d, $d]] as $index => $direction) {
                [$dx, $dy] = $direction;
                $html .= $line(37, 34, 37 + $dx * 30, 34 + $dy * 30);
                $rays[] = [37, 34, $dx, $dy, $index, 25, 5];
            }
        }
        foreach ($rays as [$cx, $cy, $dx, $dy, $index, $outer, $spacing]) {
            $field = $fields[$index];
            $grade = max(0, min(4, EyeFormData::integer($value($field))));
            $html .= '<g data-field="' . attr($field) . '" data-grade="' . $grade . '">';
            for ($tick = 0; $tick < $grade; ++$tick) {
                $distance = $outer - $tick * $spacing;
                $x = $cx + $dx * $distance;
                $y = $cy + $dy * $distance;
                $html .= $line($x + $dy * 4, $y - $dx * 4, $x - $dy * 4, $y + $dx * 4);
            }
            $html .= '</g>';
        }
        return $html . '</svg>';
    };
    $actViews = ['SCDIST' => xlt('scDist{{without correction distance}}'), 'CCDIST' => xlt('ccDist{{with correction distance}}'),
        'SCNEAR' => xlt('scNear{{without correction near}}'), 'CCNEAR' => xlt('ccNear{{with correction at near}}')];
    $hasActFindings = false;
    foreach (array_keys($actViews) as $view) {
        for ($position = 1; $position <= 11; ++$position) {
            $hasActFindings = $hasActFindings || trim($value('ACT' . $position . $view)) !== '';
        }
    }
    ob_start();
    ?>
    <div class="eye-neuro-prior">
      <div class="eye-neuro-prior-layout">
        <div class="eye-neuro-prior-left">
          <section class="eye-neuro-prior-card borderShadow eye-neuro-prior-stereo">
            <div class="eye-neuro-prior-heading">
              <strong><?php echo xlt('Stereopsis'); ?>:</strong>
              <label><?php echo xlt('Unable'); ?> <input disabled type="checkbox"<?php echo $value('STEREO_FLY') === 'U/A' ? ' checked' : ''; ?>></label>
            </div>
            <table>
              <tr><th><?php echo xlt('Fly'); ?>:</th><td><span class="eye-neuro-prior-segments">
                <span class="<?php echo $value('STEREO_FLY') === 'Yes' ? 'is-selected' : ''; ?>"><?php echo xlt('Yes'); ?></span><span class="<?php echo $value('STEREO_FLY') === 'No' ? 'is-selected' : ''; ?>"><?php echo xlt('No'); ?></span>
              </span></td></tr>
              <tr><th><label for="PRIOR_STEREO_ANIMALS"><?php echo xlt('Animals'); ?>:</label></th><td><span class="eye-neuro-prior-score"><?php echo $input('STEREO_ANIMALS'); ?><span>/3</span></span></td></tr>
              <tr><th><label for="PRIOR_STEREO_CIRCLES"><?php echo xlt('Circles'); ?>:</label></th><td><span class="eye-neuro-prior-score"><?php echo $input('STEREO_CIRCLES'); ?><span>/9</span></span></td></tr>
              <tr><td colspan="2" class="eye-neuro-prior-fixation"><?php echo xlt('Fixation Pref'); ?>: <span class="eye-neuro-prior-segments">
                <?php foreach (['OD', 'OS', 'OU', 'Alt'] as $option) { ?>
                  <span class="<?php echo $value('STEREO_FIXATION_PREF') === $option ? 'is-selected' : ''; ?>"><?php echo text($option); ?></span>
                <?php } ?>
              </span></td></tr>
              <?php if (trim($value('STEREOPSIS')) !== '') { ?>
                <tr><th><label for="PRIOR_STEREOPSIS"><?php echo xlt('Previous/Other'); ?>:</label></th><td><?php echo $input('STEREOPSIS'); ?></td></tr>
              <?php } ?>
            </table>
          </section>
          <section class="eye-neuro-prior-card borderShadow eye-neuro-prior-motility">
            <div class="eye-neuro-prior-heading"><strong><?php echo xlt('Motility'); ?>:</strong>
              <span class="eye-neuro-prior-segments"><span class="<?php echo $motilityDisplay === 'X' ? 'is-selected' : ''; ?>">X</span><span class="<?php echo $motilityDisplay === 'H' ? 'is-selected' : ''; ?>">H</span></span>
              <label><?php echo xlt('Normal'); ?> <input disabled type="checkbox"<?php echo $checked('MOTILITYNORMAL'); ?>></label>
            </div>
            <div class="eye-neuro-prior-charts">
              <div><strong><?php echo xlt('OD{{right eye}}'); ?></strong><?php echo $chart('OD'); ?></div>
              <div><strong><?php echo xlt('OS{{left eye}}'); ?></strong><?php echo $chart('OS'); ?></div>
            </div>
          </section>
        </div>
        <section class="eye-neuro-prior-card borderShadow eye-neuro-prior-act">
          <div class="eye-neuro-prior-heading"><strong><?php echo xlt('Alternate Cover Test'); ?>:</strong>
            <label><?php echo xlt('Ortho{{orthophoric}}'); ?> <input disabled type="checkbox"<?php echo $checked('ACT'); ?>></label>
          </div>
          <details class="eye-neuro-prior-act-details"<?php echo $hasActFindings ? ' open' : ''; ?>>
            <summary><?php echo xlt('Measurements'); ?></summary>
            <?php foreach ($actViews as $view => $title) { ?>
              <div class="eye-neuro-prior-act-view"><strong><?php echo $title; ?></strong>
                <div class="eye-neuro-prior-gaze-labels"><span><?php echo xlt('R{{right}}'); ?></span><span><?php echo xlt('L{{left}}'); ?></span></div>
                <div class="eye-neuro-prior-act-grid">
                  <?php for ($position = 1; $position <= 9; ++$position) { ?>
                    <span><?php echo $actInput('ACT' . $position . $view); ?></span>
                  <?php } ?>
                </div>
                <div class="eye-neuro-prior-head-tilt">
                  <label><?php echo xlt('Right head tilt'); ?> <?php echo $actInput('ACT10' . $view); ?></label>
                  <label><?php echo xlt('Left head tilt'); ?> <?php echo $actInput('ACT11' . $view); ?></label>
                </div>
              </div>
            <?php } ?>
          </details>
          <table class="eye-neuro-prior-values">
            <colgroup><col class="eye-neuro-prior-label-col"><col><col></colgroup>
            <tr><td></td><th><?php echo xlt('OD{{right eye}}'); ?></th><th><?php echo xlt('OS{{left eye}}'); ?></th></tr>
            <?php foreach ([[xlt('Color'), 'ODCOLOR', 'OSCOLOR'], [xlt('Red Desat{{red desaturation}}'), 'ODREDDESAT', 'OSREDDESAT'], [xlt('Coins'), 'ODCOINS', 'OSCOINS'], [xlt('NPA{{near point of Accommodation}}'), 'ODNPA', 'OSNPA']] as [$label, $right, $left]) { ?>
              <tr><th><label for="PRIOR_<?php echo attr($right); ?>"><?php echo $label; ?>:</label></th><td><?php echo $input($right); ?></td><td><?php echo $input($left); ?></td></tr>
            <?php } ?>
            <tr><th><label for="PRIOR_NPC"><?php echo xlt('NPC{{near point of convergence}}'); ?>:</label></th><td colspan="2"><?php echo $input('NPC'); ?></td></tr>
            <tr class="eye-neuro-prior-amplitudes"><th><?php echo xlt('Amplitudes'); ?>:</th><th><?php echo xlt('Distance'); ?></th><th><?php echo xlt('Near'); ?></th></tr>
            <tr><th><label for="PRIOR_DACCDIST"><?php echo xlt('Divergence'); ?>:</label></th><td><?php echo $input('DACCDIST'); ?></td><td><?php echo $input('DACCNEAR'); ?></td></tr>
            <tr><th><label for="PRIOR_CACCDIST"><?php echo xlt('Convergence'); ?>:</label></th><td><?php echo $input('CACCDIST'); ?></td><td><?php echo $input('CACCNEAR'); ?></td></tr>
            <tr><th><label for="PRIOR_VERTFUSAMPS"><?php echo xlt('Vert. Fusional'); ?>:</label></th><td colspan="2"><?php echo $input('VERTFUSAMPS'); ?></td></tr>
          </table>
        </section>
      </div>
      <div class="eye-neuro-prior-comments"><label for="PRIOR_NEURO_COMMENTS"><strong><?php echo xlt('Comments'); ?>:</strong></label>
        <textarea disabled id="PRIOR_NEURO_COMMENTS"><?php echo text($value('NEURO_COMMENTS')); ?></textarea>
      </div>
    </div>
    <?php
    return ob_get_clean() ?: '';
}

/**
 *  This is currently a floating div top with patient demographics and such.
 *  Used in fullscreen mode at the top.
 *
 *  @param string $pid patient_id
 *  @param string $encounter is the current encounter number
 *  @return void
 */
/**
 * Resolve the configured photo category without translating or normalizing its name.
 * @return array<string,mixed>
 */
function eye_mag_patient_photo(mixed $pid): array
{
    $categoryName = OEGlobalsBag::getInstance()->getString('patient_photo_category_name');
    if (EyeFormData::integer($pid) <= 0 || trim($categoryName) === '') {
        return [];
    }

    return EyeFormData::row(QueryUtils::querySingleRow(
        "SELECT doc.id, doc.mimetype FROM documents doc
         JOIN categories_to_documents link ON link.document_id = doc.id
         JOIN categories category ON category.id = link.category_id
         WHERE category.name = ? AND doc.foreign_id = ? AND doc.deleted = 0
         ORDER BY doc.id DESC LIMIT 1",
        [$categoryName, EyeFormData::integer($pid)]
    ));
}

/**
 * Display a compact acuity history table and a clinically ordered trend chart.
 * The table retains exact entered values; logMAR is used only for chart positioning.
 * @param array<array<string,mixed>> $priors
 */
function display_VisualAcuitiesModern(mixed $pid = 0, array $priors = [], string $dated = ''): string
{

    $typeDefinitions = [
        'BC' => ['label' => xlt('BC{{Best corrected visual acuity}}'), 'od' => '', 'os' => ''],
        'SC' => ['label' => xlt('SC{{Acuity without correction}}'), 'od' => 'SCODVA', 'os' => 'SCOSVA'],
        'CC' => ['label' => xlt('Current Rx'), 'od' => 'CCODVA', 'os' => 'CCOSVA'],
        'PH' => ['label' => xlt('PH{{Pinhole acuity}}'), 'od' => 'PHODVA', 'os' => 'PHOSVA'],
        'AR' => ['label' => xlt('AR{{Autorefraction acuity}}'), 'od' => 'ARODVA', 'os' => 'AROSVA'],
        'MR' => ['label' => xlt('MR{{Manifest Refraction acuity}}'), 'od' => 'MRODVA', 'os' => 'MROSVA'],
        'CR' => ['label' => xlt('CR{{Cycloplegic Refraction acuity}}'), 'od' => 'CRODVA', 'os' => 'CROSVA'],
        'CTL' => ['label' => xlt('CTL{{Contact Lens acuity}}'), 'od' => 'CTLODVA', 'os' => 'CTLOSVA'],
    ];
    $correctedTypes = ['CC', 'AR', 'MR', 'CR', 'CTL'];

    /** Convert documented acuity to a logMAR estimate, retaining unknown values as gaps. */
    $toLogMar = static function (mixed $raw): ?float {
        $value = trim(EyeFormData::text($raw));
        if ($value === '') {
            return null;
        }

        $normalized = strtoupper($value);
        if (preg_match('/\b(?:NLP|NO\s+LIGHT\s+PERCEPTION)\b/', $normalized)) {
            return 4.0;
        }
        if (preg_match('/\b(?:LP|PL|LIGHT\s+PERCEPTION)(?:\s*(?:WITH|W\/|\+)\s*(?:PROJECTION|PR))?\b/', $normalized)) {
            // Projection is clinically useful metadata, but it is not a separate measured acuity.
            return 3.0;
        }

        /** Normalize recorded test distances to feet for low-vision acuity estimates. */
        $distanceToFeet = (static fn(float $distance, string $unit): float => str_starts_with($unit, 'M') ? $distance * 3.28084 : $distance);
        if (preg_match('/\b(?:CF|COUNT(?:ING)?\s+FINGERS?)\s*(?:AT|@)?\s*(\d+(?:\.\d+)?)?\s*(FT|FEET|FOOT|M|METER|METERS|METRE|METRES)?\b/', $normalized, $matches)) {
            if (isset($matches[1]) && $matches[1] !== '' && $matches[1] !== '0') {
                $distanceFeet = $distanceToFeet(EyeFormData::number($matches[1]), $matches[2] ?? 'FT');
                return $distanceFeet > 0 ? round(log10(200 / $distanceFeet), 3) : 2.0;
            }
            return 2.0;
        }
        if (preg_match('/\b(?:HM|HAND\s+MOTION|HAND\s+MOVEMENTS?)\s*(?:AT|@)?\s*(\d+(?:\.\d+)?)?\s*(FT|FEET|FOOT|M|METER|METERS|METRE|METRES)?\b/', $normalized, $matches)) {
            if (isset($matches[1]) && $matches[1] !== '' && $matches[1] !== '0') {
                $distanceFeet = $distanceToFeet(EyeFormData::number($matches[1]), $matches[2] ?? 'FT');
                return $distanceFeet > 0 ? round(log10(1000 / $distanceFeet), 3) : 2.3;
            }
            return 2.3;
        }

        $numerator = 20.0;
        $denominator = 0.0;
        if (preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $value, $matches)) {
            $numerator = EyeFormData::number($matches[1]);
            $denominator = EyeFormData::number($matches[2]);
        } elseif (preg_match('/^\s*(\d+(?:\.\d+)?)/', $value, $matches)) {
            $denominator = EyeFormData::number($matches[1]);
        }
        if ($numerator <= 0 || $denominator <= 0) {
            return null;
        }
        $logMar = log10($denominator / $numerator);
        if (preg_match('/([+-])\s*(\d+)/', $value, $modifier)) {
            $letterAdjustment = min(5, EyeFormData::integer($modifier[2])) * 0.02;
            $logMar += $modifier[1] === '+' ? -$letterAdjustment : $letterAdjustment;
        }
        return round($logMar, 3);
    };

    $rows = [];
    $hasType = array_fill_keys(array_keys($typeDefinitions), false);
    $history = $priors;
    /** Order prior acuity records chronologically for the history table and chart. */
    usort($history, static fn(array $a, array $b): int => strtotime(EyeFormData::text($a['visit_date'] ?? '')) <=> strtotime(EyeFormData::text($b['visit_date'] ?? '')));
    $currentVisitTimestamp = strtotime(EyeFormData::text($dated));

    foreach ($history as $prior) {
        $visitTimestamp = strtotime(EyeFormData::text($prior['visit_date'] ?? ''));
        if (!$visitTimestamp || ($currentVisitTimestamp && $visitTimestamp > $currentVisitTimestamp)) {
            continue;
        }

        $wear = QueryUtils::querySingleRow(
            "SELECT ODVA, OSVA FROM form_eye_mag_wearing WHERE PID=? AND FORM_ID=? AND RX_NUMBER=1",
            [$pid, $prior['form_id']]
        );
        $prior['CCODVA'] = $wear['ODVA'] ?? '';
        $prior['CCOSVA'] = $wear['OSVA'] ?? '';
        $values = [];
        foreach ($typeDefinitions as $type => $definition) {
            if ($type === 'BC') {
                continue;
            }
            $values[$type] = [
                'OD' => trim(EyeFormData::text($prior[$definition['od']] ?? '')),
                'OS' => trim(EyeFormData::text($prior[$definition['os']] ?? '')),
            ];
            if ($values[$type]['OD'] !== '' || $values[$type]['OS'] !== '') {
                $hasType[$type] = true;
            }
        }

        $values['BC'] = ['OD' => '', 'OS' => ''];
        foreach (['OD', 'OS'] as $eye) {
            $bestRaw = '';
            $bestLogMar = null;
            foreach ($correctedTypes as $type) {
                $candidateRaw = $values[$type][$eye];
                if ($candidateRaw === '') {
                    continue;
                }
                if ($bestRaw === '') {
                    $bestRaw = $candidateRaw;
                }
                $candidateLogMar = $toLogMar($candidateRaw);
                if ($candidateLogMar !== null && ($bestLogMar === null || $candidateLogMar < $bestLogMar)) {
                    $bestRaw = $candidateRaw;
                    $bestLogMar = $candidateLogMar;
                }
            }
            $values['BC'][$eye] = $bestRaw;
            if ($bestRaw !== '') {
                $hasType['BC'] = true;
            }
        }

        $rows[] = [
            'date' => date('Y-m-d', $visitTimestamp),
            'display_date' => EyeFormData::text($prior['visit_date'] ?? ''),
            'values' => $values,
        ];
    }

    $visibleTableTypes = array_values(array_filter(
        array_keys($typeDefinitions),
        /** Retain only acuity types with documented values in the selected history. */
        static fn($type): bool => $hasType[$type]
    ));
    $eyeColors = ['OD' => '#2563eb', 'OS' => '#ea580c'];
    $dashPatterns = [
        'BC' => [], 'SC' => [2, 4], 'CC' => [8, 4], 'PH' => [3, 3],
        'AR' => [12, 4], 'MR' => [10, 3, 2, 3], 'CR' => [6, 3], 'CTL' => [1, 3],
    ];
    $chartDatasets = [];
    foreach ($visibleTableTypes as $type) {
        foreach (['OD', 'OS'] as $eye) {
            $points = [];
            $hasPlottableValue = false;
            foreach ($rows as $row) {
                $rawValue = $row['values'][$type][$eye] ?? '';
                $logMar = $toLogMar($rawValue);
                if ($logMar === null) {
                    $points[] = null;
                } else {
                    $hasPlottableValue = true;
                    // Higher logMAR means poorer vision; keep it above better vision on this chart.
                    $points[] = $logMar;
                }
            }
            if (!$hasPlottableValue) {
                continue;
            }
            $chartDatasets[] = [
                'label' => $type . ' ' . $eye,
                'data' => $points,
                'exactValues' => array_column(array_map(
                    /** Keep the original acuity text beside its plotted numeric estimate. */
                    static fn($row): array => ['value' => $row['values'][$type][$eye] ?? ''],
                    $rows
                ), 'value'),
                'borderColor' => $eyeColors[$eye],
                'backgroundColor' => $eyeColors[$eye],
                'borderDash' => $dashPatterns[$type],
                'borderWidth' => $type === 'BC' ? 3 : 2,
                'hidden' => $type !== 'BC',
                'pointRadius' => $type === 'BC' ? 4 : 3,
                'pointHoverRadius' => 6,
                'spanGaps' => true,
                'tension' => 0.25,
            ];
        }
    }
    $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
    $chartLabelsJson = json_encode(array_column($rows, 'date'), $jsonFlags) ?: '[]';
    $chartDatasetsJson = json_encode($chartDatasets, $jsonFlags) ?: '[]';

    ob_start();
    ?>
    <div class="vahx-header">
        <h5 class="m-0"><?php echo xlt('Visual Acuity History'); ?></h5>
        <span class="closeButton fas fa-times" id="Close_VAHx" name="Close_VAHx"></span>
    </div>
    <?php if ($rows === []) { ?>
        <div class="alert alert-info mb-0"><?php echo xlt('Visual acuity history will appear after a prior visit is available.'); ?></div>
    <?php } else { ?>
        <div class="vahx-layout">
            <section class="vahx-table-wrap" aria-label="<?php echo xla('Visual acuity history table'); ?>">
                <div class="vahx-table-scroll">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="vahx-date"><?php echo xlt('Date'); ?></th>
                                <?php foreach ($visibleTableTypes as $type) { ?>
                                    <th colspan="2"><?php echo text($typeDefinitions[$type]['label']); ?></th>
                                <?php } ?>
                            </tr>
                            <tr>
                                <?php foreach ($visibleTableTypes as $type) { ?>
                                    <th><?php echo xlt('OD{{Right eye}}'); ?></th>
                                    <th><?php echo xlt('OS{{Left eye}}'); ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($rows) as $row) { ?>
                                <tr>
                                    <td class="vahx-date"><?php echo text($row['display_date']); ?></td>
                                    <?php foreach ($visibleTableTypes as $type) { ?>
                                        <td><?php echo text(($row['values'][$type]['OD'] ?? '') ?: '—'); ?></td>
                                        <td><?php echo text(($row['values'][$type]['OS'] ?? '') ?: '—'); ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="vahx-chart-panel" aria-label="<?php echo xla('Visual acuity trend chart'); ?>">
                <div class="vahx-chart-wrap"><canvas id="canvas_VA"></canvas></div>
                <?php if ($chartDatasets !== []) { ?>
                    <div class="vahx-series-title"><?php echo xlt('Series — select additional measurements to compare'); ?></div>
                    <div class="vahx-series-controls" id="VAHX_series_controls"></div>
                <?php } else { ?>
                    <div class="text-muted mt-2"><?php echo xlt('No numeric Snellen acuities are available to chart.'); ?></div>
                <?php } ?>
            </section>
        </div>
        <?php if ($chartDatasets !== []) { ?>
            <script>
                (function () {
                    var canvas = document.getElementById('canvas_VA');
                    if (!canvas || typeof Chart === 'undefined') return;
                    if (window.eyeMagVaChart) window.eyeMagVaChart.destroy();
                    var chartTextColor = window.getComputedStyle(document.body).color;
                    var datasets = <?php echo $chartDatasetsJson; ?>;
                    window.eyeMagVaChart = new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: <?php echo $chartLabelsJson; ?>,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {mode: 'nearest', intersect: false},
                            plugins: {
                                legend: {display: false},
                                title: {
                                    display: true,
                                    color: chartTextColor,
                                    text: <?php echo xlj('Best Corrected Visual Acuity Over Time'); ?>
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var exactValue = context.dataset.exactValues[context.dataIndex] || '';
                                            return context.dataset.label + ': ' + exactValue;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {color: chartTextColor, maxRotation: 45, minRotation: 0},
                                    grid: {color: 'rgba(127,127,127,0.16)'},
                                    title: {display: true, color: chartTextColor, text: <?php echo xlj('Visit Date'); ?>}
                                },
                                y: {
                                    suggestedMin: -0.1,
                                    suggestedMax: 1.3,
                                    ticks: {
                                        color: chartTextColor,
                                        callback: function (value) {
                                            var logMar = Number(value);
                                            if (logMar >= 3.5) return 'NLP';
                                            if (logMar >= 2.65) return 'LP';
                                            if (logMar >= 2.15) return 'HM';
                                            if (logMar >= 1.75) return 'CF';
                                            var denominator = Math.max(10, Math.round(20 * Math.pow(10, logMar)));
                                            return '20/' + denominator;
                                        }
                                    },
                                    grid: {color: 'rgba(127,127,127,0.16)'},
                                    title: {display: true, color: chartTextColor, text: <?php echo xlj('Visual Acuity (worse toward top)'); ?>}
                                }
                            }
                        }
                    });

                    var controls = document.getElementById('VAHX_series_controls');
                    datasets.forEach(function (dataset, index) {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'vahx-series-toggle' + (dataset.hidden ? '' : ' active');
                        button.textContent = dataset.label;
                        button.style.setProperty('--vahx-color', dataset.borderColor);
                        button.setAttribute('aria-pressed', dataset.hidden ? 'false' : 'true');
                        button.addEventListener('click', function () {
                            var visible = window.eyeMagVaChart.isDatasetVisible(index);
                            window.eyeMagVaChart.setDatasetVisibility(index, !visible);
                            button.classList.toggle('active', !visible);
                            button.setAttribute('aria-pressed', !visible ? 'true' : 'false');
                            window.eyeMagVaChart.update();
                        });
                        controls.appendChild(button);
                    });
                })();
            </script>
        <?php } ?>
    <?php } ?>
    <?php
    return ob_get_clean() ?: '';
}

/**
 * Return the stable dilation fields with their default labels and stored medication values.
 *
 * @return array<string,array{string,string}>
 */
function eye_mag_dilation_med_defaults(): array
{
    return [
        'CYCLOMYDRIL' => ['CycloMydril', 'Cyclomydril'],
        'TROPICAMIDE' => ['Tropic 1%', 'Tropicamide 1%'],
        'NEO25' => ['Neo 2.5%', 'Neosynephrine 2.5%'],
        'NEO10' => ['Neo 10%', 'Neosynephrine 10%'],
        'CYCLOGYL' => ['Cyclo 1%', 'Cyclopentolate 1%'],
        'ATROPINE' => ['Atropine 1%', 'Atropine 1%'],
    ];
}

/**
 * Return the practice-wide document category used by Communication Engine handouts.
 *
 * OpenEMR's document tree is a nested set, so use its CategoryTree implementation
 * rather than inserting category rows directly. The advisory lock prevents two
 * simultaneous first loads from attempting to create the same nodes.
 */
function eye_mag_ensure_handout_category(): int
{
    $handout = QueryUtils::querySingleRow(
        "SELECT c.id
           FROM categories c
           JOIN categories p ON p.id = c.parent
          WHERE c.name = 'Handouts' AND p.name = 'Patient Education'
          ORDER BY c.id
          LIMIT 1"
    );
    if (EyeFormData::integer($handout['id'] ?? 0) > 0) {
        return EyeFormData::integer($handout['id'] ?? 0);
    }

    $lockName = 'eye_mag_patient_education_handouts';
    $lock = QueryUtils::querySingleRow("SELECT GET_LOCK(?, 5) AS acquired", [$lockName]);
    if (EyeFormData::integer($lock['acquired'] ?? 0) !== 1) {
        return 0;
    }

    try {
        $handout = QueryUtils::querySingleRow(
            "SELECT c.id
               FROM categories c
               JOIN categories p ON p.id = c.parent
              WHERE c.name = 'Handouts' AND p.name = 'Patient Education'
              ORDER BY c.id
              LIMIT 1"
        );
        if (EyeFormData::integer($handout['id'] ?? 0) > 0) {
            return EyeFormData::integer($handout['id'] ?? 0);
        }

        require_once(OEGlobalsBag::getInstance()->getString('srcdir') . '/classes/Tree.class.php');
        require_once(OEGlobalsBag::getInstance()->getString('srcdir') . '/classes/CategoryTree.class.php');

        $tree = new CategoryTree(1);
        $patientEducation = QueryUtils::querySingleRow(
            "SELECT id FROM categories WHERE parent = 1 AND name = 'Patient Education' ORDER BY id LIMIT 1"
        );
        $patientEducationId = EyeFormData::integer($patientEducation['id'] ?? 0) > 0
            ? EyeFormData::integer($patientEducation['id'] ?? 0)
            : EyeFormData::integer($tree->add_node(1, 'Patient Education'));

        return EyeFormData::integer($tree->add_node($patientEducationId, 'Handouts'));
    } finally {
        QueryUtils::querySingleRow("SELECT RELEASE_LOCK(?) AS released", [$lockName]);
    }
}

/**
 * Ordered, unique IDs; NULL means an encounter still uses the legacy single ID.
 * @param array<array-key,mixed> $materials
 * @return list<int>
 */
function eye_mag_material_ids(array $materials): array
{
    $ids = isset($materials['handout_document_ids'])
        ? json_decode(EyeFormData::text($materials['handout_document_ids']), true)
        : [$materials['handout_document_id'] ?? 0];
    $result = [];
    foreach (is_array($ids) ? $ids : [] as $id) {
        if ((is_int($id) || is_string($id)) && ctype_digit(EyeFormData::text($id)) && EyeFormData::integer($id) > 0) {
            $result[EyeFormData::integer($id)] = EyeFormData::integer($id);
        }
    }
    return array_values($result);
}

/**
 * Only current-patient or practice-wide PDFs in Patient Education / Handouts.
 * @return array<string,mixed>
 */
function eye_mag_material_handout(int $documentId, int $pid): array
{
    return EyeFormData::row(QueryUtils::querySingleRow(
        "SELECT d.id, d.name, d.url, d.foreign_id
           FROM documents d
           JOIN categories_to_documents ctd ON ctd.document_id = d.id
           JOIN categories c ON c.id = ctd.category_id
      LEFT JOIN categories p ON p.id = c.parent
          WHERE d.id = ? AND d.deleted = 0
            AND (d.mimetype = 'application/pdf' OR LOWER(d.url) LIKE '%.pdf')
            AND (d.foreign_id = ? OR d.foreign_id IN (0, -1))
            AND ((c.name = 'Handouts' AND p.name = 'Patient Education')
                 OR c.name = 'Patient Education/Handouts')
          LIMIT 1",
        [$documentId, $pid]
    ));
}

/**
 * Save the complete selection, never replace it with the last PDF added.
 * @param array<array-key,mixed> $posted
 */
function eye_mag_save_materials(int $formId, int $pid, array $posted): void
{

    $previous = QueryUtils::querySingleRow(
        "SELECT handout_document_id, handout_document_ids FROM form_eye_mag_materials WHERE form_id=? AND pid=?",
        [$formId, $pid]
    ) ?: [];
    $oldIds = eye_mag_material_ids($previous);
    if (isset($posted['MATERIALS_HANDOUT_IDS']) && is_string($posted['MATERIALS_HANDOUT_IDS'])
        && is_array(json_decode($posted['MATERIALS_HANDOUT_IDS'], true))) {
        $ids = eye_mag_material_ids(['handout_document_ids' => $posted['MATERIALS_HANDOUT_IDS']]);
    } elseif (isset($previous['handout_document_ids']) || isset($posted['MATERIALS_HANDOUT_IDS'])) {
        // An old browser tab or malformed payload must not collapse a saved list.
        $ids = $oldIds;
    } else {
        $ids = eye_mag_material_ids(['handout_document_id' => $posted['MATERIALS_HANDOUT_ID'] ?? 0]);
    }
    $ids = array_values(array_filter($ids,
        // Retain unavailable existing selections so the user can see/remove them;
        // both viewing and report generation independently recheck document access.
        /** Retain existing handout selections or newly selected documents the patient may access. */
        static fn(int $id): bool => in_array($id, $oldIds, true) || eye_mag_material_handout($id, $pid) !== []));
    QueryUtils::sqlStatementThrowException(
        "REPLACE INTO form_eye_mag_materials (form_id,pid,content,include_text,handout_document_id,handout_document_ids,updated_at)
         VALUES (?,?,?,?,?,?,NOW())",
        [$formId, $pid, EyeFormData::text($posted['MATERIALS_TO_PRINT'] ?? ''),
            EyeFormData::integer($posted['MATERIALS_TEXT_INCLUDED'] ?? 0) === 1 ? 1 : 0,
            $ids[0] ?? null, json_encode($ids)]
    );
}

/**
 * Read once per report request. The acknowledgment, embedded original and
 * appended pages must all use the same bytes, even if the master is replaced.
 * The resulting report is the snapshot; this does not freeze the template at Add.
 */
function eye_mag_materials_for_report(int $formId, int $pid): PatientMaterials
{
    /** @var array<string,PatientMaterials> $snapshots */
    static $snapshots = [];
    $key = $pid . ':' . $formId;
    if (isset($snapshots[$key])) {
        return $snapshots[$key];
    }

    $materials = QueryUtils::querySingleRow(
        "SELECT content, include_text, handout_document_id, handout_document_ids FROM form_eye_mag_materials WHERE form_id=? AND pid=?",
        [$formId, $pid]
    ) ?: [];
    $handouts = [];
    foreach (eye_mag_material_ids($materials) as $documentId) {
        $name = '';
        $pdfData = '';
        $handout = eye_mag_material_handout($documentId, $pid);
        if ($handout) {
            $name = trim(EyeFormData::text($handout['name'] ?? '')) ?: basename(EyeFormData::text($handout['url']));
            try {
                // Document handles encrypted and non-filesystem storage as well.
                $document = new Document($documentId);
                $bytes = $document->get_data();
                if (is_string($bytes) && str_starts_with($bytes, '%PDF-')) {
                    $pdfData = $bytes;
                }
            } catch (\BadMethodCallException | \RuntimeException) {
                // The report displays an explicit unavailable warning, not a receipt.
            }
        }
        $handouts[] = new PatientHandout($documentId, $name, $pdfData);
    }
    return $snapshots[$key] = new PatientMaterials(
        EyeFormData::text($materials['content'] ?? ''),
        EyeFormData::integer($materials['include_text'] ?? 0) === 1,
        $handouts,
    );
}

/**
 * Embed exact originals, including fillable fields, without touching core reports.
 */
function eye_mag_embed_materials(Mpdf $pdf, int $formId, int $pid, PatientMaterials $materials): bool
{
    $files = is_array($pdf->associatedFiles) ? $pdf->associatedFiles : [];
    $originals = [];
    if ($materials->includeText && trim($materials->content) !== '') {
        $originals[] = ['name' => 'patient-instructions-' . $pid . '-' . $formId . '.txt',
            'content' => $materials->content, 'mime' => 'text/plain',
            'description' => xl('Patient instructions')];
    }
    foreach ($materials->handouts as $handout) {
        if ($handout->pdfData !== '') {
            // Document ID also distinguishes handouts with identical display names.
            $originals[] = ['name' => 'patient-handout-' . $pid . '-' . $formId . '-' . $handout->documentId . '.pdf',
                'content' => $handout->pdfData, 'mime' => 'application/pdf',
                'description' => $handout->name];
        }
    }
    foreach ($originals as $original) {
        $duplicate = false;
        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }
            if (($file['name'] ?? '') === $original['name'] && ($file['content'] ?? null) === $original['content']) {
                $duplicate = true;
                break;
            }
        }
        if (!$duplicate) {
            $files[] = $original + ['AFRelationship' => 'Supplement'];
        }
    }
    $pdf->SetAssociatedFiles($files);
    return true;
}

/** Stored copies are versions, never replacements of a previously issued report. */
function eye_mag_report_filename(int $pid, int $encounter): string
{
    return 'Eye_Report_' . $pid . '_' . $encounter . '_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.pdf';
}

/** Append visible pages where possible; the embedded original remains authoritative. */
function eye_mag_append_materials_handout_pdf(Mpdf $pdf, int $formId, int $pid): bool
{
    $materials = eye_mag_materials_for_report($formId, $pid);
    eye_mag_embed_materials($pdf, $formId, $pid, $materials);
    if ($materials->handouts === []) {
        return false;
    }
    $allAppended = true;
    foreach ($materials->handouts as $handout) {
        if ($handout->pdfData === '' || !eye_mag_append_handout_pages($pdf, $handout->pdfData)) {
            $allAppended = false;
        }
    }
    return $allAppended;
}

/** Isolate each import so an unsupported PDF cannot suppress later handouts. */
function eye_mag_append_handout_pages(Mpdf $pdf, string $bytes): bool
{
    $temporaryFile = null;
    try {
        $temporaryFile = tempnam(OEGlobalsBag::getInstance()->getString('temporary_files_dir'), 'eye_handout_');
        if ($temporaryFile === false || file_put_contents($temporaryFile, $bytes) === false) {
            return false;
        }
        $pageCount = $pdf->setSourceFile($temporaryFile);
        for ($page = 1; $page <= $pageCount; ++$page) {
            $pdf->AddPage();
            $template = $pdf->importPage($page);
            $pdf->useTemplate($template, ['adjustPageSize' => true]);
        }
        return $pageCount > 0;
    } catch (\setasign\Fpdi\FpdiException | \RuntimeException | \InvalidArgumentException | \BadMethodCallException) {
        // Some encrypted/interactive PDFs cannot be imported by FPDI. Their
        // exact original is still in the report's Attachments panel.
        return false;
    } finally {
        if ($temporaryFile && is_file($temporaryFile)) {
            unlink($temporaryFile);
        }
    }
}

/**
 * Return the six fixed dilation slots with their practice-defined labels.
 * @return list<array{field:string,title:string,stored_value:string}>
 */
function eye_mag_dilation_med_options(): array
{

    $listId = 'Eye_Dilation_Meds';
    $defaults = eye_mag_dilation_med_defaults();


    $titles = [];
    $result = QueryUtils::fetchRecords(
        "SELECT option_id, title, notes FROM list_options
          WHERE list_id = ? AND activity = 1
          ORDER BY seq, option_id",
        [$listId]
    );
    foreach ($result as $row) {
        $field = strtoupper(trim(EyeFormData::text($row['option_id'] ?? '')));
        if (!isset($defaults[$field])) {
            $storedValue = trim(EyeFormData::text($row['notes'] ?? ''));
            foreach ($defaults as $defaultField => $default) {
                if ($storedValue === $default[1]) {
                    $field = $defaultField;
                    break;
                }
            }
        }
        if (!isset($defaults[$field]) || isset($titles[$field])) {
            continue;
        }
        $titles[$field] = trim(EyeFormData::text($row['title'] ?? ''));
    }

    $options = [];
    foreach ($defaults as $field => [$defaultTitle, $storedValue]) {
        $options[] = [
            'field' => $field,
            'title' => ($titles[$field] ?? '') !== '' ? $titles[$field] : $defaultTitle,
            'stored_value' => $storedValue,
        ];
    }

    return $options;
}

/**
 * Fixed IOP storage slots and their practice-facing defaults.
 * @return array<string,array{string,string}>
 */
function eye_mag_iop_method_defaults(): array
{
    return [
        'AP' => ['AP', 'Applanation'],
        'TPN' => ['TP', 'Tonopen or other handheld tonometer'],
        'FT' => ['FT', 'Finger tension'],
        'POST' => ['Dil', 'Dilated IOP'],
    ];
}

/**
 * Return every fixed IOP slot, including slots hidden by the practice.
 * @return array<string,array{method:string,title:string,description:string,active:bool}>
 */
function eye_mag_iop_method_options(): array
{
    $defaults = eye_mag_iop_method_defaults();


    $stored = [];
    $result = QueryUtils::fetchRecords(
        "SELECT option_id, title, activity FROM list_options
          WHERE list_id = ?
          ORDER BY seq, option_id",
        ['Eye_IOP_Methods']
    );
    foreach ($result as $row) {
        $method = strtoupper(trim(EyeFormData::text($row['option_id'] ?? '')));
        if (isset($defaults[$method])) {
            $stored[$method] = $row;
        }
    }

    $options = [];
    foreach ($defaults as $method => [$defaultTitle, $description]) {
        $title = trim(EyeFormData::text($stored[$method]['title'] ?? ''));
        $options[$method] = [
            'method' => $method,
            'title' => $title !== '' ? $title : $defaultTitle,
            'description' => $description,
            'active' => !isset($stored[$method]) || EyeFormData::integer($stored[$method]['activity']) === 1,
        ];
    }

    return $options;
}

/**
 * Return the practice-facing label for each stable IOP storage slot.
 * @return array<string,string>
 */
function eye_mag_iop_method_labels(): array
{
    $labels = [];
    foreach (eye_mag_iop_method_options() as $method => $option) {
        $labels[$method] = $option['title'];
    }
    return $labels;
}

/** Return X or H for the practice-wide motility-chart presentation. */
function eye_mag_motility_display_preference(): string
{

    $selected = QueryUtils::querySingleRow(
        "SELECT option_id FROM list_options
          WHERE list_id = 'Eye_Motility_Display' AND is_default = 1 AND activity = 1
          ORDER BY seq LIMIT 1"
    );
    return strtoupper(EyeFormData::text($selected['option_id'] ?? 'X')) === 'H' ? 'H' : 'X';
}

/** Emit the shared column layout for current-glasses prescription tables. */
function display_wear_colgroup(): void
{
    ?>
    <colgroup>
      <col class="wear-col-eye">
      <col class="wear-col-rx">
      <col class="wear-col-rx">
      <col class="wear-col-rx">
      <col class="wear-col-rx">
      <col class="wear-col-prism">
      <col class="wear-col-vd">
      <col class="wear-col-slab">
      <col class="wear-col-type">
      <col class="wear-col-lens">
    </colgroup>
    <?php
}

/**
 * Render prescription controls while retaining legacy prism fields for saved glasses.
 *
 * @param array<string,mixed> $values
 */
function display_wear_header_cells(mixed $W, array $values): void
{
    $W = EyeFormData::text($W);
    $values = EyeFormData::textRow($values);
    /** Read a normalized prescription field for the wearing-table controls. */
    $field = static fn(string $name): string => EyeFormData::text($values[$name] ?? '');
    ?>
    <td class="wear-column-title wear-prism-title" title="<?php echo xla('Prism amount and direction'); ?>"><?php echo xlt('Prism'); ?>
      <?php foreach (['ODHPD','ODHBASE','ODVPD','ODVBASE','OSHPD','OSHBASE','OSVPD','OSVBASE'] as $legacyPrism) { ?>
        <input type="hidden" id="<?php echo attr($legacyPrism . '_' . $W); ?>" name="<?php echo attr($legacyPrism . '_' . $W); ?>" value="<?php echo attr($field($legacyPrism)); ?>">
      <?php } ?>
    </td>
    <td name="W_wide" class="wear-column-title wear-vd-title" title="<?php echo xla('Vertex Distance'); ?>"><?php echo xlt('VD{{abbreviation for Vertex Distance}}'); ?></td>
    <td name="W_wide" class="wear-column-title wear-slab-title" title="<?php echo xla('Slab Off'); ?>"><?php echo xlt('Slab Off'); ?></td>
    <td rowspan="6" class="right rx-type-cell">
      <span class="rx-type-title"><?php echo xlt('Rx Type{{Type of glasses prescription}}'); ?></span><br>
      <?php
        $rxTypes = [
          '0' => xlt('Single'),
          '1' => xlt('Bifocal'),
          '2' => xlt('Trifocal'),
          '3' => xlt('Prog.{{Progressive lenses}}'),
        ];
        foreach ($rxTypes as $rxValue => $rxLabel) {
            $rxId = ($rxValue === 0 ? 'Single' : ($rxValue === 1 ? 'Bifocal' : ($rxValue === 2 ? 'Trifocal' : 'Progressive'))) . '_' . $W;
            ?>
          <label for="<?php echo attr($rxId); ?>" class="wear-rx-type-option">
            <span><?php echo text($rxLabel); ?></span>
            <input type="radio" value="<?php echo attr((string) $rxValue); ?>" id="<?php echo attr($rxId); ?>" name="RX_TYPE_<?php echo attr($W); ?>" <?php echo $field('RX_TYPE') === (string) $rxValue ? 'checked="checked"' : ''; ?>>
          </label>
        <?php } ?>
    </td>
    <td name="W_wide2" rowspan="6" class="wear-expanded-lens-cell">
      <a href="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/super/edit_list.php?list_id=Eye_Lens_Material" target="RTop"
         title="<?php echo xla('Click here to edit list of available Lens Materials'); ?>" name="Lens_mat">
        <span class="underline"><?php echo xlt('Lens Material'); ?></span> <i class="fa fa-pencil-alt fa-fw"></i>
      </a>
      <div class="wear-lens-material-select">
        <?php echo generate_select_list('LENS_MATERIAL_' . $W, 'Eye_Lens_Material', $field('LENS_MATERIAL'), '', '--Lens Material--', '', 'restoreSession;submit_form();', '', ['style' => 'width:120px']); ?>
      </div>
      <a href="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/super/edit_list.php?list_id=Eye_Lens_Treatments" target="RTop"
         title="<?php echo xla('Click here to edit list of available Lens Treatment Options'); ?>" name="Lens_txs">
        <span class="underline"><?php echo xlt('Lens Treatments'); ?></span> <i class="fa fa-pencil-alt fa-fw"></i>
      </a>
      <div class="wear-lens-treatments"><?php echo generate_lens_treatments($W, $field('LENS_TREATMENTS')); ?></div>
    </td>
    <?php
}

/**
 * Render the selected eye's prism, vertex distance, and slab-off inputs.
 *
 * @param array<string,mixed> $values
 */
function display_wear_distance_cells(mixed $W, string $eye, array $values): void
{
    $W = EyeFormData::text($W);
    $values = EyeFormData::textRow($values);
    $eye = $eye === 'OS' ? 'OS' : 'OD';
    ?>
    <td class="wear-prism-cell"><input type="text" class="prism prism-summary-input" id="<?php echo attr($eye . 'PRISM_' . $W); ?>" name="<?php echo attr($eye . 'PRISM_' . $W); ?>" value="<?php echo attr($values[$eye . 'PRISM'] ?? ''); ?>"></td>
    <td name="W_wide"><input type="text" class="prism" id="<?php echo attr($eye . 'VERTEXDIST_' . $W); ?>" name="<?php echo attr($eye . 'VERTEXDIST_' . $W); ?>" value="<?php echo attr($values[$eye . 'VERTEXDIST'] ?? ''); ?>"></td>
    <td name="W_wide" class="wear-slab-cell"><input type="text" class="prism" id="<?php echo attr($eye . 'SLABOFF_' . $W); ?>" name="<?php echo attr($eye . 'SLABOFF_' . $W); ?>" value="<?php echo attr($values[$eye . 'SLABOFF'] ?? ''); ?>"></td>
    <?php
}

/**
 * Render binocular PD controls while preserving saved monocular and near PD values.
 *
 * @param array<string,mixed> $values
 */
function display_wear_lower_cells(mixed $W, string $eye, array $values): void
{
    $W = EyeFormData::text($W);
    $values = EyeFormData::textRow($values);
    $eye = $eye === 'OS' ? 'OS' : 'OD';
    ?>
    <td class="wear-mpd-cell wear-pd-cell <?php echo $eye === 'OD' ? 'wear-pd-label-cell' : 'wear-pd-input-cell'; ?>">
      <input type="hidden" id="<?php echo attr($eye . 'MPDD_' . $W); ?>" name="<?php echo attr($eye . 'MPDD_' . $W); ?>" value="<?php echo attr($values[$eye . 'MPDD'] ?? ''); ?>">
      <input type="hidden" id="<?php echo attr($eye . 'MPDN_' . $W); ?>" name="<?php echo attr($eye . 'MPDN_' . $W); ?>" value="<?php echo attr($values[$eye . 'MPDN'] ?? ''); ?>">
      <?php if ($eye === 'OD') { ?>
        <label for="BPDD_<?php echo attr($W); ?>"><?php echo xlt('PD'); ?></label>
      <?php } else { ?>
        <input type="text" class="prism wear-pd-input" id="BPDD_<?php echo attr($W); ?>" name="BPDD_<?php echo attr($W); ?>" value="<?php echo attr($values['BPDD'] ?? ''); ?>">
        <input type="hidden" id="BPDN_<?php echo attr($W); ?>" name="BPDN_<?php echo attr($W); ?>" value="<?php echo attr($values['BPDN'] ?? ''); ?>">
      <?php } ?>
    </td>
    <td name="W_wide"></td>
    <td name="W_wide"></td>
    <?php
}

/** Complete the comment row with cells aligned to the prescription table. */
function display_wear_comment_tail_cells(): void
{
    ?>
    <td></td>
    <td name="W_wide" colspan="2"></td>
    <?php
}

/**
 * Return the most recent earlier Eye Forms containing documented refraction
 * or Wear data. A prescription does not require VA to qualify.
 * @return list<array<string,mixed>>
 */
function eye_mag_prior_refractions(mixed $pid, mixed $beforeId, int $limit = 3): array
{
    $limit = max(1, min(10, EyeFormData::integer($limit)));
    $sql = "SELECT r.id
              FROM form_eye_refraction r
              JOIN form_eye_acuity a ON a.id=r.id AND a.pid=r.pid
             WHERE r.pid=? AND r.id < ?
               AND (
                    TRIM(CONCAT_WS('',
                        r.ARODSPH,r.ARODCYL,r.ARODAXIS,r.ARODPRISM,r.ARODADD,
                        r.AROSSPH,r.AROSCYL,r.AROSAXIS,r.AROSPRISM,r.AROSADD,
                        r.MRODSPH,r.MRODCYL,r.MRODAXIS,r.MRODPRISM,r.MRODADD,
                        r.MROSSPH,r.MROSCYL,r.MROSAXIS,r.MROSPRISM,r.MROSADD,
                        r.CRODSPH,r.CRODCYL,r.CRODAXIS,r.CRODPRISM,
                        r.CROSSPH,r.CROSCYL,r.CROSAXIS,r.CROSPRISM,
                        r.CTLODSPH,r.CTLODCYL,r.CTLODAXIS,r.CTLODBC,r.CTLODDIAM,r.CTLODADD,
                        r.CTLOSSPH,r.CTLOSCYL,r.CTLOSAXIS,r.CTLOSBC,r.CTLOSDIAM,r.CTLOSADD,
                        a.ARODVA,a.AROSVA,a.MRODVA,a.MROSVA,a.CRODVA,a.CROSVA,a.CTLODVA,a.CTLOSVA
                    )) <> ''
                    OR EXISTS (
                        SELECT 1 FROM form_eye_mag_wearing w
                         WHERE w.PID=r.pid AND w.FORM_ID=r.id
                           AND TRIM(CONCAT_WS('',
                               w.ODSPH,w.ODCYL,w.ODAXIS,w.ODVA,w.ODADD,w.ODNEARVA,
                               w.OSSPH,w.OSCYL,w.OSAXIS,w.OSVA,w.OSADD,w.OSNEARVA,
                               w.ODPRISM,w.OSPRISM,w.ODHPD,w.ODHBASE,w.ODVPD,w.ODVBASE,
                               w.OSHPD,w.OSHBASE,w.OSVPD,w.OSVBASE,w.COMMENTS
                           )) <> ''
                    )
               )
             ORDER BY r.id DESC LIMIT " . $limit;
    return array_map(EyeFormData::row(...), QueryUtils::fetchRecords($sql, [$pid, $beforeId]));
}
