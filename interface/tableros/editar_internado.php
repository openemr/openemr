<?php

/**
 * Inpatient Admission Form — new and edit mode.
 *
 * Usage:
 *   New admission : editar_internado.php?pid=X
 *   Edit admission: editar_internado.php?id=X  (form_encounter.id)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    g0tazu
 * @copyright Copyright (c) 2026 g0tazu
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");
/** @var string $srcdir */
/** @var string $web_root */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\OeUI\OemrUI;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('patients', 'med')) {
    die(xlt('Access denied'));
}

// Determine mode
$encounter_id = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$pid_new      = (int) filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_NUMBER_INT);
$viewmode     = ($encounter_id > 0);

// Data containers
/** @var array<string,mixed>|null $encounter_row */
$encounter_row = null;
$nursing_row   = [];
/** @var array<string,mixed>|null $patient */
$patient       = null;

if ($viewmode) {
    $encounter_row = QueryUtils::querySingleRow("SELECT * FROM form_encounter WHERE id = ?", [$encounter_id]);
    if (!$encounter_row) {
        die(xlt('Encounter not found'));
    }
    $patient  = QueryUtils::querySingleRow("SELECT * FROM patient_data WHERE pid = ?", [$encounter_row['pid']]);
    $pid_form = (int) $encounter_row['pid'];
} else {
    if ($pid_new <= 0) {
        die(xlt('Missing patient ID'));
    }
    $patient  = QueryUtils::querySingleRow("SELECT * FROM patient_data WHERE pid = ?", [$pid_new]);
    $pid_form = $pid_new;
}

$departamentos = [
    'terapia_adulto' => xlt('Adult Therapy'),
    'terapia_kids'   => xlt('Pediatric Therapy'),
];
$servicios = [
    'intensivo'  => xlt('Intensive'),
    'intermedia' => xlt('Intermediate'),
    'minima'     => xlt('Minimal'),
];
$cuartos = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'];

$heading_caption = $viewmode ? xl('Edit Admission') : xl('New Admission');

$arrOeUiSettings = [
    'heading_title'        => $heading_caption,
    'include_patient_name' => false,
    'expandable'           => false,
    'expandable_files'     => [],
    'action'               => '',
    'action_title'         => '',
    'action_href'          => '',
    'show_help_icon'       => false,
    'help_file_name'       => '',
];
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-darkness', 'fontawesome']); ?>
    <title><?php echo xlt('Inpatient Admission'); ?></title>
    <script>
        function cancelClicked() {
            top.RTop.location = <?php echo js_escape($web_root . '/interface/tableros/lista_internados.php'); ?>;
            return false;
        }
    </script>
</head>
<body class="body_top">
<div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-header clearfix">
                <h2>
                    <?php echo $viewmode ? xlt('Edit Admission') : xlt('New Admission'); ?>
                    — <?php echo text(($patient !== null ? (string)($patient['fname'] ?? '') : '') . ' ' . ($patient !== null ? (string)($patient['lname'] ?? '') : '')); ?>
                </h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <form method="post" action="<?php echo $web_root; ?>/interface/tableros/save_internado.php">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
                <input type="hidden" name="mode"  value="<?php echo $viewmode ? 'edit' : 'new'; ?>">
                <input type="hidden" name="pid"   value="<?php echo attr((string)$pid_form); ?>">
                <?php if ($viewmode): ?>
                <input type="hidden" name="encounter_id" value="<?php echo attr((string)$encounter_id); ?>">
                <?php endif; ?>

                <fieldset class="p-3 mt-2">
                    <legend class="px-2"><?php echo xlt('Admission Details'); ?></legend>

                    <?php if (!$viewmode): ?>
                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Date of Admission'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="form_date" class="form-control datepicker"
                                   value="<?php echo attr(date('Y-m-d')); ?>">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Reg. No.'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="nro_registro" class="form-control"
                                   value="<?php echo attr($encounter_row !== null ? (string)($encounter_row['nro_registro'] ?? '') : ''); ?>">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Department'); ?></label>
                        <div class="col-sm-4">
                            <select name="departamento" class="form-control">
                                <?php foreach ($departamentos as $val => $label): ?>
                                <option value="<?php echo attr($val); ?>"
                                    <?php echo (($encounter_row !== null ? (string)($encounter_row['departamento'] ?? '') : '') === $val) ? 'selected' : ''; ?>>
                                    <?php echo text($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Service'); ?></label>
                        <div class="col-sm-4">
                            <select name="servicio" class="form-control">
                                <?php foreach ($servicios as $val => $label): ?>
                                <option value="<?php echo attr($val); ?>"
                                    <?php echo (($encounter_row !== null ? (string)($encounter_row['servicio'] ?? '') : '') === $val) ? 'selected' : ''; ?>>
                                    <?php echo text($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Ward'); ?></label>
                        <div class="col-sm-4">
                            <select name="cuarto" class="form-control">
                                <?php foreach ($cuartos as $val => $label): ?>
                                <option value="<?php echo attr($val); ?>"
                                    <?php echo (($encounter_row !== null ? (string)($encounter_row['cuarto'] ?? '') : '') === $val) ? 'selected' : ''; ?>>
                                    <?php echo text($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo xlt('Bed'); ?></label>
                        <div class="col-sm-4">
                            <select name="cama" class="form-control">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo attr((string)$i); ?>"
                                    <?php echo (($encounter_row !== null ? (string)($encounter_row['cama'] ?? '') : '') == $i) ? 'selected' : ''; ?>>
                                    <?php echo xlt('Bed') . ' ' . text((string)$i); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group row mt-4 px-3">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary" onclick="top.restoreSession()">
                            <i class="fa fa-check mr-1"></i><?php echo $viewmode ? xlt('Save') : xlt('Admit Patient'); ?>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ml-2" onclick="return cancelClicked()">
                            <i class="fa fa-times mr-1"></i><?php echo xlt('Cancel'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $oemr_ui->oeBelowContainerDiv(); ?>
<script>
    $('.datepicker').datetimepicker({
        <?php
        $datetimepicker_timepicker = false;
        $datetimepicker_showseconds = false;
        $datetimepicker_formatInput = true;
        require(OEGlobalsBag::getInstance()->getString('srcdir') . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
        ?>
    });
</script>
</body>
</html>
