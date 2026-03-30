<?php

/**
 * Nursing Wound Care Form - new.php
 * Wound care assessment form for inpatients.
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
// Initialize field variables
$herida_operatoria      = 0;
$obs_herida_operatoria  = '';
$traqueostomia          = 0;
$obs_traqueostomia      = '';
$ostomias               = 0;
$obs_ostomias           = '';
$escaras                = 0;
$obs_escaras            = '';
$via_venosa_central     = 0;
$obs_via_venosa_central = '';
$via_venosa             = 0;
$obs_via_venosa         = '';
$hora_operacion         = '';
// Load existing data in edit mode
if ($is_edit) {
    $row = sqlQuery("SELECT * FROM form_curaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
    if ($row) {
        $herida_operatoria      = (int)($row['herida_operatoria']      ?? 0);
        $obs_herida_operatoria  = $row['obs_herida_operatoria']         ?? '';
        $traqueostomia          = (int)($row['traqueostomia']          ?? 0);
        $obs_traqueostomia      = $row['obs_traqueostomia']             ?? '';
        $ostomias               = (int)($row['ostomias']               ?? 0);
        $obs_ostomias           = $row['obs_ostomias']                  ?? '';
        $escaras                = (int)($row['escaras']                ?? 0);
        $obs_escaras            = $row['obs_escaras']                   ?? '';
        $via_venosa_central     = (int)($row['via_venosa_central']     ?? 0);
        $obs_via_venosa_central = $row['obs_via_venosa_central']        ?? '';
        $via_venosa             = (int)($row['via_venosa']             ?? 0);
        $obs_via_venosa         = $row['obs_via_venosa']                ?? '';
        $hora_operacion         = $row['hora_operacion']                ?? '';
    } else {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
}

$page_title = $is_edit ? xlt('Edit Wound Care') : xlt('New Wound Care');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo text($page_title); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .curaciones-form * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .form-group h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .radio-container {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
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
<div class="curaciones-form container">
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
        <form method="POST" action="save.php" id="formCuraciones" onsubmit="top.restoreSession();">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
            <input type="hidden" name="pid"       value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <?php if ($is_edit) :
                ?>
            <input type="hidden" name="id" value="<?php echo attr($id); ?>">
                <?php
            endif; ?>

            <?php
            $fields = [
                'herida_operatoria'  => ['label' => xlt('Surgical Wound'),       'val' => $herida_operatoria,      'obs' => $obs_herida_operatoria],
                'traqueostomia'      => ['label' => xlt('Tracheostomy'),          'val' => $traqueostomia,          'obs' => $obs_traqueostomia],
                'ostomias'           => ['label' => xlt('Ostomies'),              'val' => $ostomias,               'obs' => $obs_ostomias],
                'escaras'            => ['label' => xlt('Pressure Sores'),        'val' => $escaras,                'obs' => $obs_escaras],
                'via_venosa_central' => ['label' => xlt('Central Venous Line'),   'val' => $via_venosa_central,     'obs' => $obs_via_venosa_central],
                'via_venosa'         => ['label' => xlt('Peripheral IV Line'),    'val' => $via_venosa,             'obs' => $obs_via_venosa],
            ];
            foreach ($fields as $name => $meta) :
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

            <!-- CARE TIME -->
            <div class="form-group">
                <div class="hora-grupo">
                    <label for="hora_operacion"><?php echo xlt('Care Time'); ?>:</label>
                    <input type="time" name="hora_operacion" id="hora_operacion"
                           value="<?php echo attr($hora_operacion); ?>">
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
        var horaInput = document.getElementById('hora_operacion');

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
