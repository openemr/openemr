<?php

/**
 * Nursing Wound Care Form - view.php
 * Displays wound care records for a patient encounter, with PDF export.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$session   = SessionWrapperFactory::getInstance()->getActiveSession();
$pid       = (int) filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT)
    ?: (int)($session->get('pid') ?? 0);
$encounter = (int) filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)
    ?: (int)($session->get('encounter') ?? 0);
$id        = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$pid || !$encounter) {
    echo "<div style='padding:20px;color:red;'>" . xlt("Could not retrieve PID or Encounter.") . "</div>";
    exit;
}

// Get patient info
$paciente = QueryUtils::querySingleRow("SELECT CONCAT(fname, ' ', lname) AS full_name, pubpid, DOB FROM patient_data WHERE pid = ?", array($pid));
// Calculate age
$age = '';
$dob_val = ($paciente ?? [])['DOB'] ?? '';
if ($dob_val !== '') {
    $dob = new DateTime($dob_val);
    $age  = (new DateTime())->diff($dob)->y . ' ' . xlt('years');
}

if ($id > 0) {
    $result = sqlStatement("SELECT * FROM form_curaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
} else {
    $result = sqlStatement("SELECT * FROM form_curaciones WHERE pid = ? AND encounter = ? ORDER BY date DESC", array($pid, $encounter));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Nursing Wound Care'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .curaciones-view *, .curaciones-view *::before, .curaciones-view *::after { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 40px; }
        h2 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid; font-size: 24px; }
        .info-paciente { padding: 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid rgba(128,128,128,0.25); }
        .info-paciente h3 { font-size: 16px; margin-bottom: 15px; font-weight: 600; }
        .info-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px; }
        .info-item { padding: 10px 15px; border-radius: 3px; border: 1px solid rgba(128,128,128,0.2); }
        .info-item strong { font-size: 12px; display: block; margin-bottom: 5px; }
        .info-item span   { font-size: 15px; font-weight: 500; }
        .registro-card { border: 2px solid rgba(128,128,128,0.25); border-radius: 5px; padding: 25px; margin-bottom: 25px; }
        .registro-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 2px solid rgba(128,128,128,0.2); margin-bottom: 20px; }
        .registro-fecha { display: flex; align-items: center; gap: 15px; }
        .fecha-principal { font-size: 18px; font-weight: 600; }
        .hora-principal  { font-size: 14px; opacity: 0.6; }
        .registro-acciones { display: flex; gap: 8px; }
        .registro-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .info-box-small { padding: 12px 15px; border-radius: 3px; border: 1px solid rgba(128,128,128,0.2); }
        .info-box-small strong { font-size: 12px; display: block; margin-bottom: 5px; font-weight: 600; }
        .info-box-small span   { font-size: 16px; font-weight: 500; }
        .seccion-titulo { font-size: 15px; font-weight: 700; margin: 20px 0 15px 0; padding: 10px 15px; border-left: 5px solid #667eea; border-radius: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .curacion-detalle { border: 1px solid rgba(128,128,128,0.2); border-radius: 3px; margin-bottom: 12px; overflow: hidden; }
        .curacion-header  { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; }
        .curacion-header.si { background-color: rgba(40,167,69,0.15); border-left: 4px solid #28a745; }
        .curacion-header.no { border-left: 4px solid #6c757d; }
        .curacion-nombre  { font-size: 14px; font-weight: 600; }
        .estado-badge { padding: 6px 14px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .estado-badge.si { background-color: #28a745; color: white; }
        .estado-badge.no { background-color: #6c757d; color: white; }
        .curacion-obs { padding: 18px 20px; }
        .curacion-obs.con-contenido { background: rgba(13,110,253,0.08); border-top: 3px solid #0d6efd; }
        .curacion-obs h5 { font-size: 12px; opacity: 0.6; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
        .curacion-obs p  { font-size: 14px; line-height: 1.7; white-space: pre-wrap; }
        .no-registros { text-align: center; padding: 80px 40px; border-radius: 15px; margin-top: 20px; opacity: 0.6; }
        @media print {
            body { background: white; padding: 0; }
            .registro-acciones, .form-group { display: none !important; }
        }
    </style>
</head>
<body class="body_top">
<div class="curaciones-view container">
    <h2><?php echo $id > 0 ? xlt('Wound Care Detail') : xlt('Wound Care List'); ?></h2>

    <div class="info-paciente">
        <h3><?php echo xlt('Patient Information'); ?></h3>
        <div class="info-row">
            <div class="info-item">
                <strong><?php echo xlt('Patient'); ?>:</strong>
                <span><?php echo text(($paciente ?? [])['full_name'] ?? xlt('Not available')); ?></span>
            </div>
            <div class="info-item">
                <strong><?php echo xlt('ID'); ?>:</strong>
                <span><?php echo text(($paciente ?? [])['pubpid'] ?? xlt('Not available')); ?></span>
            </div>
            <?php if ($age !== '') :
                ?>
            <div class="info-item">
                <strong><?php echo xlt('Age'); ?>:</strong>
                <span><?php echo text($age); ?></span>
            </div>
                <?php
            endif; ?>
        </div>
    </div>

    <?php
    if (sqlNumRows($result) == 0) {
        echo "<div class='no-registros'><h3>" . xlt('No wound care records found') . "</h3></div>";
    }

    $fields = [
        'herida_operatoria'  => xlt('Surgical Wound'),
        'traqueostomia'      => xlt('Tracheostomy'),
        'ostomias'           => xlt('Ostomies'),
        'escaras'            => xlt('Pressure Sores'),
        'via_venosa_central' => xlt('Central Venous Line'),
        'via_venosa'         => xlt('Peripheral IV Line'),
    ];

    while ($row = sqlFetchArray($result)) :
        ?>

    <div class="registro-card">
        <div class="registro-header">
            <div class="registro-fecha">
                <span class="fecha-principal"><?php echo text(date('d/m/Y', strtotime((string)($row['date'] ?? '')))); ?></span>
                <span class="hora-principal"><?php echo text(date('H:i', strtotime((string)($row['date'] ?? '')))); ?></span>
            </div>
            <div class="registro-acciones">
                <a href="<?php echo attr(OEGlobalsBag::getInstance()->getString('webroot') . '/interface/forms/curaciones/new.php?pid=' . $pid . '&encounter=' . $encounter . '&id=' . (int)($row['id'] ?? 0)); ?>"
                   class="btn btn-primary mr-1" onclick="top.restoreSession()">
                    <i class="fas fa-pen mr-1"></i><?php echo xlt('Edit'); ?>
                </a>
                <a href="<?php echo attr(OEGlobalsBag::getInstance()->getString('webroot') . '/interface/forms/curaciones/print.php?pid=' . $pid . '&encounter=' . $encounter . '&id=' . (int)($row['id'] ?? 0)); ?>"
                   target="_blank" class="btn btn-success" onclick="top.restoreSession()">
                    <i class="fas fa-print mr-1"></i><?php echo xlt('Print'); ?>
                </a>
            </div>
        </div>

        <div class="registro-info">
            <div class="info-box-small">
                <strong><?php echo xlt('Care Time'); ?>:</strong>
                <span><?php echo text((string)($row['hora_operacion'] ?? 'N/A')); ?></span>
            </div>
            <div class="info-box-small">
                <strong><?php echo xlt('User'); ?>:</strong>
                <span><?php echo text((string)($row['user'] ?? 'N/A')); ?></span>
            </div>
        </div>

        <div class="seccion-titulo"><?php echo xlt('Wound Care Detail'); ?></div>

        <?php foreach ($fields as $campo => $label) :
            $val = (int)($row[$campo] ?? 0);
            $obs = $row['obs_' . $campo] ?? '';
            $cls = $val ? 'si' : 'no';
            ?>
        <div class="curacion-detalle">
            <div class="curacion-header <?php echo $cls; ?>">
                <div class="curacion-nombre"><?php echo text($label); ?></div>
                <span class="estado-badge <?php echo $cls; ?>">
                    <?php echo $val ? xlt('Yes') : xlt('No'); ?>
                </span>
            </div>
            <div class="curacion-obs <?php echo ($obs !== '') ? 'con-contenido' : ''; ?>">
                <h5><?php echo xlt('Observations'); ?></h5>
                <p><?php echo ($obs !== '') ? nl2br(text($obs)) : xlt('No observations recorded'); ?></p>
            </div>
        </div>
            <?php
        endforeach; ?>

    </div>

        <?php
    endwhile; ?>

    <div class="form-group mt-3">
        <button type="button" onclick="history.back()" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left mr-1"></i><?php echo xlt('Back'); ?>
        </button>
    </div>
</div>

</body>
</html>
