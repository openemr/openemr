<?php
/**
 *
 * Copyright (C) 2016-2018 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
 require_once("verify_session.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
 require_once("lib/portal_mail.inc");


if ($_SESSION['register'] === true) {
    session_destroy();
    header('Location: '.$landingpage.'&w');
    exit();
}

if (!isset($_SESSION['portal_init'])) {
    $_SESSION['portal_init'] = true;
}

 $whereto = 'profilepanel';
if (isset($_SESSION['whereto'])) {
    $whereto = $_SESSION['whereto'];
}

 $user = isset($_SESSION['sessionUser']) ? $_SESSION['sessionUser'] : 'portal user';
 $result = getPatientData($pid);

 $msgs = getPortalPatientNotes($_SESSION['portal_username']);
 $msgcnt = count($msgs);
 $newcnt = 0;
foreach ($msgs as $i) {
    if ($i['message_status']=='New') {
        $newcnt += 1;
    }
}

require_once '_header.php';
 echo "<script>var cpid='" . attr($pid) . "';var cuser='" . attr($user) . "';var webRoot='" . $GLOBALS['web_root'] . "';var ptName='" . attr($_SESSION['ptName']) . "';</script>";
?>
<script type="text/javascript">
var webroot_url = webRoot;

$(document).ready(function () {

    $("#profilereport").load("./get_profile.php", {embeddedScreen: true}, function () {
        $("table").addClass("table  table-responsive");
        $(".demographics td").removeClass("label");
        $(".demographics td").addClass("bold");
        $(".insurance table").addClass("table-sm table-striped");
        $("#editDems").click(function () {
            showProfileModal()
        });
    });
    $("#reports").load("./report/portal_patient_report.php?pid='<?php echo attr($pid) ?>'", {embeddedScreen: true}, function () {
        <?php if ($GLOBALS['portal_two_payments']) { ?>
            $("#payment").load("./portal_payment.php", {embeddedScreen: true}, function () {});
        <?php } ?>
    });
    $("#medicationlist").load("./get_medications.php", {embeddedScreen: true}, function () {
        $("#allergylist").load("./get_allergies.php", {embeddedScreen: true}, function () {
            $("#problemslist").load("./get_problems.php", {embeddedScreen: true}, function () {
                $("#amendmentslist").load("./get_amendments.php", {embeddedScreen: true}, function () {
                    $("#labresults").load("./get_lab_results.php", {embeddedScreen: true}, function () {

                    });
                });
            });
        });
    });

    $('.sigPad').signaturePad({drawOnly: true});
    $(".generateDoc_download").click(function () {
        $("#doc_form").submit();
    });

    function showProfileModal() {
        var title = '<?php echo xla('Demographics Legend Red: Charted Values. Blue: Patient Edits'); ?> ';

        var params = {
            buttons: [
                {text: '<?php echo xla('Help'); ?>', close: false, style: 'info', id: 'formHelp'},
                {text: '<?php echo xla('Cancel'); ?>', close: true, style: 'default'},
                {text: '<?php echo xla('Revert Edits'); ?>', close: false, style: 'danger', id: 'replaceAllButton'},
                {text: '<?php echo xla('Send for Review'); ?>',
                    close: false,
                    style: 'success',
                    id: 'donePatientButton'
                }],
            onClosed: 'reload',
            type: 'GET',
            url: webRoot + '/portal/patient/patientdata?pid=' + cpid + '&user=' + cuser
        };
        dlgopen('','','modal-xl', 500, '', title, params);
    }

    function saveProfile() {
        page.updateModel();
    }

    var gowhere = '#<?php echo $whereto?>';
    $(gowhere).collapse('show');

    var $doHides = $('#panelgroup');
    $doHides.on('show.bs.collapse', '.collapse', function () {
        $doHides.find('.collapse.in').collapse('hide');
    });
    //Enable sidebar toggle
    $("[data-toggle='offcanvas']").click(function (e) {
        e.preventDefault();
        //If window is small enough, enable sidebar push menu
        if ($(window).width() <= 992) {
            $('.row-offcanvas').toggleClass('active');
            $('.left-side').removeClass("collapse-left");
            $(".right-side").removeClass("strech");
            $('.row-offcanvas').toggleClass("relative");
        } else {
            //Else, enable content streching
            $('.left-side').toggleClass("collapse-left");
            $(".right-side").toggleClass("strech");
        }
    });
    $(function () {
        $('#popwait').hide();
        $('#callccda').click(function () {
            $('#popwait').show();
        })
    });
});

function editAppointment(mode,deid){
    if(mode == 'add'){
        var title = '<?php echo xla('Request New Appointment'); ?>';
        var mdata = {pid:deid};
    }
    else{
        var title = '<?php echo xla('Edit Appointment'); ?>';
        var mdata = {eid:deid};
    }
    var params = {
        dialogId: 'editpop',
        buttons: [
            { text: '<?php echo xla('Cancel'); ?>', close: true, style: 'default' }
            //{ text: 'Print', close: false, style: 'success', click: showCustom }
        ],
        type:'GET',
        dataType: 'text',
        url: './add_edit_event_user.php',
        data: mdata
    };

    dlgopen('', 'apptModal', 610, 300, '', title, params);

};

</script>
    <!-- Right side column. Contains content of the page -->
    <aside class="right-side">
        <!-- Main content -->
        <section class="container-fluid content panel-group" id="panelgroup">
        <div id="popwait" class="alert alert-warning" style="font-size:18px"><strong><?php echo xlt('Working!'); ?></strong> <?php echo xlt('Please wait...'); ?></div>
            <div class="row collapse" id="lists">
                <div class="col-sm-6">
                    <div class="panel panel-primary">
                        <header class="panel-heading"><?php echo xlt('Medications'); ?> </header>
                        <div id="medicationlist" class="panel-body"></div>

                        <div class="panel-footer"></div>
                    </div>

                    <div class="panel panel-primary">
                        <header class="panel-heading"><?php echo xlt('Medications Allergy List'); ?>  </header>
                        <div id="allergylist" class="panel-body"></div>

                        <div class="panel-footer"></div>
                    </div>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <div class="panel panel-primary">
                        <header class="panel-heading"><?php echo xlt('Issues List'); ?></header>
                        <div id="problemslist" class="panel-body"></div>

                        <div class="panel-footer"></div>
                    </div>
                    <div class="panel panel-primary">
                        <header class="panel-heading"><?php echo xlt('Amendment List'); ?> </header>
                        <div id="amendmentslist" class="panel-body"></div>

                        <div class="panel-footer"></div>
                    </div>
                </div><!-- /.col -->
                    <div class="col-sm-12">
                        <div class="panel panel-primary">
                            <header class="panel-heading"><?php echo xlt('Lab Results'); ?>  </header>
                            <div id="labresults" class="panel-body"></div>
                            <div class="panel-footer"></div>
                        </div><!-- /.panel -->
                    </div><!-- /.col -->

            </div><!-- /.lists -->
            <?php if ($GLOBALS['allow_portal_appointments']) { ?>
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel panel-primary collapse" id="appointmentpanel">
                        <header class="panel-heading"><?php echo xlt('Appointments'); ?>  </header>
                        <div id="appointmentslist" class="panel-body">
                        <?php
                            $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " . "e.pc_startTime, e.pc_hometext, e.pc_apptstatus, u.fname, u.lname, u.mname, " .
                                "c.pc_catname " . "FROM openemr_postcalendar_events AS e, users AS u, " .
                                "openemr_postcalendar_categories AS c WHERE " . "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " . "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " . "ORDER BY e.pc_eventDate, e.pc_startTime";

                            $res = sqlStatement($query, array(
                                $pid
                            ));

                        if (sqlNumRows($res) > 0) {
                            $count = 0;
                            echo '<table id="appttable" style="width:100%;background:#eee;" class="table table-striped fixedtable"><thead>
                                </thead><tbody>';
                            while ($row = sqlFetchArray($res)) {
                                $status_title = getListItemTitle('apptstat', $row['pc_apptstatus']);
                                $count++;
                                $dayname = xl(date("l", strtotime($row ['pc_eventDate'])));
                                $dispampm = "am";
                                $disphour = substr($row ['pc_startTime'], 0, 2) + 0;
                                $dispmin = substr($row ['pc_startTime'], 3, 2);
                                if ($disphour >= 12) {
                                    $dispampm = "pm";
                                    if ($disphour > 12) {
                                        $disphour -= 12;
                                    }
                                }

                                if ($row ['pc_hometext'] != "") {
                                    $etitle = 'Comments' . ": " . $row ['pc_hometext'] . "\r\n";
                                } else {
                                    $etitle = "";
                                }

                                echo "<tr><td><p>";
                                echo "<a href='#' onclick='editAppointment(0," . htmlspecialchars($row ['pc_eid'], ENT_QUOTES) . ')' . "' title='" . htmlspecialchars($etitle, ENT_QUOTES) . "'>";
                                echo "<b>" . htmlspecialchars($dayname . ", " . $row ['pc_eventDate'], ENT_NOQUOTES) . "&nbsp;";
                                echo htmlspecialchars("$disphour:$dispmin " . $dispampm, ENT_NOQUOTES) . "</b><br>";
                                echo htmlspecialchars($row ['pc_catname'], ENT_NOQUOTES) . "<br><b>";
                                echo xlt("Provider") . ":</b> " . htmlspecialchars($row ['fname'] . " " . $row ['lname'], ENT_NOQUOTES) . "<br><b>";
                                echo xlt("Status") . ":</b> " . htmlspecialchars($status_title, ENT_NOQUOTES);
                                echo "</a></p></td></tr>";
                            }

                            if (isset($res) && $res != null) {
                                if ($count < 1) {
                                    echo "&nbsp;&nbsp;" . xlt('None');
                                }
                            }
                        } else { // if no appts
                            echo xlt('No Appointments');
                        }

                            echo '</tbody></table>';
                        ?>
                            <div style='margin: 5px 0 5px'>
                                <a href='#' onclick="editAppointment('add',<?php echo attr($pid); ?>)">
                                    <button class='btn btn-primary pull-right'><?php echo xlt('Schedule New Appointment'); ?></button>
                                </a>
                            </div>
                        </div>
                        <div class="panel-footer"></div>
                    </div><!-- /.panel -->
                </div><!-- /.col -->
            </div><!-- /.row -->
            <?php } ?>
            <?php if ($GLOBALS['portal_two_payments']) { ?>
            <div class="row">
               <div class="col-sm-12">
                    <div class="panel panel-primary collapse" id="paymentpanel">
                        <header class="panel-heading"> <?php echo xlt('Payments'); ?> </header>
                        <div id="payment" class="panel-body"></div>
                        <div class="panel-footer">
                        </div>
                    </div>
                </div> <!--/.col  -->
            </div>
            <?php } ?>
            <?php if ($GLOBALS['allow_portal_chat']) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-primary collapse" style="padding-top:0;padding-bottom:0;" id="messagespanel">
                        <!-- <header class="panel-heading"><?php //echo xlt('Secure Chat'); ?>  </header>-->
                        <div id="messages" class="panel-body" style="height:calc(100vh - 120px);overflow:auto;padding:0 0 0 0;" >
                             <iframe src="./messaging/secure_chat.php" width="100%" height="100%"></iframe>
                        </div>
                    </div>
                </div><!-- /.col -->
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-sm-8">
                    <div class="panel panel-primary collapse" id="reportpanel">
                        <header class="panel-heading"><?php echo xlt('Reports'); ?>  </header>
                        <div id="reports" class="panel-body"></div>
                        <div class="panel-footer"></div>
                    </div>
                </div>
                <!-- /.col -->
                <?php if (!empty($GLOBALS['portal_onsite_document_download'])) { ?>
                <div class="col-sm-6">
                    <div class="panel panel-primary collapse" id="downloadpanel">
                        <header class="panel-heading"> <?php echo xlt('Download Documents'); ?> </header>
                        <div id="docsdownload" class="panel-body">
                            <div>
                                <span class="text"><?php echo xlt('Download all patient documents');?></span>
                                <form name='doc_form' id='doc_form' action='./get_patient_documents.php' method='post'>
                                <input type="button" class="generateDoc_download" value="<?php echo xla('Download'); ?>" />
                                </form>
                            </div>
                        </div><!-- /.panel-body -->
                        <div class="panel-footer"></div>
                    </div>
                </div><!-- /.col -->
                <?php } ?>
            </div>
            <?php if ($GLOBALS['portal_two_ledger']) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-primary collapse" id="ledgerpanel">
                        <header class="panel-heading"><?php echo xlt('Ledger');?> </header>
                        <div id="patledger" class="panel-body"></div>
                        <div class="panel-footer">
                          <iframe src="./report/pat_ledger.php?form=1&patient_id=<?php echo attr($pid);?>" width="100%" height="475" scrolling="yes"></iframe>
                        </div>
                    </div>
                </div><!-- /.col -->
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-primary collapse" id="profilepanel">
                        <header class="panel-heading"><?php echo xlt('Profile'); ?></header>
                        <div id="profilereport" class="panel-body"></div>
                    <div class="panel-footer"></div>
                    </div>
              </div>
            </div>

        </section>
        <!-- /.content -->
        <!--<div class="footer-main">Onsite Patient Portal Beta v3.0 Copyright &copy By sjpadgett@gmail.com, 2016 All Rights Reserved and Recorded</div>-->
    </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->
<div id="openSignModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="input-group">
                    <span class="input-group-addon"
                          onclick="getSignature(document.getElementById('patientSignaturem'))"><em> <?php echo xlt('Show Current Signature On File'); ?>
                            <br>
                            <?php echo xlt('As appears on documents'); ?>.</em></span> <img
                        class="signature form-control" type="patient-signature"
                        id="patientSignaturem" onclick="getSignature(this)"
                        alt="Signature On File" src="">
                </div>
            </div>
            <div class="modal-body">
                <form name="signit" id="signit" class="sigPad">
                    <input type="hidden" name="name" id="name" class="name">
                    <ul class="sigNav">
                        <label style='display: none;'><input style='display: none;'
                            type="checkbox" class="" id="isAdmin" name="isAdmin" /><?php echo xlt('Is Authorizing Signature');?></label>
                        <li class="clearButton"><a href="#clear"><button><?php echo xlt('Clear Signature');?></button></a></li>
                    </ul>
                    <div class="sig sigWrapper">
                        <div class="typed"></div>
                        <canvas class="spad" id="drawpad" width="765" height="325"
                            style="border: 1px solid #000000; left: 0px;"></canvas>
                        <img id="loading"
                            style="display: none; position: absolute; TOP: 150px; LEFT: 315px; WIDTH: 100px; HEIGHT: 100px"
                            src="sign/assets/loading.gif" /> <input type="hidden" id="output"
                            name="output" class="output">
                    </div>
                    <input type="hidden" name="type" id="type"
                        value="patient-signature">
                    <button type="button" onclick="signDoc(this)"><?php echo xlt('Acknowledge as my Electronic Signature');?>.</button>
                </form>
            </div>
        </div>
        <!-- <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div> -->
    </div>
</div><!-- Modal -->
<img id="waitend"
    style="display: none; position: absolute; top: 100px; left: 260px; width: 100px; height: 100px"
    src="sign/assets/loading.gif" />


</body>
</html>
