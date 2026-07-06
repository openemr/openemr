<?php

/**
 * Wound Care Form - report.php
 * Displays the wound care record embedded in the encounter summary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Database\QueryUtils;

$curaciones_report = function (int $pid, int $encounter, int $cols, int $id): void {
    $row = QueryUtils::querySingleRow("SELECT * FROM form_curaciones WHERE id = ? AND pid = ?", [$id, $pid]);
    if (!$row) {
        echo "<p style='color:#c0392b;padding:10px;'>" . xlt("No data found for this record.") . "</p>";
        return;
    }

    $items = [
        'herida_operatoria'  => xlt('Surgical Wound'),
        'traqueostomia'      => xlt('Tracheostomy'),
        'ostomias'           => xlt('Ostomies'),
        'escaras'            => xlt('Pressure Sores'),
        'via_venosa_central' => xlt('Central Venous Line'),
        'via_venosa'         => xlt('Peripheral IV Line'),
    ];
    $hora_raw = $row['hora_operacion'] ?? '';
    $hora = ($hora_raw !== '')
        ? date('H:i', strtotime((string) $hora_raw))
        : xlt('Not specified');
    $date_raw = $row['date'] ?? '';
    $fecha = ($date_raw !== '')
        ? date('d/m/Y H:i', strtotime((string) $date_raw))
        : '-';
// Contar activos
    $total_activos = 0;
    foreach (array_keys($items) as $campo) {
        if ((int)($row[$campo] ?? 0) === 1) {
            $total_activos++;
        }
    }
    $total_items = count($items);
    ?>

    <style>
        .rep-cur * { box-sizing: border-box; }
        .rep-cur {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10px 0;
        }

        /* Cabecera principal */
        .rep-cur .main-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 10px 14px;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        .rep-cur .main-header .title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .rep-cur .main-header .counter {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .rep-cur .main-header .counter span {
            font-size: 13px;
            color: #2ecc71;
        }

        /* Barra de metadatos */
        .rep-cur .meta-bar {
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
        .rep-cur .meta-bar span strong { color: inherit; }

        /* Tabla */
        .rep-cur table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 4px;
            overflow: hidden;
        }
        .rep-cur table thead th {
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
        .rep-cur table thead th:last-child { border-right: none; }

        .rep-cur table tbody tr.row-si td { background: rgba(40,167,69,0.1); }
        .rep-cur table tbody tr.row-no  td { background: transparent; }
        .rep-cur table tbody tr:hover td  { background: rgba(0,123,255,0.1) !important; }

        .rep-cur table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid rgba(128,128,128,0.15);
            border-right: 1px solid rgba(128,128,128,0.15);
            font-size: 11px;
            vertical-align: top;
        }
        .rep-cur table tbody td:last-child { border-right: none; }

        .rep-cur .td-nombre {
            font-weight: 600;
            color: inherit;
            width: 30%;
        }
        .rep-cur .td-nombre.activo { color: #1a7a41; }
        .rep-cur .td-estado { width: 12%; text-align: center; }
        .rep-cur .td-obs    { color: inherit; opacity: 0.8; }

        /* Badges */
        .rep-cur .badge-si {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 3px;
            letter-spacing: 0.3px;
        }
        .rep-cur .badge-no {
            display: inline-block;
            background: #bdc3c7;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 3px;
        }

        /* Obs vacía */
        .rep-cur .obs-vacia {
            color: inherit;
            opacity: 0.45;
            font-style: italic;
            font-size: 10px;
        }

        /* Resumen inferior */
        .rep-cur .summary-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 10px;
            padding: 9px 14px;
            background: rgba(128,128,128,0.05);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 4px;
            font-size: 11px;
        }
        .rep-cur .summary-bar .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .rep-cur .summary-bar .pill-activos {
            background: #d5f5e3;
            color: #1a7a41;
            border: 1px solid #a9dfbf;
        }
        .rep-cur .summary-bar .pill-inactivos {
            background: rgba(128,128,128,0.1);
            color: inherit;
            border: 1px solid rgba(128,128,128,0.2);
        }
        .rep-cur .summary-bar .num { font-size: 15px; }

        @media print {
            .rep-cur .main-header { background: #000 !important; -webkit-print-color-adjust: exact; }
            .rep-cur table thead th { background: #333 !important; -webkit-print-color-adjust: exact; }
        }
    </style>

    <div class="rep-cur">

        <!-- CABECERA -->
        <div class="main-header">
            <div class="title"><?php echo xlt('Wound Care Record'); ?></div>
            <div class="counter">
                <?php echo xlt('Active procedures'); ?>:
                <span><?php echo $total_activos; ?></span> / <?php echo $total_items; ?>
            </div>
        </div>

        <!-- META BAR -->
        <div class="meta-bar">
            <span><strong><?php echo xlt('Care Time'); ?>:</strong> <?php echo text($hora); ?></span>
            <span><strong><?php echo xlt('Recorded'); ?>:</strong> <?php echo text($fecha); ?></span>
            <?php if (isset($row['user']) && $row['user'] !== '') :
                ?>
            <span><strong><?php echo xlt('User'); ?>:</strong> <?php echo text($row['user']); ?></span>
                <?php
            endif; ?>
        </div>

        <!-- TABLA -->
        <table>
            <thead>
                <tr>
                    <th class="td-nombre"><?php echo xlt('Procedure'); ?></th>
                    <th class="td-estado"><?php echo xlt('Status'); ?></th>
                    <th class="td-obs"><?php echo xlt('Observations'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $campo => $label) :
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

        <!-- RESUMEN -->
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
