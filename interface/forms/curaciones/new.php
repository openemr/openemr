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
/** @var string $srcdir */
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Get parameters
$pid       = is_numeric($v = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0
    ?: (is_numeric($v = $session->get('pid')) ? (int) $v : 0);
$encounter = is_numeric($v = filter_input(INPUT_GET, 'encounter', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0
    ?: (is_numeric($v = $session->get('encounter')) ? (int) $v : 0);
$id        = is_numeric($v = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)) ? (int) $v : 0;

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
    /** @var array<string, string|int|null>|false $row */
    $row = QueryUtils::querySingleRow("SELECT * FROM form_curaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
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

    <form method="POST" action="save.php" id="formCuraciones" onsubmit="top.restoreSession();">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <input type="hidden" name="pid"       value="<?php echo attr((string)$pid); ?>">
        <input type="hidden" name="encounter" value="<?php echo attr((string)$encounter); ?>">
        <?php if ($is_edit) : ?>
        <input type="hidden" name="id" value="<?php echo attr((string)$id); ?>">
        <?php endif; ?>

        <?php
        $fields = [
            'herida_operatoria'  => ['label' => xlt('Surgical Wound'),     'val' => $herida_operatoria,      'obs' => $obs_herida_operatoria],
            'traqueostomia'      => ['label' => xlt('Tracheostomy'),        'val' => $traqueostomia,          'obs' => $obs_traqueostomia],
            'ostomias'           => ['label' => xlt('Ostomies'),            'val' => $ostomias,               'obs' => $obs_ostomias],
            'escaras'            => ['label' => xlt('Pressure Sores'),      'val' => $escaras,                'obs' => $obs_escaras],
            'via_venosa_central' => ['label' => xlt('Central Venous Line'), 'val' => $via_venosa_central,     'obs' => $obs_via_venosa_central],
            'via_venosa'         => ['label' => xlt('Peripheral IV Line'),  'val' => $via_venosa,             'obs' => $obs_via_venosa],
        ];
        foreach ($fields as $name => $meta) : ?>
        <div class="nurs-section">
            <h6 class="font-weight-bold"><?php echo text((string)$meta['label']); ?></h6>
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
                      placeholder="<?php echo attr(xlt('Observations...')); ?>"><?php echo text((string)$meta['obs']); ?></textarea>
        </div>
        <?php endforeach; ?>

        <div class="form-group">
            <label for="hora_operacion" class="font-weight-bold"><?php echo xlt('Care Time'); ?>:</label>
            <input type="time" name="hora_operacion" id="hora_operacion"
                   class="form-control w-auto" value="<?php echo attr((string)$hora_operacion); ?>">
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
    var horaInput = document.getElementById('hora_operacion');
    if (<?php echo $is_edit ? 'false' : 'true'; ?> && horaInput.value === '') {
        var now = new Date();
        horaInput.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }
});
</script>
</body>
</html>
