<?php

/**
 * Nursing Evaluations Form - view.php
 * Displays evaluation records for a patient encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$session   = SessionWrapperFactory::getInstance()->getActiveSession();
$pid       = isset($_GET['pid'])       ? (int)$_GET['pid']       : (int)($session->get('pid') ?? 0);
$encounter = isset($_GET['encounter']) ? (int)$_GET['encounter'] : (int)($session->get('encounter') ?? 0);
$id        = isset($_GET['id'])        ? (int)$_GET['id']        : 0;

if (!$pid || !$encounter) {
    echo "<div class='alert alert-danger m-3'>" . xlt("Could not retrieve PID or Encounter.") . "</div>";
    exit;
}

$paciente = sqlQuery("SELECT CONCAT(fname, ' ', lname) AS full_name, pubpid, DOB FROM patient_data WHERE pid = ?", [$pid]);
$age = '';
if (!empty($paciente['DOB'])) {
    $dob = new DateTime($paciente['DOB']);
    $age = (new DateTime())->diff($dob)->y . ' ' . xlt('years');
}

if ($id > 0) {
    $result = sqlStatement("SELECT * FROM form_evaluaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
} else {
    $result = sqlStatement("SELECT * FROM form_evaluaciones WHERE pid = ? AND encounter = ? ORDER BY date DESC", [$pid, $encounter]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Nursing Evaluations'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .evaluaciones-view * { box-sizing: border-box; }
        .evaluaciones-view .registro-card { border: 1px solid #dee2e6; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
        .evaluaciones-view .glasgow-badge { font-size: 1.1em; font-weight: bold; }
        .evaluaciones-view .field-detail { background: #f8f9fa; border-radius: 4px; padding: 10px 14px; margin-bottom: 10px; border-left: 4px solid #6c757d; }
        .evaluaciones-view .field-detail.has-obs { border-left-color: #0d6efd; background: #e7f3ff; }
    </style>
</head>
<body>
<div class="evaluaciones-view container-fluid mt-3">
    <h5 class="border-bottom pb-2">
        <?php echo $id > 0 ? xlt('Evaluation Detail') : xlt('Nursing Evaluations'); ?>
    </h5>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row">
                <div class="col-sm-4"><strong><?php echo xlt('Patient'); ?>:</strong> <?php echo text($paciente['full_name'] ?? ''); ?></div>
                <div class="col-sm-3"><strong><?php echo xlt('ID'); ?>:</strong> <?php echo text($paciente['pubpid'] ?? ''); ?></div>
                <?php if ($age) : ?>
                <div class="col-sm-3"><strong><?php echo xlt('Age'); ?>:</strong> <?php echo text($age); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (sqlNumRows($result) == 0) : ?>
    <div class="alert alert-info"><?php echo xlt('No evaluations recorded'); ?></div>
    <?php endif; ?>

    <?php while ($row = sqlFetchArray($result)) :
        $glasgow = (int)($row['glasgow_total'] ?? 0);
        if ($glasgow >= 13) {
            $g_class = 'success'; $g_level = xlt('Mild');
        } elseif ($glasgow >= 9) {
            $g_class = 'warning'; $g_level = xlt('Moderate');
        } else {
            $g_class = 'danger';  $g_level = xlt('Severe');
        }
        ?>
    <div class="registro-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong><?php echo text(date('d/m/Y H:i', strtotime($row['date']))); ?></strong>
                <small class="text-muted ml-2"><?php echo xlt('User'); ?>: <?php echo text($row['user']); ?></small>
            </div>
            <div>
                <a href="<?php echo attr($GLOBALS['webroot'] . '/interface/forms/evaluaciones/new.php?pid=' . $pid . '&encounter=' . $encounter . '&id=' . $row['id']); ?>"
                   class="btn btn-primary mr-1" onclick="top.restoreSession()"><i class="fas fa-pen mr-1"></i><?php echo xlt('Edit'); ?></a>
                <a href="<?php echo attr($GLOBALS['webroot'] . '/interface/forms/evaluaciones/print.php?pid=' . $pid . '&encounter=' . $encounter . '&id=' . $row['id']); ?>"
                   target="_blank" class="btn btn-success" onclick="top.restoreSession()"><i class="fas fa-print mr-1"></i><?php echo xlt('Print'); ?></a>
            </div>
        </div>

        <div class="alert alert-<?php echo attr($g_class); ?> py-2">
            <strong><?php echo xlt('Glasgow'); ?>: <?php echo text($glasgow); ?>/15</strong>
            &nbsp;— <?php echo text($g_level); ?>
        </div>

        <?php
        $fields = [
            'conciencia' => [xlt('Consciousness'),     'obs_conciencia'],
            'tono'       => [xlt('Muscle Tone'),        'obs_tono'],
            'pupilas'    => [xlt('Pupils'),             'obs_pupilas'],
            'mucosas'    => [xlt('Mucous Membranes'),   'obs_mucosas'],
            'glasgow_ojos'   => [xlt('Eye Opening'),    'obs_glasgow_ojos'],
            'glasgow_motora' => [xlt('Motor Response'), 'obs_glasgow_motora'],
            'glasgow_verbal' => [xlt('Verbal Response'),'obs_glasgow_verbal'],
        ];
        foreach ($fields as $field => [$label, $obs_field]) :
            $val = $row[$field] ?? '';
            $obs = $row[$obs_field] ?? '';
            ?>
        <div class="field-detail <?php echo !empty($obs) ? 'has-obs' : ''; ?>">
            <strong><?php echo text($label); ?>:</strong> <?php echo text($val ?: '—'); ?>
            <?php if ($obs) : ?>
            <div class="mt-1 small text-muted"><?php echo nl2br(text($obs)); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if ($row['hora_evaluacion']) : ?>
        <small class="text-muted"><?php echo xlt('Evaluation Time'); ?>: <?php echo text($row['hora_evaluacion']); ?></small>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>

    <div class="form-group mt-3">
        <button type="button" onclick="history.back()" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left mr-1"></i><?php echo xlt('Back'); ?>
        </button>
    </div>
</div>
</body>
</html>
