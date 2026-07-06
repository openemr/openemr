<?php
/**
 * Nursing Evaluations Form - report.php
 * Renders a summary of the evaluation for the OpenEMR encounter report view.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Database\QueryUtils;

$evaluaciones_report = function (int $pid, int $encounter, int $cols, int $id): void {
    $result = QueryUtils::querySingleRow(
        "SELECT * FROM form_evaluaciones WHERE id = ? AND pid = ? LIMIT 1",
        array($id, $pid)
    );

    if (!$result) {
        echo "<div style='padding:10px;color:#c0392b;'>" . xlt("No data found") . "</div>";
        return;
    }

    $score = (int)($result['glasgow_total'] ?? 0);
    if ($score >= 13) {
        $level_text  = xlt('Mild');
        $level_color = '#27ae60';
        $level_bg    = '#eafaf1';
        $level_border = '#27ae60';
    } elseif ($score >= 9) {
        $level_text  = xlt('Moderate');
        $level_color = '#e67e22';
        $level_bg    = '#fef5e7';
        $level_border = '#e67e22';
    } else {
        $level_text  = xlt('Severe');
        $level_color = '#e74c3c';
        $level_bg    = '#fdedec';
        $level_border = '#e74c3c';
    }

    $hora_raw = $result['hora_evaluacion'] ?? '';
    $hora  = text($hora_raw !== '' ? $hora_raw : '-');
    $date_raw = $result['date'] ?? '';
    $fecha = text($date_raw !== '' ? date('d/m/Y H:i', strtotime($date_raw)) : '-');
    $user  = text($result['user'] ?? '-');
    ?>

    <style>
        .rpt-eval * { box-sizing: border-box; }
        .rpt-eval {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10px 0;
        }

        /* Barra de metadatos */
        .rpt-eval .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            background: rgba(128,128,128,0.08);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 4px;
            padding: 8px 14px;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .rpt-eval .meta-bar span strong { color: inherit; }

        /* Cabecera de sección */
        .rpt-eval .sec-header {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 7px 12px;
            border-radius: 4px 4px 0 0;
            margin-top: 14px;
            color: #fff;
        }
        .rpt-eval .sec-header.blue  { background: #2c3e50; }
        .rpt-eval .sec-header.red   { background: #c0392b; }

        /* Tabla */
        .rpt-eval table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .rpt-eval table thead th {
            background: #34495e;
            color: #fff;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .rpt-eval table tbody tr:nth-child(even) td { background: rgba(128,128,128,0.05); }
        .rpt-eval table tbody tr:hover td { background: rgba(0,123,255,0.1); }
        .rpt-eval table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid rgba(128,128,128,0.15);
            font-size: 12px;
            vertical-align: top;
        }
        .rpt-eval .td-item  { font-weight: 600; color: inherit; width: 30%; }
        .rpt-eval .td-val   { width: 22%; }
        .rpt-eval .td-obs   { color: inherit; opacity: 0.8; }
        .rpt-eval .td-sub   { padding-left: 24px; color: inherit; opacity: 0.75; font-style: italic; }

        /* Badge valor */
        .rpt-eval .val-badge {
            display: inline-block;
            background: #2980b9;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 3px;
        }

        /* Texto vacío */
        .rpt-eval .obs-vacia {
            color: inherit;
            opacity: 0.45;
            font-style: italic;
            font-size: 11px;
        }

        /* Card Glasgow total */
        .rpt-eval .glasgow-card {
            margin-top: 14px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid;
        }
        .rpt-eval .glasgow-card-header {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 7px 14px;
            color: #fff;
        }
        .rpt-eval .glasgow-card-body {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 14px 18px;
        }
        .rpt-eval .glasgow-big {
            font-size: 40px;
            font-weight: bold;
            line-height: 1;
        }
        .rpt-eval .glasgow-big small {
            font-size: 16px;
            font-weight: normal;
            color: #aaa;
        }
        .rpt-eval .glasgow-label {
            font-size: 16px;
            font-weight: bold;
        }
        .rpt-eval .glasgow-legend {
            margin-left: auto;
            font-size: 10px;
            color: #888;
            line-height: 1.7;
            text-align: right;
        }

        @media print {
            .rpt-eval .sec-header.blue { background: #000 !important; -webkit-print-color-adjust: exact; }
            .rpt-eval .sec-header.red  { background: #555 !important; -webkit-print-color-adjust: exact; }
        }
    </style>

    <div class="rpt-eval">

        <!-- META BAR -->
        <div class="meta-bar">
            <span><strong><?php echo xlt('Evaluation Time'); ?>:</strong> <?php echo $hora; ?></span>
            <span><strong><?php echo xlt('Recorded'); ?>:</strong> <?php echo $fecha; ?></span>
            <span><strong><?php echo xlt('User'); ?>:</strong> <?php echo $user; ?></span>
        </div>

        <!-- SECCIÓN: VALORACIONES BÁSICAS -->
        <div class="sec-header blue"><?php echo xlt('Basic Assessments'); ?></div>
        <table>
            <thead>
                <tr>
                    <th class="td-item"><?php echo xlt('Item'); ?></th>
                    <th class="td-val"><?php echo xlt('Response'); ?></th>
                    <th class="td-obs"><?php echo xlt('Observations'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $basic = [
                'conciencia' => ['label' => xlt('Consciousness'), 'obs' => 'obs_conciencia'],
                'tono'       => ['label' => xlt('Muscle Tone'),   'obs' => 'obs_tono'],
                'pupilas'    => ['label' => xlt('Pupils'),        'obs' => 'obs_pupilas'],
                'mucosas'    => ['label' => xlt('Mucous Membranes'), 'obs' => 'obs_mucosas'],
            ];
            foreach ($basic as $field => $meta) :
                $val = trim($result[$field] ?? '');
                $obs = trim($result[$meta['obs']] ?? '');
                ?>
                <tr>
                    <td class="td-item"><?php echo text($meta['label']); ?></td>
                    <td class="td-val">
                        <?php if ($val !== '' && $val !== '-') : ?>
                            <span class="val-badge"><?php echo text($val); ?></span>
                        <?php else : ?>
                            <span class="obs-vacia">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="td-obs">
                        <?php echo $obs !== '' ? nl2br(text($obs)) : '<span class="obs-vacia">' . xlt('No observations recorded') . '</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- SECCIÓN: ESCALA DE GLASGOW -->
        <div class="sec-header red"><?php echo xlt('Glasgow Coma Scale'); ?></div>
        <table>
            <thead>
                <tr>
                    <th class="td-item"><?php echo xlt('Component'); ?></th>
                    <th class="td-val"><?php echo xlt('Score'); ?></th>
                    <th class="td-obs"><?php echo xlt('Observations'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $glasgow_fields = [
                'glasgow_ojos'   => ['label' => xlt('Eye Opening'),     'obs' => 'obs_glasgow_ojos'],
                'glasgow_motora' => ['label' => xlt('Motor Response'),  'obs' => 'obs_glasgow_motora'],
                'glasgow_verbal' => ['label' => xlt('Verbal Response'), 'obs' => 'obs_glasgow_verbal'],
            ];
            foreach ($glasgow_fields as $field => $meta) :
                $val = trim($result[$field] ?? '');
                $obs = trim($result[$meta['obs']] ?? '');
                ?>
                <tr>
                    <td class="td-item td-sub"><?php echo text($meta['label']); ?></td>
                    <td class="td-val">
                        <?php if ($val !== '' && $val !== '-') : ?>
                            <span class="val-badge"><?php echo text($val); ?></span>
                        <?php else : ?>
                            <span class="obs-vacia">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="td-obs">
                        <?php echo $obs !== '' ? nl2br(text($obs)) : '<span class="obs-vacia">' . xlt('No observations recorded') . '</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- CARD: PUNTAJE TOTAL GLASGOW -->
        <div class="glasgow-card" style="border-color:<?php echo attr($level_border); ?>">
            <div class="glasgow-card-header" style="background:<?php echo attr($level_color); ?>">
                <?php echo xlt('Glasgow Coma Scale — Total Score'); ?>
            </div>
            <div class="glasgow-card-body" style="background:<?php echo attr($level_bg); ?>">
                <div class="glasgow-big" style="color:<?php echo attr($level_color); ?>">
                    <?php echo text((string)$score); ?><small>/15</small>
                </div>
                <div class="glasgow-label" style="color:<?php echo attr($level_color); ?>">
                    <?php echo text($level_text); ?>
                </div>
                <div class="glasgow-legend">
                    13–15: <?php echo xlt('Mild'); ?><br>
                    9–12: <?php echo xlt('Moderate'); ?><br>
                    3–8: <?php echo xlt('Severe'); ?>
                </div>
            </div>
        </div>

    </div>
    <?php
};
?>
