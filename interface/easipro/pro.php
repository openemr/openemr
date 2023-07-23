<?php

/**
 * pro.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Easipro\Easipro;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('PRO{{Patient Reported Outcomes}}'); ?></title>
    <?php Header::setupHeader(); ?>
    <script>
        $(function () {
            $('#ext-proc-out').hide();
            $('#ext-enc').on('click', function () {
                $('#ext-enc').addClass('active');
                $('#ext-proc').removeClass('active');
                $('#ext-proc-out').hide();
                $('#ext-enc-out').show();
            });
            $('#ext-proc').on('click', function () {
                $('#ext-proc').addClass('active');
                $('#ext-enc').removeClass('active');
                $('#ext-enc-out').hide();
                $('#ext-proc-out').show();
            });
        });

        function listForms(param) {
            param.innerHTML = "<i class='fa fa-circle-notch fa-spin'></i> " + jsText(<?php echo xlj('Loading'); ?>);

            top.restoreSession();
            $.ajax({
                url: "../../library/ajax/easipro_util.php",
                type: "POST",
                data: {
                    'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                    'function': 'list_forms'
                },
                dataType: "json",
                success: function (data) {
                    if (data.Error) {
                        syncAlertMsg(data.Error, 10000);
                        // let drop through to show available categories.
                    }
                    $('#form-list').html("");
                    let forms = data.Form;
                    let ascq_me_forms = [];
                    let neuro_qol_forms = [];
                    let nih_tb_forms = [];
                    let promis_forms = [];
                    let sci_fi_forms = [];
                    let sci_qol_forms = [];
                    let tbi_qol_forms = [];
                    // if not any forms then just report 0 for sub categories.
                    if (forms) {
                        for (let i = 0; i < forms.length; i++) {
                            if (forms[i].Name.startsWith("ASCQ-Me")) {
                                ascq_me_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("Neuro-QoL") || forms[i].Name.startsWith("Neuro-QOL")) {
                                neuro_qol_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("NIH TB")) {
                                // alert(forms[i].Name);
                                nih_tb_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("PROMIS")) {
                                promis_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("SCI-FI")) {
                                sci_fi_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("SCI-QOL") || forms[i].Name.startsWith("SCI-QoL")) {
                                sci_qol_forms.push(forms[i])
                            } else if (forms[i].Name.startsWith("TBI-QOL") || forms[i].Name.startsWith("TBI-QoL")) {
                                tbi_qol_forms.push(forms[i])
                            }
                        }
                    }

                    // ascq
                    let ascq_me_container = "<div onclick='openCloseList(this)' style='cursor:pointer;'><div class='list-title-close'><b>ASCQ-Me (" + ascq_me_forms.length + ")</b></div></div>"
                    let alist = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < ascq_me_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + ascq_me_forms[i].OID + "' desc='" + ascq_me_forms[i].Name + "'>" + ascq_me_forms[i].Name + "</input></li>";
                        alist += myform;
                    }
                    alist += "</ul>"
                    $('#form-list').append(ascq_me_container);
                    $('#form-list').append(alist);
                    // neuro_qol
                    let neuro_qol_container = "<div onclick='openCloseList(this)' style='cursor:pointer;'><div class='list-title-close'><b>Neuro-QOL (" + neuro_qol_forms.length + ")</b></div></div>"
                    let blist = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < neuro_qol_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + neuro_qol_forms[i].OID + "' desc='" + neuro_qol_forms[i].Name + "'>" + neuro_qol_forms[i].Name + "</input></li>";
                        blist += myform;
                    }
                    blist += "</ul>"
                    $('#form-list').append(neuro_qol_container);
                    $('#form-list').append(blist);
                    // nih tb
                    let nih_tb_container = "<div onclick='openCloseList(this)' style='cursor:pointer;'><div class='list-title-close'><b>NIH TB (" + nih_tb_forms.length + ")</b></div></div>"
                    let list = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < nih_tb_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + nih_tb_forms[i].OID + "' desc='" + nih_tb_forms[i].Name + "'>" + nih_tb_forms[i].Name + "</input></li>";
                        list += myform;
                    }
                    list += "</ul>"
                    $('#form-list').append(nih_tb_container);
                    $('#form-list').append(list);
                    //prmois
                    let promis_container = "<div onclick='openCloseList(this)' style='cursor:pointer;'><div class='list-title-close'><b>PROMIS (" + promis_forms.length + ")</b></div></div>"
                    list = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < promis_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + promis_forms[i].OID + "' desc='" + promis_forms[i].Name + "'>" + promis_forms[i].Name + "</input></li>";
                        list += myform;
                    }
                    list += "</ul>"
                    $('#form-list').append(promis_container);
                    $('#form-list').append(list);

                    // sci-fi
                    let sci_fi_container = "<div onclick='openCloseList(this)' style='cursor: pointer;'><div class='list-title-close'><b>SCI-FI (" + sci_fi_forms.length + ")</b></div></div>"
                    list = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < sci_fi_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + sci_fi_forms[i].OID + "' desc='" + sci_fi_forms[i].Name + "'>" + sci_fi_forms[i].Name + "</input></li>";
                        list += myform;
                    }
                    list += "</ul>"
                    $('#form-list').append(sci_fi_container);
                    $('#form-list').append(list);

                    // sci-qol
                    let sci_qol_container = "<div onclick='openCloseList(this)' style='cursor: pointer;'><div class='list-title-close font-weight-bold'>SCI-QOL (" + sci_qol_forms.length + ")</div></div>"
                    list = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < sci_qol_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + sci_qol_forms[i].OID + "' desc='" + sci_qol_forms[i].Name + "'>" + sci_qol_forms[i].Name + "</input></li>";
                        list += myform;
                    }
                    list += "</ul>"
                    $('#form-list').append(sci_qol_container);
                    $('#form-list').append(list);
                    //
                    // tbi-qol
                    let tbi_qol_container = "<div onclick='openCloseList(this)' style='cursor: pointer;'><div class='list-title-close font-weight-bold'>TBI-QOL (" + tbi_qol_forms.length + ")</div></div>"
                    list = "<ul class='m-0 p-0' style='list-style: none; display:none'>"
                    for (let i = 0; i < tbi_qol_forms.length; i++) {
                        let myform = "<li><input type='checkbox' value='" + tbi_qol_forms[i].OID + "' desc='" + tbi_qol_forms[i].Name + "'>" + tbi_qol_forms[i].Name + "</input></li>";
                        list += myform;
                    }
                    list += "</ul>"
                    $('#form-list').append(tbi_qol_container);
                    $('#form-list').append(list);

                    param.innerHTML = "<?php echo xla('List Forms'); ?>";
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    param.innerHTML = "<?php echo xla('List Forms'); ?>";

                    document.write(jqXHR.responseText + ':' + textStatus + ':' + errorThrown);
                }
            })
        }

        function openCloseList(ele) {
            $(ele).next().toggle();
            child = $(ele).find(">:first-child")
            if (child.hasClass('list-title-close')) {
                child.removeClass('list-title-close');
                child.addClass('list-title-open');
            } else {
                child.removeClass('list-title-open');
                child.addClass('list-title-close');
            }
        }

        function orderForm(param) {
            param.innerHTML = "<i class='fa fa-circle-notch fa-spin'></i> " + jsText(<?php echo xlj('Ordering'); ?>);

            let selectedForm = $('#form-list').find('input:checked');
            if (selectedForm.length > 0) {
                // Ajax call started to start an assessment
                // alert(selectedForm.length)
                for (let i = 0; i < selectedForm.length; i++) {
                    // writeOrder("test", "testname", "testOID", 1, "2018-01-25 00:00:00")
                    (function (i) {
                        let formOID = $(selectedForm[i]).val();
                        let formName = $(selectedForm[i]).attr('desc');
                        top.restoreSession();
                        $.ajax({
                            url: "../../library/ajax/easipro_util.php",
                            type: "POST",
                            data: {
                                'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                                'function': 'order_form',
                                'formOID': formOID
                            },
                            dataType: "json",
                            success: function (data) {
                                // Expiration: Timestamp; duration: 3 days; timezone: CST
                                writeOrder(formOID, formName, data.OID, data.Expiration, 'ordered')

                                param.innerHTML = jsText(<?php echo xlj('Order Form'); ?>);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                param.innerHTML = jsText(<?php echo xlj('Order Form'); ?>);

                                document.write(jqXHR.responseText + ':' + textStatus + ':' + errorThrown);
                            }
                        });
                        // Ajax call ended
                    })(i)
                }
            } else {
                alert(<?php echo xlj('No form selected to order!'); ?>);
            }
        }

        function writeOrder(formOID, formName, assessmentOID, expiration, status) {
            top.restoreSession();
            $.ajax({
                url: "../../library/ajax/easipro_util.php",
                type: 'POST',
                data: {
                    'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                    'function': 'request_assessment',
                    'formOID': formOID,
                    'formName': formName,
                    'assessmentOID': assessmentOID,
                    'expiration': expiration,
                    'status': status
                },
                success: function () {
                    alert(<?php echo xlj('Successfully ordered form'); ?> +" " + formName);
                    document.location.reload();
                }
            });
        }

        <?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
    </script>
    <?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Patient Reported Outcomes'),
        'include_patient_name' => true,
        'expandable' => false,
        'expandable_files' => array(),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
        'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body class="body_top">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer(); ?>">
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php"); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php
                $list_id = "patient_reported_outcomes"; // to indicate nav item is active, count and give correct id
                // Collect the patient menu then build it
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>

        <nav class="nav nav-pills">
            <a href="#" id="ext-enc" class="nav-item nav-link active"><?php echo xlt('Existing Forms'); ?></a>
            <a href="#" id="ext-proc" class="nav-item nav-link"><?php echo xlt('Add New Form'); ?></a>
        </nav>

        <hr />

        <div id="ext-enc-out">
            <?php
            $records1 = Easipro::assessmentsForPatient($pid);
            if (!empty($records1)) { ?>
                <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th><?php echo xlt('Name'); ?></th>
                        <th><?php echo xlt('Deadline (CST)'); ?></th>
                        <th><?php echo xlt('Status'); ?></th>
                        <th><?php echo xlt('T-Score'); ?></th>
                    </tr>
                    </thead>
                    <?php foreach ($records1 as $value1) { ?>
                        <tr>
                            <td><?php echo text($value1['form_name']); ?></td>
                            <td><?php echo text(oeFormatDateTime($value1['deadline'])); ?></td>
                            <td><?php echo text($value1['status']); ?></td>
                            <td><?php echo text(substr($value1['score'], 0, 4)); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>

            <?php if (empty($records1)) { ?>
                <h3 class="text-center font-weight-bold"><?php echo xlt('Nothing to display'); ?></h3>
            <?php } ?>
        </div>
        <div id="ext-proc-out">
            <button class="btn btn-sm btn-secondary" id="listforms" onclick="listForms(this)"><?php echo xlt('List Forms'); ?></button>
            <button class="btn btn-sm btn-secondary" onclick="orderForm(this)"><?php echo xlt('Order Form'); ?></button>
            <div id='form-list'></div>
        </div>
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv(); ?>
    <script>
        let listId = '#' + <?php echo js_escape($list_id); ?>;
        $(function () {
            $(listId).addClass("active");
        });
    </script>
</body>
</html>
