<?php

/**
 * Lista de Internados - Nursing Dashboard
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");
/** @var string $srcdir */
/** @var string $web_root */
require_once "$srcdir/user.inc.php";
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\OeUI\OemrUI;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$id_encounter_raw = filter_input(INPUT_GET, 'id_encounter', FILTER_SANITIZE_NUMBER_INT);
$id_encounter     = ($id_encounter_raw !== null && $id_encounter_raw !== '') ? (int)$id_encounter_raw : null;
$nombre_paciente  = filter_input(INPUT_GET, 'paciente', FILTER_SANITIZE_SPECIAL_CHARS);
$death_date       = filter_input(INPUT_GET, 'death_date', FILTER_SANITIZE_SPECIAL_CHARS);
$update_raw       = filter_input(INPUT_GET, 'update', FILTER_SANITIZE_NUMBER_INT);
$update           = ($update_raw !== null && $update_raw !== '') ? (int)$update_raw : null;

if ($death_date) {
    $ts_death = strtotime((string)$death_date);
    $death_date_safe = $ts_death !== false ? date('Y-m-d', $ts_death) : date('Y-m-d');
    QueryUtils::sqlStatementThrowException(
        "UPDATE form_encounter SET date_end = ?, death_date = ? WHERE id = ?",
        [$death_date_safe, $death_date_safe, $id_encounter]
    );
    $id_encounter = null;
} elseif ($id_encounter) {
    QueryUtils::sqlStatementThrowException(
        "UPDATE form_encounter SET date_end = DATE(NOW()) WHERE id = ?",
        [$id_encounter]
    );
}

// Resolve inpatient category ID by name (portable across installations)
define('NURSING_INPATIENT_CATEGORY', 'Inpatient');
$catRow = QueryUtils::querySingleRow(
    "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catname = ? LIMIT 1",
    [NURSING_INPATIENT_CATEGORY]
);
$inpatient_catid = $catRow ? (int)(string)($catRow['pc_catid'] ?? '0') : 0;

$inpatient = QueryUtils::fetchRecords(
    "SELECT f.*, CONCAT(p.fname, ' ', p.lname) AS paciente, p.pubpid AS pubpid
     FROM form_encounter AS f
     JOIN patient_data AS p ON p.pid = f.pid
     WHERE f.pc_catid = ? AND f.date_end IS NULL",
    [$inpatient_catid]
);

// Nursing form definitions: form folder => display label
$nursing_forms = [
    'curaciones'  => xlt('Wound Care'),
    'aplicaciones' => xlt('Nursing Applications'),
    'cuidados'    => xlt('Nursing Care Bundle'),
    'evaluaciones' => xlt('Nursing Evaluation'),
    'registro_vm' => xlt('Ventilation Record'),
];
?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-bs', 'fontawesome']); ?>
    <title><?php echo xlt('Inpatient List'); ?></title>

    <style type="text/css">
        div.dataTables_wrapper div.dataTables_processing {
            top: -20px;
            width: auto;
            margin: 0;
            color: red;
            transform: translateX(-50%);
        }

        @media screen and (max-width: 640px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: inherit;
                text-align: justify;
            }
        }

        thead input { width: 100%; }

        .inner { display: inline-block; }
        .outer {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
            align-items: center;
        }

        /* Nursing modal — patient bar */
        .enf-paciente-bar {
            background: rgba(128,128,128,0.08);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Nursing form card buttons */
        .enf-col { padding: 8px; }

        .btn-enf-card {
            width: 100%;
            min-height: 120px;
            background: transparent;
            color: inherit;
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            cursor: pointer;
        }

        .btn-enf-card:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,0.14);
            transform: translateY(-3px);
        }

        .btn-enf-card:active { transform: translateY(-1px); }

        .btn-enf-card .enf-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .btn-enf-card .enf-icon i { color: #fff; }

        .enf-icon--green  { background: #4CAF50; }
        .enf-icon--blue   { background: #2196F3; }
        .enf-icon--red    { background: #e53935; }
        .enf-icon--orange { background: #FB8C00; }
        .enf-icon--purple { background: #8E24AA; }
        .enf-icon--gray   { background: #9e9e9e; }

        .btn-enf-card .enf-label {
            font-size: 12px;
            font-weight: 700;
            color: inherit;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: center;
            line-height: 1.3;
        }

        .btn-enf-disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Row separation */
        #inp_table tbody tr {
            border-bottom: 3px solid rgba(128, 128, 128, 0.25) !important;
        }

        #inp_table tbody tr td {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .dark-mode #inp_table tbody tr {
            border-bottom: 3px solid rgba(255, 255, 255, 0.15) !important;
        }

        @media (max-width: 576px) {
            .btn-enf-card { min-height: 100px; }
            .btn-enf-card .enf-icon { width: 44px; height: 44px; }
        }
    </style>

    <?php
    $arrOeUiSettings = [
        'heading_title'        => xl('Patient Finder'),
        'include_patient_name' => false,
        'expandable'           => true,
        'expandable_files'     => ['dynamic_finder_xpd'],
        'action'               => "search",
        'action_title'         => "",
        'action_href'          => "",
        'show_help_icon'       => false,
        'help_file_name'       => ""
    ];
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>

<body class="body_top">

    <div id="container" class="<?php echo attr($oemr_ui->oeContainer()); ?>" style="width: 95%;">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <h2><?php echo xlt('Inpatient List'); ?></h2>
                    <button id="btn-nuevo-ingreso" class="btn btn-success btn-sm mb-2">
                        <i class="fa fa-plus"></i>&nbsp;<?php echo xlt('New Admission'); ?>
                    </button>
                    <br />
                    <?php if ($id_encounter !== null) : ?>
                    <div class="alert alert-success alert-dismissible show" role="alert">
                        <?php echo xlt('Patient discharged successfully'); ?>:
                        <strong><?php echo text((string)($nombre_paciente ?? '')); ?></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo xla('Close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php if ($update !== null) : ?>
                    <div class="alert alert-success alert-dismissible show" role="alert">
                        <?php echo xlt('Patient updated successfully'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo xla('Close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div id="dynamic">
                    <table border="0" cellpadding="0" cellspacing="0" class="display" id="inp_table" style="width:100%">
                        <thead>
                            <tr>
                                <th class="head" style="width:5%;"><?php echo xlt('Record No.'); ?></th>
                                <th class="head"><?php echo xlt('Patient'); ?></th>
                                <th class="head"><?php echo xlt('Admission'); ?></th>
                                <th class="head"><?php echo xlt('ID (Patient Record)'); ?></th>
                                <th class="head" style="width:4%;"><?php echo xlt('Reg. No.'); ?></th>
                                <th class="head" style="width:10%;"><?php echo xlt('Service'); ?></th>
                                <th class="head" style="width:10%;"><?php echo xlt('Ward'); ?></th>
                                <th class="head" style="width:5%;"><?php echo xlt('Bed'); ?></th>
                                <th class="head" style="width:10%;"><?php echo xlt('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inpatient as $result) : ?>
                            <tr>
                                <td class="btn-pacienteData btn-link"
                                    data-pid="<?php echo attr((string)($result['pid'] ?? '')); ?>"
                                    data-encounter="<?php echo attr((string)($result['encounter'] ?? '')); ?>">
                                    <?php echo text((string)($result['pid'] ?? '')); ?>
                                </td>
                                <td><?php echo text((string)($result['paciente'] ?? '')); ?></td>
                                <?php $ts_date = strtotime((string)($result['date'] ?? '')); ?>
                                <td><?php echo text(date('d/m/Y', $ts_date !== false ? $ts_date : time())); ?></td>
                                <td><?php echo text((string)($result['pubpid'] ?? '')); ?></td>
                                <td><?php echo text((string)($result['nro_registro'] ?? '')); ?></td>
                                <td><?php echo text(strtoupper((string)($result['servicio'] ?? ''))); ?></td>
                                <td><?php echo text(strtoupper((string)($result['cuarto'] ?? ''))); ?></td>
                                <td><?php echo text((string)($result['cama'] ?? '')); ?></td>
                                <td>
                                    <div class="outer">
                                        <div class="inner">
                                            <button class="btn btn-info btn-sm btn-editar" type="button"
                                                data-id="<?php echo attr((string)($result['id'] ?? '')); ?>">
                                                <i class="fa fa-pencil-alt mr-1"></i><?php echo xlt('Edit'); ?>
                                            </button>
                                        </div>
                                        <div class="inner">
                                            <button class="btn btn-outline-secondary btn-sm btn-alta" type="button"
                                                id="<?php echo attr((string)($result['id'] ?? '')); ?>"
                                                data-title="<?php echo attr(xlt('Discharge patient') . ': ' . (string)($result['paciente'] ?? '')); ?>"
                                                data-paciente="<?php echo attr((string)($result['paciente'] ?? '')); ?>">
                                                <i class="fa fa-sign-out-alt mr-1"></i><?php echo xlt('Discharge'); ?>
                                            </button>
                                        </div>
                                        <div class="inner">
                                            <button class="btn btn-danger btn-sm btn-death" type="button"
                                                data-id="<?php echo attr((string)($result['id'] ?? '')); ?>"
                                                data-title="<?php echo attr(xlt('Register patient death') . ': ' . (string)($result['paciente'] ?? '')); ?>"
                                                data-paciente="<?php echo attr((string)($result['paciente'] ?? '')); ?>">
                                                <i class="fa fa-times-circle mr-1"></i><?php echo xlt('Deceased'); ?>
                                            </button>
                                        </div>
                                        <div class="inner">
                                            <button class="btn btn-primary btn-sm btn-Enferm" type="button"
                                                data-id="<?php echo attr((string)($result['id'] ?? '')); ?>"
                                                data-paciente="<?php echo attr((string)($result['paciente'] ?? '')); ?>"
                                                data-pid="<?php echo attr((string)($result['pid'] ?? '')); ?>"
                                                data-encounter="<?php echo attr((string)($result['encounter'] ?? '')); ?>">
                                                <i class="fa fa-user-nurse mr-1"></i><?php echo xlt('Nursing'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="head"><?php echo xlt('Record No.'); ?></th>
                                <th class="head"><?php echo xlt('Patient'); ?></th>
                                <th class="head"><?php echo xlt('Admission Date'); ?></th>
                                <th class="head"><?php echo xlt('ID (Patient Record)'); ?></th>
                                <th class="head"><?php echo xlt('Reg. No.'); ?></th>
                                <th class="head"><?php echo xlt('Service'); ?></th>
                                <th class="head"><?php echo xlt('Ward'); ?></th>
                                <th class="head"><?php echo xlt('Bed'); ?></th>
                                <th class="head"><?php echo xlt('Actions'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal DISCHARGE/DECEASED -->
        <div class="modal" tabindex="-1" role="dialog" id="modal_alta">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal_title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo xla('Close'); ?>">
                            <span aria-hidden="true"><i class="fa fa-times"></i></span>
                        </button>
                    </div>
                    <form method="get" name="form" action="lista_internados.php">
                        <div class="modal-body">
                            <p id="body_alta">
                                <?php echo xlt('Are you sure you want to discharge the patient'); ?>:
                                <b id="paciente_name"></b>?
                            </p>
                            <div class="row" id="body_death" style="display:none;">
                                <div class="col-md-6">
                                    <label for="death_date"><?php echo xlt('Register date of death'); ?></label>
                                    <input id="death_date" name="death_date" class="form-control" type="date" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="id_encounter" id="id_encounter" />
                            <input type="hidden" name="paciente" id="nombre_paciente" />
                            <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">
                                <?php echo xlt('Cancel'); ?>
                            </button>
                            <input type="submit" value="<?php echo xla('Confirm'); ?>" class="btn btn-success pull-right">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal NURSING -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal_Enf">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-user-md"></i> <?php echo xlt('Nursing Options'); ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo xla('Close'); ?>">
                            <span aria-hidden="true"><i class="fa fa-times"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="enf-paciente-bar">
                            <strong><?php echo xlt('Patient'); ?>:</strong>
                            <span id="paciente_nombre_enf"></span>
                        </p>
                        <div class="row">
                            <!-- Curaciones -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enfRedired" type="button" data-form="curaciones">
                                    <div class="enf-icon enf-icon--green">
                                        <i class="fa fa-heartbeat fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Wound Care'); ?></div>
                                </button>
                            </div>
                            <!-- Aplicaciones -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enfRedired" type="button" data-form="aplicaciones">
                                    <div class="enf-icon enf-icon--blue">
                                        <i class="fa fa-tint fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Nursing Applications'); ?></div>
                                </button>
                            </div>
                            <!-- Cuidados -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enfRedired" type="button" data-form="cuidados">
                                    <div class="enf-icon enf-icon--red">
                                        <i class="fa fa-heart fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Nursing Care Bundle'); ?></div>
                                </button>
                            </div>
                            <!-- Evaluaciones -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enfRedired" type="button" data-form="evaluaciones">
                                    <div class="enf-icon enf-icon--orange">
                                        <i class="fa fa-clipboard fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Nursing Evaluation'); ?></div>
                                </button>
                            </div>
                            <!-- Registro VM -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enfRedired" type="button" data-form="registro_vm">
                                    <div class="enf-icon enf-icon--purple">
                                        <i class="fa fa-stethoscope fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Ventilation Record'); ?></div>
                                </button>
                            </div>
                            <!-- Próximamente -->
                            <div class="col-md-4 col-sm-6 enf-col">
                                <button class="btn-enf-card btn-enf-disabled" type="button" disabled>
                                    <div class="enf-icon enf-icon--gray">
                                        <i class="fa fa-plus fa-2x"></i>
                                    </div>
                                    <div class="enf-label"><?php echo xlt('Coming Soon'); ?></div>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">
                            <?php echo xlt('Close'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php $oemr_ui->oeBelowContainerDiv(); ?>

    <script type="text/javascript">
        var webroot_url = <?php echo js_escape($web_root); ?>;
        var encounter_sel;
        var pid_sel;

        var xl_strings_tabs_view_model = <?php echo json_encode([
            'encounter_locked'    => xla('This encounter is locked. No new forms can be added.'),
            'must_select_patient' => OEGlobalsBag::getInstance()->getBoolean('enable_group_therapy')
                ? xla('You must first select or add a patient or therapy group.')
                : xla('You must first select or add a patient.'),
            'must_select_encounter' => xla('You must first select or create an encounter.'),
            'new' => xla('New')
        ]); ?>;
        var csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>;

        $(function() {

            // NEW ADMISSION — patient selector popup callback
            window.setpatient = function(pid, lname, fname, dob) {
                top.restoreSession();
                top.RTop.location = webroot_url + '/interface/tableros/editar_internado.php?pid=' + encodeURIComponent(pid);
            };

            // NEW ADMISSION button — open patient picker as in-app dialog
            $('#btn-nuevo-ingreso').on('click', function() {
                top.restoreSession();
                dlgopen(
                    webroot_url + '/interface/main/calendar/find_patient_popup.php',
                    'findPatientAdmission',
                    700, 500, false, <?php echo js_escape(xlt('Select Patient')); ?>
                );
            });

            // DISCHARGE button
            $(document).on('click', '.btn-alta', function() {
                var id_enc  = this.id;
                var titulo  = $(this).data('title');
                var pacient = $(this).data('paciente');
                $('#body_alta').show();
                $('#body_death').hide();
                $('#death_date').removeAttr('required');
                $('#modal_title').html('<b>' + titulo + '</b>');
                $('#paciente_name').html('<b>' + pacient + '</b>');
                $('#id_encounter').val(id_enc);
                $('#nombre_paciente').val(pacient);
                $('#modal_alta').modal('toggle');
            });

            // DECEASED button
            $(document).on('click', '.btn-death', function() {
                var id_enc  = $(this).data('id');
                var titulo  = $(this).data('title');
                var pacient = $(this).data('paciente');
                $('#death_date').attr('required', true);
                $('#body_alta').hide();
                $('#body_death').show();
                $('#modal_title').html('<b>' + titulo + '</b>');
                $('#paciente_name').html('<b>' + pacient + '</b>');
                $('#id_encounter').val(id_enc);
                $('#nombre_paciente').val(pacient);
                $('#modal_alta').modal('toggle');
            });

            // NURSING button — open modal
            $(document).on('click', '.btn-Enferm', function() {
                encounter_sel = $(this).data('encounter');
                pid_sel       = $(this).data('pid');
                $('#paciente_nombre_enf').text($(this).data('paciente'));
                $('#modal_Enf').modal('show');
            });

            // EDIT button
            $(document).on('click', '.btn-editar', function() {
                top.RTop.location = webroot_url + '/interface/tableros/editar_internado.php?id=' + $(this).data('id');
            });

            // NURSING form buttons — single handler via data-form attribute
            $(document).on('click', '.btn-enfRedired', function() {
                var form = $(this).data('form');
                if (!encounter_sel || !pid_sel) {
                    alert(<?php echo js_escape(xlt('Error: Could not retrieve patient data.')); ?>);
                    return;
                }
                $('#modal_Enf').modal('hide');
                setTimeout(function() {
                    top.RTop.location = webroot_url + '/interface/forms/' + form
                        + '/new.php?mode=new&id=0&pid=' + pid_sel + '&encounter=' + encounter_sel;
                }, 300);
            });

            // Patient data link
            $(document).on('click', '.btn-pacienteData', function() {
                top.RTop.location = webroot_url + '/interface/patient_file/encounter/encounter_top.php'
                    + '?set_encounter=' + $(this).data('encounter') + '&pid=' + $(this).data('pid');
            });

            // DataTable
            $(document).ready(function() {
                $('#inp_table thead tr').clone(true).appendTo('#inp_table thead');
                $('#inp_table thead tr:eq(1) th').each(function(i) {
                    var title = $(this).text();
                    if (title.trim() !== <?php echo js_escape(xlt('Actions')); ?>) {
                        $(this).html('<input type="text" placeholder="<?php echo xla('Search'); ?> ' + title + '" />');
                    } else {
                        $(this).html('');
                    }
                    $('input', this).on('keyup change', function() {
                        if (datatable.column(i).search() !== this.value) {
                            datatable.column(i).search(this.value).draw();
                        }
                    });
                });

                const datatable = $('#inp_table').DataTable({
                    order: [[6, 'asc'], [7, 'asc']],
                    responsive: true,
                    orderCellsTop: true,
                    fixedHeader: true
                });
            });
        });
    </script>
    <script>document.addEventListener('touchstart', {});</script>
</body>
</html>
