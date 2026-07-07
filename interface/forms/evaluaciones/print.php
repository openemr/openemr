<?php

/**
 * Nursing Evaluations Form - print.php
 * Generates a PDF report using mPDF for a single evaluation record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$_print_session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid       = is_numeric($v = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0
    ?: (is_numeric($v = $_print_session->get('pid')) ? (int) $v : 0);
$encounter = is_numeric($v = filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0
    ?: (is_numeric($v = $_print_session->get('encounter')) ? (int) $v : 0);
$id        = is_numeric($v = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0;
if (!$pid || !$encounter || !$id) {
    die(xlt("Error: Missing required parameters."));
}

// Load evaluation record
    /** @var array<string, string|int|null>|false $row */
$row = QueryUtils::querySingleRow("SELECT * FROM form_evaluaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
if (!$row) {
    die(xlt("Error: Record not found or insufficient permissions."));
}

// Load patient data
/** @var array<string, string|int|null>|false $paciente */
$paciente = QueryUtils::querySingleRow("SELECT CONCAT(fname, ' ', lname) AS full_name, pubpid, DOB FROM patient_data WHERE pid = ?", [$pid]);
// Calculate age
$age = '';
$dob_val = $paciente !== false ? (string)($paciente['DOB'] ?? '') : '';
if ($dob_val !== '') {
    $dob = new DateTime($dob_val);
    $age = (new DateTime())->diff($dob)->y . ' ' . xlt('years');
}

// Glasgow level — colores suaves aptos para impresión
$glasgow = (int)($row['glasgow_total'] ?? 0);
if ($glasgow >= 13) {
    $glasgow_level  = xlt('Mild');
    $glasgow_color  = '#388e3c';
// verde medio suave
    $glasgow_bg     = '#f9fbe7';
// fondo casi blanco verdoso
    $glasgow_border = '#c5e1a5';
// borde verde suave
} elseif ($glasgow >= 9) {
    $glasgow_level  = xlt('Moderate');
    $glasgow_color  = '#e65100';
// naranja oscuro
    $glasgow_bg     = '#fff8e1';
// amarillo muy claro
    $glasgow_border = '#ffcc80';
// naranja suave
} else {
    $glasgow_level  = xlt('Severe');
    $glasgow_color  = '#b71c1c';
// rojo oscuro
    $glasgow_bg     = '#fce4ec';
// rosa muy claro
    $glasgow_border = '#ef9a9a';
// rojo suave
}

// Evaluation date/time
$date_raw  = $row['date'] ?? '';
$ts_eval = $date_raw !== '' ? strtotime((string) $date_raw) : false;
$eval_date = $ts_eval !== false ? date('d/m/Y', $ts_eval) : '-';
$hora_raw  = $row['hora_evaluacion'] ?? '';
$eval_time = ($hora_raw !== '') ? (string)$hora_raw       : '-';

// Helper: table row
$evalRow = function (string $label, string $value, string $obs): string {
    $obs_html = ($obs !== '' && $obs !== '-')
        ? htmlspecialchars($obs)
        : '<span style="color:#bbb;font-style:italic;">' . xlt('No observations recorded') . '</span>';
    $val_html = ($value !== '' && $value !== '-')
        ? '<span style="background:#2980b9;color:#fff;padding:2px 9px;border-radius:3px;font-size:8px;font-weight:bold;">' . htmlspecialchars($value) . '</span>'
        : '<span style="color:#ccc;">—</span>';
    return '
    <tr>
        <td style="padding:7px 10px;border-bottom:1px solid #e4e9ef;font-weight:600;font-size:9px;color:#2c3e50;width:28%;">'
            . htmlspecialchars($label) . '</td>
        <td style="padding:7px 10px;border-bottom:1px solid #e4e9ef;width:20%;">' . $val_html . '</td>
        <td style="padding:7px 10px;border-bottom:1px solid #e4e9ef;font-size:9px;color:#444;">' . $obs_html . '</td>
    </tr>';
};

// Section header helper
$secHeader = (fn(string $title, string $bg): string => '<div style="background:' . $bg . ';color:#fff;font-size:9px;font-weight:bold;'
     . 'text-transform:uppercase;letter-spacing:1px;padding:7px 12px;margin:12px 0 0 0;">'
     . $title . '</div>');

// Table header row
$tableHeader = (fn(string $c1, string $c2, string $c3): string => '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;">'
     . '<thead><tr>'
     . '<th style="background:#34495e;color:#fff;padding:7px 10px;text-align:left;font-size:9px;'
     . 'text-transform:uppercase;letter-spacing:0.5px;width:28%;">' . $c1 . '</th>'
     . '<th style="background:#34495e;color:#fff;padding:7px 10px;text-align:left;font-size:9px;'
     . 'text-transform:uppercase;letter-spacing:0.5px;width:20%;">' . $c2 . '</th>'
     . '<th style="background:#34495e;color:#fff;padding:7px 10px;text-align:left;font-size:9px;'
     . 'text-transform:uppercase;letter-spacing:0.5px;">' . $c3 . '</th>'
     . '</tr></thead><tbody>');

// ---------------------------------------------------------------
// Build HTML
// ---------------------------------------------------------------
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 10px; color: #222; margin: 0; padding: 0; }
    .page { padding: 5px; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; }
    tr:nth-child(even) td { background: #f7f9fb; }
</style>
</head>
<body>
<div class="page">

    <!-- HEADER -->
    <div style="border-bottom:3px solid #2c3e50; padding-bottom:10px; margin-bottom:12px; text-align:center;">
        <div style="font-size:15px; font-weight:bold; color:#2c3e50; text-transform:uppercase; letter-spacing:2px;">
            <?php echo xlt('NURSING EVALUATIONS RECORD'); ?>
        </div>
        <div style="font-size:8px; color:#7f8c8d; margin-top:5px;">
            <?php echo xlt('Encounter'); ?>: <strong><?php echo text((string)$encounter); ?></strong>
            &nbsp;|&nbsp;
            <?php echo xlt('Date'); ?>: <strong><?php echo text($eval_date); ?></strong>
            &nbsp;|&nbsp;
            <?php echo xlt('Evaluation Time'); ?>: <strong><?php echo text($eval_time); ?></strong>
        </div>
    </div>

    <!-- PATIENT INFO -->
    <div style="background:#f8f9fa; border:1px solid #d0d8e4; border-radius:4px; padding:9px 12px; margin-bottom:14px;">
        <div style="font-weight:bold; font-size:8px; color:#495057; text-transform:uppercase; letter-spacing:0.5px;
                    margin-bottom:7px; border-bottom:1px solid #dee2e6; padding-bottom:5px;">
            <?php echo xlt('Patient Information'); ?>
        </div>
        <table>
            <tr>
                <td style="width:38%; padding:3px 8px 3px 0;">
                    <div style="font-size:7px; color:#888; margin-bottom:2px;"><?php echo xlt('Patient'); ?></div>
                    <div style="font-weight:bold; font-size:11px;"><?php echo text($paciente !== false ? (string)($paciente['full_name'] ?? '-') : '-'); ?></div>
                </td>
                <td style="width:18%; padding:3px 8px;">
                    <div style="font-size:7px; color:#888; margin-bottom:2px;"><?php echo xlt('ID'); ?></div>
                    <div style="font-weight:bold; font-size:11px;"><?php echo text($paciente !== false ? (string)($paciente['pubpid'] ?? '-') : '-'); ?></div>
                </td>
                <?php if ($age !== '') :
                    ?>
                <td style="width:18%; padding:3px 8px;">
                    <div style="font-size:7px; color:#888; margin-bottom:2px;"><?php echo xlt('Age'); ?></div>
                    <div style="font-weight:bold; font-size:11px;"><?php echo text($age); ?></div>
                </td>
                    <?php
                endif; ?>
                <td style="padding:3px 0 3px 8px;">
                    <div style="font-size:7px; color:#888; margin-bottom:2px;"><?php echo xlt('User'); ?></div>
                    <div style="font-weight:bold; font-size:11px;"><?php echo text((string)($row['user'] ?? '-')); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- BASIC ASSESSMENTS -->
    <?php echo $secHeader(xlt('Basic Assessments'), '#2c3e50'); ?>
    <?php echo $tableHeader(xlt('Item'), xlt('Response'), xlt('Observations')); ?>
    <?php
    $basic = [
        xlt('Consciousness')    => ['val' => $row['conciencia']  ?? '', 'obs' => $row['obs_conciencia']  ?? ''],
        xlt('Muscle Tone')      => ['val' => $row['tono']        ?? '', 'obs' => $row['obs_tono']        ?? ''],
        xlt('Pupils')           => ['val' => $row['pupilas']     ?? '', 'obs' => $row['obs_pupilas']     ?? ''],
        xlt('Mucous Membranes') => ['val' => $row['mucosas']     ?? '', 'obs' => $row['obs_mucosas']     ?? ''],
    ];
    foreach ($basic as $label => $data) {
        echo $evalRow((string)$label, (string)$data['val'], (string)$data['obs']);
    }
    ?>
    </tbody></table>

    <!-- GLASGOW COMA SCALE -->
    <?php echo $secHeader(xlt('Glasgow Coma Scale'), '#c0392b'); ?>
    <?php echo $tableHeader(xlt('Component'), xlt('Score'), xlt('Observations')); ?>
    <?php
    $glasgow_fields = [
        xlt('Eye Opening')     => ['val' => $row['glasgow_ojos']   ?? '', 'obs' => $row['obs_glasgow_ojos']   ?? ''],
        xlt('Motor Response')  => ['val' => $row['glasgow_motora'] ?? '', 'obs' => $row['obs_glasgow_motora'] ?? ''],
        xlt('Verbal Response') => ['val' => $row['glasgow_verbal'] ?? '', 'obs' => $row['obs_glasgow_verbal'] ?? ''],
    ];
    foreach ($glasgow_fields as $label => $data) {
        echo $evalRow((string)$label, (string)$data['val'], (string)$data['obs']);
    }
    ?>
    </tbody></table>

    <!-- GLASGOW TOTAL CARD -->
    <div style="margin-top:14px; border:1px solid <?php echo $glasgow_border; ?>; border-top:3px solid <?php echo $glasgow_color; ?>; border-radius:4px; overflow:hidden; background:<?php echo $glasgow_bg; ?>;">

        <!-- Título -->
        <div style="background:#34495e; color:#fff; font-size:9px; font-weight:bold;
                    text-transform:uppercase; letter-spacing:1px; padding:7px 14px;">
            <?php echo xlt('Glasgow Coma Scale — Total Score'); ?>
        </div>

        <!-- Cuerpo con tabla para compatibilidad mPDF -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <!-- Puntaje grande -->
                <td style="width:22%; text-align:center; padding:16px 10px; border-right:1px solid <?php echo $glasgow_border; ?>; vertical-align:middle;">
                    <div style="font-size:44px; font-weight:bold; color:#2c3e50; line-height:1;">
                        <?php echo text((string)$glasgow); ?>
                    </div>
                    <div style="font-size:11px; color:#999; margin-top:2px;">/ 15</div>
                </td>

                <!-- Nivel -->
                <td style="width:28%; text-align:center; padding:16px 10px; border-right:1px solid <?php echo $glasgow_border; ?>; vertical-align:middle;">
                    <div style="font-size:8px; color:#888; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">
                        <?php echo xlt('Injury level'); ?>
                    </div>
                    <div style="display:inline-block; background:#546e7a; color:#fff;
                                font-size:13px; font-weight:bold; padding:6px 18px; border-radius:4px; letter-spacing:0.5px;">
                        <?php echo text($glasgow_level); ?>
                    </div>
                </td>

                <!-- Tabla de referencia -->
                <td style="padding:12px 16px; vertical-align:middle;">
                    <div style="font-size:8px; color:#555; font-weight:bold; text-transform:uppercase;
                                letter-spacing:0.5px; margin-bottom:8px;">
                        <?php echo xlt('Reference scale'); ?>
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:9px;">
                        <tr>
                            <td style="padding:4px 8px; background:#<?php echo $glasgow >= 13 ? '66bb6a' : 'e0e0e0'; ?>;
                                       color:#<?php echo $glasgow >= 13 ? '1b5e20' : '777'; ?>;
                                       font-weight:<?php echo $glasgow >= 13 ? 'bold' : 'normal'; ?>;
                                       border-radius:3px 3px 0 0; border-bottom:1px solid #ccc;">
                                13 – 15 &nbsp; <?php echo xlt('Mild'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:4px 8px; background:#<?php echo ($glasgow >= 9 && $glasgow <= 12) ? 'e65100' : 'e0e0e0'; ?>;
                                       color:#<?php echo ($glasgow >= 9 && $glasgow <= 12) ? 'fff' : '777'; ?>;
                                       font-weight:<?php echo ($glasgow >= 9 && $glasgow <= 12) ? 'bold' : 'normal'; ?>;
                                       border-bottom:1px solid #ccc;">
                                &nbsp;9 – 12 &nbsp; <?php echo xlt('Moderate'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:4px 8px; background:#<?php echo $glasgow <= 8 ? 'b71c1c' : 'e0e0e0'; ?>;
                                       color:#<?php echo $glasgow <= 8 ? 'fff' : '777'; ?>;
                                       font-weight:<?php echo $glasgow <= 8 ? 'bold' : 'normal'; ?>;
                                       border-radius:0 0 3px 3px;">
                                &nbsp;3 – &nbsp;8 &nbsp; <?php echo xlt('Severe'); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- SIGNATURE -->
    <table style="width:100%; margin-top:40px; border-top:1px solid #ccc; padding-top:15px;">
        <tr>
            <td style="width:50%; text-align:center; padding:0 25px;">
                <div style="border-top:2px solid #333; margin:50px 15px 6px 15px;"></div>
                <div style="font-size:9px; font-weight:bold; color:#333;"><?php echo xlt('Responsible Signature'); ?></div>
            </td>
            <td style="width:50%; text-align:center; padding:0 25px;">
                <div style="border-top:2px solid #333; margin:50px 15px 6px 15px;"></div>
                <div style="font-size:9px; font-weight:bold; color:#333;"><?php echo xlt('Clarification'); ?></div>
            </td>
        </tr>
    </table>
    <div style="text-align:center; margin-top:12px; font-size:8px; color:#888;">
        <?php echo xlt('Date'); ?>: _____/_____/________
    </div>

</div>
</body>
</html>
<?php
$html = ob_get_clean();
// ---------------------------------------------------------------
// Generate PDF with mPDF
// ---------------------------------------------------------------
$mpdf = new Mpdf([
    'mode'              => 'utf-8',
    'format'            => 'Letter',
    'margin_top'        => 12,
    'margin_bottom'     => 12,
    'margin_left'       => 15,
    'margin_right'      => 15,
    'default_font'      => 'Arial',
    'default_font_size' => 10,
    'tempDir'           => sys_get_temp_dir(),
]);
$mpdf->SetTitle(xlt('Nursing Evaluations') . ' - ' . ($paciente !== false ? (string)($paciente['full_name'] ?? '') : ''));
$mpdf->WriteHTML((string)$html);
$filename = 'Evaluaciones_' . preg_replace('/\s+/', '_', $paciente !== false ? (string)($paciente['full_name'] ?? 'paciente') : 'paciente') . '_' . date('Ymd_His') . '.pdf';
$mpdf->Output($filename, 'D');
exit;
