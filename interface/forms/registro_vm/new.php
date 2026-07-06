<?php

/**
 * Mechanical Ventilation Record Form - new.php
 * Ventilator parameters record for mechanically ventilated patients.
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
    ?: (int)($session->get('pid') ?? 0);
$encounter = (int) filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)
    ?: (int)($session->get('encounter') ?? 0);
$id        = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$pid || !$encounter) {
    die(xlt("Error: Missing required parameters (PID or Encounter)"));
}

$is_edit = ($id > 0);

// Initialize all field variables
$modo_ventilacion            = '';
$obs_modo                    = '';
$presion                     = 0;
$obs_presion                 = '';
$volumen                     = 0;
$obs_volumen                 = '';
$simv                        = 0;
$obs_simv                    = '';
$psv                         = 0;
$obs_psv                     = '';
$otros                       = 0;
$obs_otros                   = '';
$frecuencia_respiratoria     = 0;
$obs_frecuencia_respiratoria = '';
$p_inspiratorio              = 0;
$obs_p_inspiratorio          = '';
$p_media                     = 0;
$obs_p_media                 = '';
$p_max                       = 0;
$obs_p_max                   = '';
$chst                        = 0;
$obs_chst                    = '';
$disparo                     = 0;
$obs_disparo                 = '';
$fvt                         = 0;
$obs_fvt                     = '';
$vol_tidal                   = 0;
$obs_vol_tidal               = '';
$vm_programado               = 0;
$obs_vm_programado           = '';
$petco2                      = 0;
$obs_petco2                  = '';
$vdvt                        = 0;
$obs_vdvt                    = '';
$ko2                         = 0;
$obs_ko2                     = '';
$hora_registro               = '';

// Load existing data in edit mode
if ($is_edit) {
    $row = QueryUtils::querySingleRow("SELECT * FROM form_registro_vm WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if ($row) {
        $modo_ventilacion            = $row['modo_ventilacion']            ?? '';
        $obs_modo                    = $row['obs_modo']                    ?? '';
        $presion                     = (int)($row['presion']               ?? 0);
        $obs_presion                 = $row['obs_presion']                 ?? '';
        $volumen                     = (int)($row['volumen']               ?? 0);
        $obs_volumen                 = $row['obs_volumen']                 ?? '';
        $simv                        = (int)($row['simv']                  ?? 0);
        $obs_simv                    = $row['obs_simv']                    ?? '';
        $psv                         = (int)($row['psv']                   ?? 0);
        $obs_psv                     = $row['obs_psv']                     ?? '';
        $otros                       = (int)($row['otros']                 ?? 0);
        $obs_otros                   = $row['obs_otros']                   ?? '';
        $frecuencia_respiratoria     = (int)($row['frecuencia_respiratoria']  ?? 0);
        $obs_frecuencia_respiratoria = $row['obs_frecuencia_respiratoria'] ?? '';
        $p_inspiratorio              = (int)($row['p_inspiratorio']        ?? 0);
        $obs_p_inspiratorio          = $row['obs_p_inspiratorio']          ?? '';
        $p_media                     = (int)($row['p_media']               ?? 0);
        $obs_p_media                 = $row['obs_p_media']                 ?? '';
        $p_max                       = (int)($row['p_max']                 ?? 0);
        $obs_p_max                   = $row['obs_p_max']                   ?? '';
        $chst                        = (int)($row['chst']                  ?? 0);
        $obs_chst                    = $row['obs_chst']                    ?? '';
        $disparo                     = (int)($row['disparo']               ?? 0);
        $obs_disparo                 = $row['obs_disparo']                 ?? '';
        $fvt                         = (int)($row['fvt']                   ?? 0);
        $obs_fvt                     = $row['obs_fvt']                     ?? '';
        $vol_tidal                   = (int)($row['vol_tidal']             ?? 0);
        $obs_vol_tidal               = $row['obs_vol_tidal']               ?? '';
        $vm_programado               = (int)($row['vm_programado']         ?? 0);
        $obs_vm_programado           = $row['obs_vm_programado']           ?? '';
        $petco2                      = (int)($row['petco2']                ?? 0);
        $obs_petco2                  = $row['obs_petco2']                  ?? '';
        $vdvt                        = (int)($row['vdvt']                  ?? 0);
        $obs_vdvt                    = $row['obs_vdvt']                    ?? '';
        $ko2                         = (int)($row['ko2']                   ?? 0);
        $obs_ko2                     = $row['obs_ko2']                     ?? '';
        $hora_registro               = $row['hora_registro']               ?? '';
    } else {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
}

$modo_options = [
    'ESPONTANEA'           => xlt('Spontaneous'),
    'VENTILACION MECANICA' => xlt('Mechanical Ventilation'),
];
$page_title = $is_edit ? xlt('Edit Ventilation Record') : xlt('New Ventilation Record');
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
        .nurs-section-modo {
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

    <form method="POST" action="save.php" id="formRegistroVM" onsubmit="top.restoreSession();">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <input type="hidden" name="pid"       value="<?php echo attr((string)$pid); ?>">
        <input type="hidden" name="encounter" value="<?php echo attr((string)$encounter); ?>">
        <?php if ($is_edit) : ?>
        <input type="hidden" name="id" value="<?php echo attr((string)$id); ?>">
        <?php endif; ?>

        <!-- VENTILATION MODE -->
        <div class="nurs-section-modo">
            <h6 class="font-weight-bold"><?php echo xlt('Ventilation Mode'); ?></h6>
            <div class="mb-2">
                <?php foreach ($modo_options as $val => $label) : ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="modo_ventilacion"
                           id="modo_<?php echo attr($val); ?>"
                           value="<?php echo attr($val); ?>"
                           <?php echo ($modo_ventilacion === $val) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="modo_<?php echo attr($val); ?>">
                        <?php echo text($label); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <textarea name="obs_modo" class="form-control" rows="2"
                      placeholder="<?php echo attr(xlt('Observations...')); ?>"><?php echo text($obs_modo); ?></textarea>
        </div>

        <?php
        $bool_fields = [
            'presion'                 => ['label' => xlt('Pressure'),                      'val' => $presion,                 'obs' => $obs_presion],
            'volumen'                 => ['label' => xlt('Volume'),                        'val' => $volumen,                 'obs' => $obs_volumen],
            'simv'                    => ['label' => 'SIMV',                               'val' => $simv,                    'obs' => $obs_simv],
            'psv'                     => ['label' => 'PSV',                                'val' => $psv,                     'obs' => $obs_psv],
            'otros'                   => ['label' => xlt('Other'),                         'val' => $otros,                   'obs' => $obs_otros],
            'frecuencia_respiratoria' => ['label' => xlt('Respiratory Rate'),              'val' => $frecuencia_respiratoria, 'obs' => $obs_frecuencia_respiratoria],
            'p_inspiratorio'          => ['label' => xlt('P.Inspiratory / T.Inspiratory'), 'val' => $p_inspiratorio,          'obs' => $obs_p_inspiratorio],
            'p_media'                 => ['label' => xlt('P.Mean / PEEP'),                 'val' => $p_media,                 'obs' => $obs_p_media],
            'p_max'                   => ['label' => xlt('P.Max / P.Plateau'),             'val' => $p_max,                   'obs' => $obs_p_max],
            'chst'                    => ['label' => 'CHST / CDIN',                        'val' => $chst,                    'obs' => $obs_chst],
            'disparo'                 => ['label' => xlt('Trigger F/P'),                   'val' => $disparo,                 'obs' => $obs_disparo],
            'fvt'                     => ['label' => 'F / VT',                             'val' => $fvt,                     'obs' => $obs_fvt],
            'vol_tidal'               => ['label' => xlt('Tidal Volume / Flow'),           'val' => $vol_tidal,               'obs' => $obs_vol_tidal],
            'vm_programado'           => ['label' => xlt('Programmed/Measured MV'),        'val' => $vm_programado,           'obs' => $obs_vm_programado],
            'petco2'                  => ['label' => 'PETCO2',                             'val' => $petco2,                  'obs' => $obs_petco2],
            'vdvt'                    => ['label' => 'VD / VT',                            'val' => $vdvt,                    'obs' => $obs_vdvt],
            'ko2'                     => ['label' => 'KO2',                                'val' => $ko2,                     'obs' => $obs_ko2],
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
            <label for="hora_registro" class="font-weight-bold"><?php echo xlt('Record Time'); ?>:</label>
            <input type="time" name="hora_registro" id="hora_registro"
                   class="form-control w-auto" value="<?php echo attr($hora_registro); ?>">
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
    var horaInput = document.getElementById('hora_registro');
    if (<?php echo $is_edit ? 'false' : 'true'; ?> && horaInput.value === '') {
        var now = new Date();
        horaInput.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }
});
</script>
</body>
</html>
