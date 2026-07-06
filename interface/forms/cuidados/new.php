<?php

/**
 * Nursing Care Bundle Form - new.php
 * Care bundle record for ventilated inpatients.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
/** @var string $srcdir */
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Get parameters
$pid       = (int) filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT)
    ?: (int) ($session->get('pid') ?? 0);
$encounter = (int) filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)
    ?: (int) ($session->get('encounter') ?? 0);
$id        = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$pid || !$encounter) {
    die(xlt("Error: Missing required parameters (PID or Encounter)"));
}

$is_edit = ($id > 0);

// Initialize field variables
$posicion_paciente        = '';
$obs_posicion_paciente    = '';
$enjuague_bucal           = 0;
$obs_enjuague_bucal       = '';
$higiene_manos            = 0;
$obs_higiene_manos        = '';
$aspirado_secreciones     = 0;
$obs_aspirado_secreciones = '';
$suspension_sedacion      = 0;
$obs_suspension_sedacion  = '';
$medicion_cuff            = 0;
$obs_medicion_cuff        = '';
$hora_cuidado             = '';

// Load existing data in edit mode
if ($is_edit) {
    $row = QueryUtils::querySingleRow("SELECT * FROM form_cuidados WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if ($row) {
        $posicion_paciente        = $row['posicion_paciente']        ?? '';
        $obs_posicion_paciente    = $row['obs_posicion_paciente']    ?? '';
        $enjuague_bucal           = (int)($row['enjuague_bucal']     ?? 0);
        $obs_enjuague_bucal       = $row['obs_enjuague_bucal']       ?? '';
        $higiene_manos            = (int)($row['higiene_manos']      ?? 0);
        $obs_higiene_manos        = $row['obs_higiene_manos']        ?? '';
        $aspirado_secreciones     = (int)($row['aspirado_secreciones']  ?? 0);
        $obs_aspirado_secreciones = $row['obs_aspirado_secreciones'] ?? '';
        $suspension_sedacion      = (int)($row['suspension_sedacion']   ?? 0);
        $obs_suspension_sedacion  = $row['obs_suspension_sedacion']  ?? '';
        $medicion_cuff            = (int)($row['medicion_cuff']      ?? 0);
        $obs_medicion_cuff        = $row['obs_medicion_cuff']        ?? '';
        $hora_cuidado             = $row['hora_cuidado']             ?? '';
    } else {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
}

$posicion_options = ['DLI', 'DLD', 'DS', 'DV', 'CABECERA 30°'];
$page_title = $is_edit ? xlt('Edit Care Bundle') : xlt('New Care Bundle');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo text($page_title); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .nurs-section {
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .nurs-section-posicion {
            border-left: 4px solid #1976d2;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="body_top">
<div class="container-fluid mt-3">
    <div class="row mb-3">
        <div class="col-12">
            <h4>
                <?php echo text($page_title); ?>
                <span class="badge badge-<?php echo $is_edit ? 'warning' : 'success'; ?> ml-2">
                    <?php echo $is_edit ? xlt('Edit Mode') : xlt('Create Mode'); ?>
                </span>
            </h4>
            <small class="text-muted"><?php echo xlt('Encounter'); ?>: <?php echo text((string)$encounter); ?></small>
        </div>
    </div>

    <form method="POST" action="save.php" id="formCuidados" onsubmit="top.restoreSession();">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <input type="hidden" name="pid"       value="<?php echo attr((string)$pid); ?>">
        <input type="hidden" name="encounter" value="<?php echo attr((string)$encounter); ?>">
        <?php if ($is_edit) : ?>
        <input type="hidden" name="id" value="<?php echo attr((string)$id); ?>">
        <?php endif; ?>

        <!-- PATIENT POSITION -->
        <div class="nurs-section-posicion">
            <h6 class="font-weight-bold"><?php echo xlt('Patient Position'); ?></h6>
            <div class="mb-2">
                <?php foreach ($posicion_options as $opt) : ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="posicion_paciente"
                           id="pos_<?php echo attr($opt); ?>"
                           value="<?php echo attr($opt); ?>"
                           <?php echo ($posicion_paciente === $opt) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="pos_<?php echo attr($opt); ?>">
                        <?php echo text($opt); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <textarea name="obs_posicion_paciente" class="form-control" rows="2"
                      placeholder="<?php echo attr(xlt('Observations...')); ?>"><?php echo text($obs_posicion_paciente); ?></textarea>
        </div>

        <?php
        $bool_fields = [
            'enjuague_bucal'       => ['label' => xlt('Oral Rinse'),                                             'val' => $enjuague_bucal,           'obs' => $obs_enjuague_bucal],
            'higiene_manos'        => ['label' => xlt('Hand Hygiene Pre/Post Suctioning'),                       'val' => $higiene_manos,            'obs' => $obs_higiene_manos],
            'aspirado_secreciones' => ['label' => xlt('Secretion Suctioning with Gloves and Assistant'),         'val' => $aspirado_secreciones,     'obs' => $obs_aspirado_secreciones],
            'suspension_sedacion'  => ['label' => xlt('Daily Sedation Suspension and Extubation Evaluation'),    'val' => $suspension_sedacion,      'obs' => $obs_suspension_sedacion],
            'medicion_cuff'        => ['label' => xlt('Cuff Pressure Measurement'),                              'val' => $medicion_cuff,            'obs' => $obs_medicion_cuff],
        ];
        foreach ($bool_fields as $name => $meta) : ?>
        <div class="nurs-section">
            <h6 class="font-weight-bold"><?php echo text($meta['label']); ?></h6>
            <div class="mb-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="<?php echo attr($name); ?>"
                           id="<?php echo attr($name); ?>_1" value="1"
                           <?php echo ($meta['val'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="<?php echo attr($name); ?>_1">
                        <?php echo xlt('Yes'); ?>
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="<?php echo attr($name); ?>"
                           id="<?php echo attr($name); ?>_0" value="0"
                           <?php echo ($meta['val'] == 0) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="<?php echo attr($name); ?>_0">
                        <?php echo xlt('No'); ?>
                    </label>
                </div>
            </div>
            <textarea name="<?php echo attr('obs_' . $name); ?>" class="form-control" rows="2"
                      placeholder="<?php echo attr(xlt('Observations...')); ?>"><?php echo text($meta['obs']); ?></textarea>
        </div>
        <?php endforeach; ?>

        <div class="form-group">
            <label for="hora_cuidado" class="font-weight-bold"><?php echo xlt('Care Time'); ?>:</label>
            <input type="time" name="hora_cuidado" id="hora_cuidado"
                   class="form-control w-auto" value="<?php echo attr($hora_cuidado); ?>">
        </div>

        <div class="form-group mt-3">
            <button type="submit" onclick="top.restoreSession()" class="btn btn-primary">
                <i class="fas fa-check mr-1"></i><?php echo $is_edit ? xlt('Save Changes') : xlt('Save'); ?>
            </button>
            <button type="button" onclick="history.back()" class="btn btn-outline-secondary ml-2">
                <i class="fas fa-times mr-1"></i><?php echo xlt('Cancel'); ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var horaInput = document.getElementById('hora_cuidado');
    if (<?php echo $is_edit ? 'false' : 'true'; ?> && horaInput.value === '') {
        var now = new Date();
        horaInput.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }
});
</script>
</body>
</html>
