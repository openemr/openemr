<?php

/**
 * Mechanical Ventilation Record Form - report.php
 * Displays the ventilation record embedded in the encounter summary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Database\QueryUtils;

$registro_vm_report = function (int $pid, int $encounter, int $cols, int $id): void {
    $row = QueryUtils::querySingleRow("SELECT * FROM form_registro_vm WHERE id = ? AND pid = ?", [$id, $pid]);
    if (!$row) {
        echo "<p style='color:#c0392b;padding:10px;'>" . xlt("No data found for this record.") . "</p>";
        return;
    }

    $bool_items = [
        'presion'                 => xlt('Pressure'),
        'volumen'                 => xlt('Volume'),
        'simv'                    => 'SIMV',
        'psv'                     => 'PSV',
        'otros'                   => xlt('Other'),
        'frecuencia_respiratoria' => xlt('Respiratory Rate'),
        'p_inspiratorio'          => xlt('P.Inspiratory / T.Inspiratory'),
        'p_media'                 => xlt('P.Mean / PEEP'),
        'p_max'                   => xlt('P.Max / P.Plateau'),
        'chst'                    => 'CHST / CDIN',
        'disparo'                 => xlt('Trigger F/P'),
        'fvt'                     => 'F / VT',
        'vol_tidal'               => xlt('Tidal Volume / Flow'),
        'vm_programado'           => xlt('Programmed/Measured MV'),
        'petco2'                  => 'PETCO2',
        'vdvt'                    => 'VD / VT',
        'ko2'                     => 'KO2',
    ];
    $modo_labels = [
        'ESPONTANEA'           => xlt('Spontaneous'),
        'VENTILACION MECANICA' => xlt('Mechanical Ventilation'),
    ];
    $modo_display = $modo_labels[$row['modo_ventilacion'] ?? ''] ?? ($row['modo_ventilacion'] ?? xlt('Not specified'));
    $hora_raw = $row['hora_registro'] ?? '';
    $hora = ($hora_raw !== '')
        ? date('H:i', strtotime((string) $hora_raw))
        : xlt('Not specified');
    $date_raw = $row['date'] ?? '';
    $fecha = ($date_raw !== '')
        ? date('d/m/Y H:i', strtotime((string) $date_raw))
        : '-';
    $total_activos = 0;
    foreach (array_keys($bool_items) as $campo) {
        if ((int)($row[$campo] ?? 0) === 1) {
            $total_activos++;
        }
    }
    $total_items = count($bool_items);
    ?>

    <style>
        .rep-vm * { box-sizing: border-box; }
        .rep-vm {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10px 0;
        }

        .rep-vm .main-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 10px 14px;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        .rep-vm .main-header .title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .rep-vm .main-header .counter {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .rep-vm .main-header .counter span {
            font-size: 13px;
            color: #2ecc71;
        }

        .rep-vm .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            background: rgba(128,128,128,0.08);
            border: 1px solid rgba(128,128,128,0.2);
            border-top: none;
            padding: 8px 14px;
            margin-bottom: 12px;
            font-size: 11px;
            border-radius: 0 0 4px 4px;
        }
        .rep-vm .meta-bar span strong { color: inherit; }

        .rep-vm .modo-bar {
            background: rgba(25,118,210,0.08);
            border: 1px solid rgba(25,118,210,0.3);
            border-left: 4px solid #1976d2;
            padding: 8px 14px;
            margin-bottom: 12px;
            font-size: 11px;
            border-radius: 3px;
        }
        .rep-vm .modo-bar strong { color: #1976d2; font-size: 13px; }

        .rep-vm table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid rgba(128,128,128,0.2);
            overflow: hidden;
        }
        .rep-vm table thead th {
            background: #34495e;
            color: #fff;
            padding: 9px 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .rep-vm table thead th:last-child { border-right: none; }

        .rep-vm table tbody tr.row-si td { background: rgba(40,167,69,0.1); }
        .rep-vm table tbody tr.row-no  td { background: transparent; }
        .rep-vm table tbody tr:hover td  { background: rgba(0,123,255,0.1) !important; }

        .rep-vm table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid rgba(128,128,128,0.15);
            border-right: 1px solid rgba(128,128,128,0.15);
            font-size: 11px;
            vertical-align: top;
        }
        .rep-vm table tbody td:last-child { border-right: none; }

        .rep-vm .td-nombre { font-weight: 600; color: inherit; width: 30%; }
        .rep-vm .td-nombre.activo { color: #1a7a41; }
        .rep-vm .td-estado { width: 12%; text-align: center; }
        .rep-vm .td-obs    { color: inherit; opacity: 0.8; }

        .rep-vm .badge-si {
            display: inline-block; background: #27ae60; color: #fff;
            font-size: 10px; font-weight: bold; padding: 4px 12px; border-radius: 3px;
        }
        .rep-vm .badge-no {
            display: inline-block; background: #bdc3c7; color: #fff;
            font-size: 10px; font-weight: bold; padding: 4px 12px; border-radius: 3px;
        }

        .rep-vm .obs-vacia { color: inherit; opacity: 0.45; font-style: italic; font-size: 10px; }

        .rep-vm .summary-bar {
            display: flex; align-items: center; gap: 14px; margin-top: 10px;
            padding: 9px 14px; background: rgba(128,128,128,0.05); border: 1px solid rgba(128,128,128,0.2);
            border-radius: 4px; font-size: 11px;
        }
        .rep-vm .summary-bar .pill {
            display: inline-flex; align-items: center; gap: 5px; font-weight: bold;
            font-size: 11px; padding: 4px 12px; border-radius: 20px;
        }
        .rep-vm .summary-bar .pill-activos  { background: #d5f5e3; color: #1a7a41; border: 1px solid #a9dfbf; }
        .rep-vm .summary-bar .pill-inactivos { background: rgba(128,128,128,0.1); color: inherit; border: 1px solid rgba(128,128,128,0.2); }
        .rep-vm .summary-bar .num { font-size: 15px; }

        @media print {
            .rep-vm .main-header { background: #000 !important; -webkit-print-color-adjust: exact; }
            .rep-vm table thead th { background: #333 !important; -webkit-print-color-adjust: exact; }
        }
    </style>

    <div class="rep-vm">

        <!-- HEADER -->
        <div class="main-header">
            <div class="title"><?php echo xlt('Ventilation Record'); ?></div>
            <div class="counter">
                <?php echo xlt('Active parameters'); ?>:
                <span><?php echo $total_activos; ?></span> / <?php echo $total_items; ?>
            </div>
        </div>

        <!-- META BAR -->
        <div class="meta-bar">
            <span><strong><?php echo xlt('Record Time'); ?>:</strong> <?php echo text($hora); ?></span>
            <span><strong><?php echo xlt('Recorded'); ?>:</strong> <?php echo text($fecha); ?></span>
            <?php if (isset($row['user']) && $row['user'] !== '') :
                ?>
            <span><strong><?php echo xlt('User'); ?>:</strong> <?php echo text($row['user']); ?></span>
                <?php
            endif; ?>
        </div>

        <!-- VENTILATION MODE -->
        <div class="modo-bar">
            <?php echo xlt('Ventilation Mode'); ?>:
            <strong><?php echo text($modo_display); ?></strong>
            <?php if (($row['obs_modo'] ?? '') !== '') :
                ?>
            &mdash; <span style="color:#555;font-size:11px;"><?php echo text($row['obs_modo']); ?></span>
                <?php
            endif; ?>
        </div>

        <!-- TABLE -->
        <table>
            <thead>
                <tr>
                    <th class="td-nombre"><?php echo xlt('Parameter'); ?></th>
                    <th class="td-estado"><?php echo xlt('Status'); ?></th>
                    <th class="td-obs"><?php echo xlt('Observations'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bool_items as $campo => $label) :
                $valor  = (int)($row[$campo] ?? 0);
                $obs    = trim($row['obs_' . $campo] ?? '');
                $rowCls = $valor ? 'row-si' : 'row-no';
                $tdCls  = $valor ? 'td-nombre activo' : 'td-nombre';
                ?>
                <tr class="<?php echo $rowCls; ?>">
                    <td class="<?php echo $tdCls; ?>"><?php echo text($label); ?></td>
                    <td class="td-estado">
                        <?php if ($valor) :
                            ?>
                            <span class="badge-si"><?php echo xlt('Yes'); ?></span>
                            <?php
                        else :
                            ?>
                            <span class="badge-no"><?php echo xlt('No'); ?></span>
                            <?php
                        endif; ?>
                    </td>
                    <td class="td-obs">
                        <?php if ($obs !== '') :
                            ?>
                            <?php echo nl2br(text($obs)); ?>
                            <?php
                        else :
                            ?>
                            <span class="obs-vacia"><?php echo xlt('No observations recorded'); ?></span>
                            <?php
                        endif; ?>
                    </td>
                </tr>
                <?php
            endforeach; ?>
            </tbody>
        </table>

        <!-- SUMMARY -->
        <div class="summary-bar">
            <span><?php echo xlt('Summary'); ?>:</span>
            <span class="pill pill-activos">
                <span class="num"><?php echo $total_activos; ?></span>
                <?php echo xlt('active'); ?>
            </span>
            <span class="pill pill-inactivos">
                <span class="num"><?php echo $total_items - $total_activos; ?></span>
                <?php echo xlt('inactive'); ?>
            </span>
        </div>

    </div>
    <?php
};
?>
