<?php

/**
 * Nursing Applications Form - report.php
 * Displays the application record embedded in the encounter summary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
function aplicaciones_report($pid, $encounter, $cols, $id)
{
    $row = sqlQuery("SELECT * FROM form_aplicaciones WHERE id = ? AND pid = ?", array($id, $pid));
    if (!$row) {
        echo "<p style='color:#c0392b;padding:10px;'>" . xlt("No data found for this record.") . "</p>";
        return;
    }

    $items = [
        'medicamentos'  => xlt('Medications'),
        'sueros'        => xlt('Saline Solutions'),
        'vacunas'       => xlt('Vaccines'),
        'expansiones'   => xlt('Plasma Expanders'),
        'sangre'        => xlt('Blood and Blood Products'),
    ];
    $hora = !empty($row['hora_registro'])
        ? date('H:i', strtotime($row['hora_registro']))
        : xlt('Not specified');
    $fecha = !empty($row['date'])
        ? date('d/m/Y H:i', strtotime($row['date']))
        : '-';
    $total_activos = 0;
    foreach (array_keys($items) as $campo) {
        if ((int)($row[$campo] ?? 0) === 1) {
            $total_activos++;
        }
    }
    $total_items = count($items);
    ?>

    <style>
        .rep-apl * { box-sizing: border-box; }
        .rep-apl {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
            padding: 10px 0;
        }

        .rep-apl .main-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 10px 14px;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        .rep-apl .main-header .title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .rep-apl .main-header .counter {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .rep-apl .main-header .counter span {
            font-size: 13px;
            color: #2ecc71;
        }

        .rep-apl .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            background: #f0f4f8;
            border: 1px solid #d0d8e4;
            border-top: none;
            padding: 8px 14px;
            margin-bottom: 12px;
            font-size: 11px;
            color: #555;
            border-radius: 0 0 4px 4px;
        }
        .rep-apl .meta-bar span strong { color: #2c3e50; }

        .rep-apl table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dde3ea;
            border-radius: 4px;
            overflow: hidden;
        }
        .rep-apl table thead th {
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
        .rep-apl table thead th:last-child { border-right: none; }

        .rep-apl table tbody tr.row-si td { background: #f0faf4; }
        .rep-apl table tbody tr.row-no  td { background: #fff; }
        .rep-apl table tbody tr:hover td  { background: #eaf2ff !important; }

        .rep-apl table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #e4e9ef;
            border-right: 1px solid #e4e9ef;
            font-size: 11px;
            vertical-align: top;
        }
        .rep-apl table tbody td:last-child { border-right: none; }

        .rep-apl .td-nombre {
            font-weight: 600;
            color: #2c3e50;
            width: 30%;
        }
        .rep-apl .td-nombre.activo { color: #1a7a41; }
        .rep-apl .td-estado { width: 12%; text-align: center; }
        .rep-apl .td-obs    { color: #555; }

        .rep-apl .badge-si {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 3px;
            letter-spacing: 0.3px;
        }
        .rep-apl .badge-no {
            display: inline-block;
            background: #bdc3c7;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 3px;
        }

        .rep-apl .obs-vacia {
            color: #bbb;
            font-style: italic;
            font-size: 10px;
        }

        .rep-apl .summary-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 10px;
            padding: 9px 14px;
            background: #f8f9fa;
            border: 1px solid #dde3ea;
            border-radius: 4px;
            font-size: 11px;
            color: #555;
        }
        .rep-apl .summary-bar .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .rep-apl .summary-bar .pill-activos {
            background: #d5f5e3;
            color: #1a7a41;
            border: 1px solid #a9dfbf;
        }
        .rep-apl .summary-bar .pill-inactivos {
            background: #f2f3f4;
            color: #7f8c8d;
            border: 1px solid #d5d8dc;
        }
        .rep-apl .summary-bar .num { font-size: 15px; }

        @media print {
            .rep-apl .main-header { background: #000 !important; -webkit-print-color-adjust: exact; }
            .rep-apl table thead th { background: #333 !important; -webkit-print-color-adjust: exact; }
        }
    </style>

    <div class="rep-apl">

        <!-- HEADER -->
        <div class="main-header">
            <div class="title"><?php echo xlt('Application Record'); ?></div>
            <div class="counter">
                <?php echo xlt('Active items'); ?>:
                <span><?php echo (int)$total_activos; ?></span> / <?php echo (int)$total_items; ?>
            </div>
        </div>

        <!-- META BAR -->
        <div class="meta-bar">
            <span><strong><?php echo xlt('Record Time'); ?>:</strong> <?php echo text($hora); ?></span>
            <span><strong><?php echo xlt('Recorded'); ?>:</strong> <?php echo text($fecha); ?></span>
            <?php if (!empty($row['user'])) :
                ?>
            <span><strong><?php echo xlt('User'); ?>:</strong> <?php echo text($row['user']); ?></span>
                <?php
            endif; ?>
        </div>

        <!-- TABLE -->
        <table>
            <thead>
                <tr>
                    <th class="td-nombre"><?php echo xlt('Item'); ?></th>
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

        <!-- SUMMARY -->
        <div class="summary-bar">
            <span><?php echo xlt('Summary'); ?>:</span>
            <span class="pill pill-activos">
                <span class="num"><?php echo (int)$total_activos; ?></span>
                <?php echo xlt('active'); ?>
            </span>
            <span class="pill pill-inactivos">
                <span class="num"><?php echo (int)($total_items - $total_activos); ?></span>
                <?php echo xlt('inactive'); ?>
            </span>
        </div>

    </div>
    <?php
}
?>
