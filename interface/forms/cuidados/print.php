<?php

/**
 * Nursing Care Bundle Form - print.php
 * Generates a PDF report using mPDF for a single care bundle record.
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
$pid       = (int) filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT)
    ?: (int) ($_print_session->get('pid') ?? 0);
$encounter = (int) filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)
    ?: (int) ($_print_session->get('encounter') ?? 0);
$id        = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$pid || !$encounter || !$id) {
    die(xlt("Error: Missing required parameters."));
}

// Load care bundle record
$row = QueryUtils::querySingleRow("SELECT * FROM form_cuidados WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
if (!$row) {
    die(xlt("Error: Record not found or insufficient permissions."));
}

// Load patient data
$paciente = QueryUtils::querySingleRow("SELECT CONCAT(fname, ' ', lname) AS full_name, pubpid, DOB FROM patient_data WHERE pid = ?", [$pid]);
// Calculate age
$age = '';
$dob_val = ($paciente ?? [])['DOB'] ?? '';
if ($dob_val !== '') {
    $dob = new DateTime($dob_val);
    $age = (new DateTime())->diff($dob)->y . ' ' . xlt('years');
}

$date_raw  = $row['date'] ?? '';
$eval_date = ($date_raw !== '')         ? date('d/m/Y', strtotime((string) $date_raw)) : '-';
$hora_raw  = $row['hora_cuidado'] ?? '';
$eval_time = ($hora_raw !== '') ? $hora_raw                   : '-';

// Helper: boolean table row
$careRow = function (string $label, int $val, string $obs): string {
    $obs_html = ($obs !== '' && $obs !== '-')
        ? htmlspecialchars($obs)
        : '<span style="color:#bbb;font-style:italic;">' . xlt('No observations recorded') . '</span>';
    $badge = $val
        ? '<span style="background:#27ae60;color:#fff;padding:2px 9px;border-radius:3px;font-size:8px;font-weight:bold;">' . xlt('Yes') . '</span>'
        : '<span style="background:#95a5a6;color:#fff;padding:2px 9px;border-radius:3px;font-size:8px;font-weight:bold;">' . xlt('No') . '</span>';
    return '
    <tr>
        <td style="padding:7px 10px;border-bottom:1px solid #e4e9ef;font-weight:600;font-size:9px;color:#2c3e50;width:30%;">'
            . htmlspecialchars($label) . '</td>
        <td style="padding:7px 10px;border-bottom:1px solid #e4e9ef;width:20%;text-align:center;">' . $badge . '</td>
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
     . 'text-transform:uppercase;letter-spacing:0.5px;width:30%;">' . $c1 . '</th>'
     . '<th style="background:#34495e;color:#fff;padding:7px 10px;text-align:center;font-size:9px;'
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
            <?php echo xlt('NURSING CARE BUNDLE RECORD'); ?>
        </div>
        <div style="font-size:8px; color:#7f8c8d; margin-top:5px;">
            <?php echo xlt('Encounter'); ?>: <strong><?php echo text((string)$encounter); ?></strong>
            &nbsp;|&nbsp;
            <?php echo xlt('Date'); ?>: <strong><?php echo text($eval_date); ?></strong>
            &nbsp;|&nbsp;
            <?php echo xlt('Care Time'); ?>: <strong><?php echo text($eval_time); ?></strong>
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
                    <div style="font-weight:bold; font-size:11px;"><?php echo text(($paciente ?? [])['full_name'] ?? '-'); ?></div>
                </td>
                <td style="width:18%; padding:3px 8px;">
                    <div style="font-size:7px; color:#888; margin-bottom:2px;"><?php echo xlt('ID'); ?></div>
                    <div style="font-weight:bold; font-size:11px;"><?php echo text(($paciente ?? [])['pubpid'] ?? '-'); ?></div>
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
                    <div style="font-weight:bold; font-size:11px;"><?php echo text($row['user'] ?? '-'); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PATIENT POSITION -->
    <?php echo $secHeader(xlt('Patient Position'), '#1976d2'); ?>
    <div style="background:#eef2ff; border:1px solid #c7d2fe; padding:10px 14px; margin-bottom:12px;">
        <span style="font-size:9px; color:#6c757d; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">
            <?php echo xlt('Position'); ?>:
        </span>
        <span style="font-size:12px; font-weight:bold; color:#1976d2; margin-left:8px;">
            <?php echo ($row['posicion_paciente'] ?? '') !== '' ? text($row['posicion_paciente']) : xlt('Not specified'); ?>
        </span>
        <?php if (($row['obs_posicion_paciente'] ?? '') !== '') :
            ?>
        <div style="margin-top:6px; font-size:9px; color:#444;"><?php echo htmlspecialchars((string) $row['obs_posicion_paciente']); ?></div>
            <?php
        endif; ?>
    </div>

    <!-- CARE BUNDLE TABLE -->
    <?php echo $secHeader(xlt('Care Bundle Detail'), '#2c3e50'); ?>
    <?php echo $tableHeader(xlt('Procedure'), xlt('Status'), xlt('Observations')); ?>
    <?php
    $bool_fields = [
        xlt('Oral Rinse')                                              => ['val' => (int)($row['enjuague_bucal']           ?? 0), 'obs' => $row['obs_enjuague_bucal']       ?? ''],
        xlt('Hand Hygiene Pre/Post Suctioning')                        => ['val' => (int)($row['higiene_manos']            ?? 0), 'obs' => $row['obs_higiene_manos']        ?? ''],
        xlt('Secretion Suctioning with Gloves and Assistant')          => ['val' => (int)($row['aspirado_secreciones']     ?? 0), 'obs' => $row['obs_aspirado_secreciones'] ?? ''],
        xlt('Daily Sedation Suspension and Extubation Evaluation')     => ['val' => (int)($row['suspension_sedacion']      ?? 0), 'obs' => $row['obs_suspension_sedacion']  ?? ''],
        xlt('Cuff Pressure Measurement')                               => ['val' => (int)($row['medicion_cuff']            ?? 0), 'obs' => $row['obs_medicion_cuff']        ?? ''],
    ];
    foreach ($bool_fields as $label => $data) {
        echo $careRow($label, $data['val'], $data['obs']);
    }
    ?>
    </tbody></table>

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
$mpdf->SetTitle(xlt('Nursing Care Bundle') . ' - ' . (($paciente ?? [])['full_name'] ?? ''));
$mpdf->WriteHTML((string)$html);
$filename = 'Cuidados_' . preg_replace('/\s+/', '_', ($paciente ?? [])['full_name'] ?? 'paciente') . '_' . date('Ymd_His') . '.pdf';
$mpdf->Output($filename, 'D');
exit;
