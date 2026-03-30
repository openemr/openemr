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
require_once("$srcdir/api.inc.php");
use OpenEMR\Common\Csrf\CsrfUtils;
// Get parameters
$pid       = isset($_GET['pid'])       ? (int)$_GET['pid']       : (int)($_SESSION['pid'] ?? 0);
$encounter = isset($_GET['encounter']) ? (int)$_GET['encounter'] : (int)($_SESSION['encounter'] ?? 0);
$id        = isset($_GET['id'])        ? (int)$_GET['id']        : 0;
if (!$pid || !$encounter) {
    die(xlt("Error: Missing required parameters (PID or Encounter)"));
}

$is_edit = ($id > 0);
// Initialize all field variables
$modo_ventilacion            = '';
$obs_modo                    = '';
$presion                     = 0;
$obs_presion                  = '';
$volumen                     = 0;
$obs_volumen                  = '';
$simv                        = 0;
$obs_simv                     = '';
$psv                         = 0;
$obs_psv                      = '';
$otros                       = 0;
$obs_otros                    = '';
$frecuencia_respiratoria     = 0;
$obs_frecuencia_respiratoria  = '';
$p_inspiratorio              = 0;
$obs_p_inspiratorio           = '';
$p_media                     = 0;
$obs_p_media                  = '';
$p_max                       = 0;
$obs_p_max                    = '';
$chst                        = 0;
$obs_chst                     = '';
$disparo                     = 0;
$obs_disparo                  = '';
$fvt                         = 0;
$obs_fvt                      = '';
$vol_tidal                   = 0;
$obs_vol_tidal                = '';
$vm_programado               = 0;
$obs_vm_programado            = '';
$petco2                      = 0;
$obs_petco2                   = '';
$vdvt                        = 0;
$obs_vdvt                     = '';
$ko2                         = 0;
$obs_ko2                      = '';
$hora_registro               = '';
// Load existing data in edit mode
if ($is_edit) {
    $row = sqlQuery("SELECT * FROM form_registro_vm WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
    if ($row) {
        $modo_ventilacion            = $row['modo_ventilacion']            ?? '';
        $obs_modo                    = $row['obs_modo']                    ?? '';
        $presion                     = (int)($row['presion']                     ?? 0);
        $obs_presion                  = $row['obs_presion']                  ?? '';
        $volumen                     = (int)($row['volumen']                     ?? 0);
        $obs_volumen                  = $row['obs_volumen']                  ?? '';
        $simv                        = (int)($row['simv']                        ?? 0);
        $obs_simv                     = $row['obs_simv']                     ?? '';
        $psv                         = (int)($row['psv']                         ?? 0);
        $obs_psv                      = $row['obs_psv']                      ?? '';
        $otros                       = (int)($row['otros']                       ?? 0);
        $obs_otros                    = $row['obs_otros']                    ?? '';
        $frecuencia_respiratoria     = (int)($row['frecuencia_respiratoria']     ?? 0);
        $obs_frecuencia_respiratoria  = $row['obs_frecuencia_respiratoria']  ?? '';
        $p_inspiratorio              = (int)($row['p_inspiratorio']              ?? 0);
        $obs_p_inspiratorio           = $row['obs_p_inspiratorio']           ?? '';
        $p_media                     = (int)($row['p_media']                     ?? 0);
        $obs_p_media                  = $row['obs_p_media']                  ?? '';
        $p_max                       = (int)($row['p_max']                       ?? 0);
        $obs_p_max                    = $row['obs_p_max']                    ?? '';
        $chst                        = (int)($row['chst']                        ?? 0);
        $obs_chst                     = $row['obs_chst']                     ?? '';
        $disparo                     = (int)($row['disparo']                     ?? 0);
        $obs_disparo                  = $row['obs_disparo']                  ?? '';
        $fvt                         = (int)($row['fvt']                         ?? 0);
        $obs_fvt                      = $row['obs_fvt']                      ?? '';
        $vol_tidal                   = (int)($row['vol_tidal']                   ?? 0);
        $obs_vol_tidal                = $row['obs_vol_tidal']                ?? '';
        $vm_programado               = (int)($row['vm_programado']               ?? 0);
        $obs_vm_programado            = $row['obs_vm_programado']            ?? '';
        $petco2                      = (int)($row['petco2']                      ?? 0);
        $obs_petco2                   = $row['obs_petco2']                   ?? '';
        $vdvt                        = (int)($row['vdvt']                        ?? 0);
        $obs_vdvt                     = $row['obs_vdvt']                     ?? '';
        $ko2                         = (int)($row['ko2']                         ?? 0);
        $obs_ko2                      = $row['obs_ko2']                      ?? '';
        $hora_registro               = $row['hora_registro']                     ?? '';
    } else {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
}

// Ventilation mode options (stored values / display labels)
$modo_options = [
    'ESPONTANEA'         => xlt('Spontaneous'),
    'VENTILACION MECANICA' => xlt('Mechanical Ventilation'),
];
$page_title = $is_edit ? xlt('Edit Ventilation Record') : xlt('New Ventilation Record');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo text($page_title); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .registro-vm-form * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header .subtitle { font-size: 14px; opacity: 0.9; }

        .form-content { padding: 40px; }

        .form-group {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group-modo {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #1976d2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group h3,
        .form-group-modo h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .radio-container {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .radio-container label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            color: #333;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }

        .radio-container label:hover { background-color: #f8f9fa; }
        .radio-container input[type="radio"] { width: 18px; height: 18px; cursor: pointer; }

        .observaciones {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.3s;
        }

        .observaciones:focus { outline: none; border-color: #3498db; }

        .hora-grupo { margin-top: 20px; }
        .hora-grupo label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .hora-grupo input[type="time"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .hora-grupo input[type="time"]:focus { outline: none; border-color: #3498db; }

        .form-actions { display: flex; gap: 15px; margin-top: 30px; }

        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }

        .mode-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .mode-create { background: #28a745; color: white; }
        .mode-edit   { background: #ffc107; color: #000; }
    </style>
</head>
<body>
<div class="registro-vm-form container">
    <div class="header">
        <h1>
            <?php echo text($page_title); ?>
            <span class="mode-badge <?php echo attr($is_edit ? 'mode-edit' : 'mode-create'); ?>">
                <?php echo $is_edit ? xlt('Edit Mode') : xlt('Create Mode'); ?>
            </span>
        </h1>
        <div class="subtitle">
            <?php echo xlt('Encounter'); ?>: <?php echo text($encounter); ?>
        </div>
    </div>

    <div class="form-content">
        <form method="POST" action="save.php" id="formRegistroVM" onsubmit="top.restoreSession();">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
            <input type="hidden" name="pid"       value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <?php if ($is_edit) :
                ?>
            <input type="hidden" name="id" value="<?php echo attr($id); ?>">
                <?php
            endif; ?>

            <!-- VENTILATION MODE (multi-option) -->
            <div class="form-group-modo">
                <h3><?php echo xlt('Ventilation Mode'); ?></h3>
                <div class="radio-container">
                    <?php foreach ($modo_options as $val => $label) :
                        ?>
                    <label>
                        <input type="radio" name="modo_ventilacion"
                               value="<?php echo attr($val); ?>"
                               <?php echo ($modo_ventilacion === $val) ? 'checked' : ''; ?>>
                        <?php echo text($label); ?>
                    </label>
                        <?php
                    endforeach; ?>
                </div>
                <textarea name="obs_modo" class="observaciones"
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
            foreach ($bool_fields as $name => $meta) :
                ?>
            <div class="form-group">
                <h3><?php echo text($meta['label']); ?></h3>
                <div class="radio-container">
                    <label>
                        <input type="radio" name="<?php echo attr($name); ?>" value="1"
                               <?php echo ($meta['val'] == 1) ? 'checked' : ''; ?>>
                        <?php echo xlt('Yes'); ?>
                    </label>
                    <label>
                        <input type="radio" name="<?php echo attr($name); ?>" value="0"
                               <?php echo ($meta['val'] == 0) ? 'checked' : ''; ?>>
                        <?php echo xlt('No'); ?>
                    </label>
                </div>
                <textarea name="<?php echo attr('obs_' . $name); ?>" class="observaciones"
                          placeholder="<?php echo attr(xlt('Observations...')); ?>"><?php echo text($meta['obs']); ?></textarea>
            </div>
                <?php
            endforeach; ?>

            <!-- RECORD TIME -->
            <div class="form-group">
                <div class="hora-grupo">
                    <label for="hora_registro"><?php echo xlt('Record Time'); ?>:</label>
                    <input type="time" name="hora_registro" id="hora_registro"
                           value="<?php echo attr($hora_registro); ?>">
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $is_edit ? xlt('Save Changes') : xlt('Save'); ?>
                </button>
                <a href="<?php echo attr($GLOBALS['webroot'] . '/interface/tableros/lista_internados.php'); ?>"
                   class="btn btn-secondary">
                    <?php echo xlt('Cancel'); ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var isEdit    = <?php echo ($is_edit ? 'true' : 'false'); ?>;
        var horaInput = document.getElementById('hora_registro');

        if (!isEdit && horaInput.value === '') {
            var now = new Date();
            horaInput.value =
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0');
        }
    });
</script>
</body>
</html>
