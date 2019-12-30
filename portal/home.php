<?php
/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ian Jardine ( github.com/epsdky )
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ian Jardine ( github.com/epsdky )
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("lib/portal_mail.inc");
require_once(dirname(__FILE__)."/../library/appointments.inc.php");


if ($_SESSION['register'] === true) {
    require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
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
//$whereto = 'paymentpanel';

 $user = isset($_SESSION['sessionUser']) ? $_SESSION['sessionUser'] : 'portal user';
 $result = getPatientData($pid);

 $msgs = getPortalPatientNotes($_SESSION['portal_username']);
 $msgcnt = count($msgs);
 $newcnt = 0;
foreach ($msgs as $i) {
    if ($i['message_status'] == 'New') {
        $newcnt += 1;
    }
}

require_once '_header.php';

echo "<script>var cpid=" . js_escape($pid) . ";var cuser=" . js_escape($user) . ";var webRoot=" . js_escape($GLOBALS['web_root']) . ";var ptName=" . js_escape($_SESSION['ptName']) . ";</script>";
?>
<script type="text/javascript">
var webroot_url = webRoot;

$(function () {

    $("#profilereport").load("./get_profile.php", {}, function () {
        $("table").addClass("table  table-responsive");
        $(".demographics td").removeClass("label");
        $(".demographics td").addClass("bold");
        $(".insurance table").addClass("table-sm table-striped");
        $("#editDems").click(function () {
            showProfileModal()
        });
    });

    $("#medicationlist").load("./get_medications.php", {}, function () {});
    $("#labresults").load("./get_lab_results.php", {}, function () {});
    $("#amendmentslist").load("./get_amendments.php", {}, function () {});
    $("#problemslist").load("./get_problems.php", {}, function () {});
    $("#allergylist").load("./get_allergies.php", {}, function () {});
    $("#reports").load("./report/portal_patient_report.php?pid='<?php echo attr_url($pid) ?>'", {}, function () {});

    <?php if ($GLOBALS['portal_two_payments']) { ?>
    $("#payment").load("./portal_payment.php", {}, function () {});
    <?php } ?>

    $(".generateDoc_download").click(function () {
        $("#doc_form").submit();
    });

    function showProfileModal() {
        var title = <?php echo xlj('Demographics Legend Red: Charted Values. Blue: Patient Edits'); ?> + ' ';

        var params = {
            buttons: [
                {text: <?php echo xlj('Help'); ?>, close: false, style: 'info', id: 'formHelp'},
                {text: <?php echo xlj('Cancel'); ?>, close: true, style: 'default'},
                {text: <?php echo xlj('Revert Edits'); ?>, close: false, style: 'danger', id: 'replaceAllButton'},
                {text: <?php echo xlj('Send for Review'); ?>, close: false, style: 'success', id: 'donePatientButton'}
                ],
            onClosed: 'reload',
            type: 'GET',
            url: webRoot + '/portal/patient/patientdata?pid=' + encodeURIComponent(cpid) + '&user=' + encodeURIComponent(cuser)
        };
        dlgopen('','','modal-xl', 500, '', title, params);
    }

    function saveProfile() {
        page.updateModel();
    }

    var gowhere = '#' + <?php echo js_escape($whereto); ?>;
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
        var title = <?php echo xlj('Request New Appointment'); ?>;
        var mdata = {pid:deid};
    }
    else{
        var title = <?php echo xlj('Edit Appointment'); ?>;
        var mdata = {eid:deid};
    }
    var params = {
        dialogId: 'editpop',
        buttons: [
            { text: <?php echo xlj('Cancel'); ?>, close: true, style: 'default' }
        ],
        type:'GET',
        dataType: 'text',
        url: './add_edit_event_user.php',
        data: mdata
    };

    dlgopen('', 'apptModal', 675, 325, '', title, params);
}

function changeCredentials(e) {
    title = <?php echo xlj('Please Enter New Credentials'); ?>;
    dlgopen("./account/index_reset.php", '', 600, 360, null, title, {});
}
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
                        //
                        $current_date2 = date('Y-m-d');
                        $apptLimit = 300;
                        $appts = fetchNextXAppts($current_date2, $pid, $apptLimit);
                        //
                        if ($appts) {
                            $stringCM = "(" . xl("Comments field entry present") . ")";
                            $stringR = "(" . xl("Recurring appointment") . ")";
                            //
                            $count = 0;
                            echo '<table id="appttable" style="width:100%;background:#eee;" class="table table-striped fixedtable"><thead>
                                </thead><tbody>';

                            foreach ($appts as $row) {
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
                                    $etitle = xl('Comments') . ": " . $row ['pc_hometext'] . "\r\n";
                                } else {
                                    $etitle = "";
                                }

                                echo "<tr><td><p>";
                                //
                                if ($row['pc_recurrtype'] == '0') {
                                    echo "<a href='#' class='portal_appt' onclick='editAppointment(0," . attr_js($row ['pc_eid']) . ")" . "' title='" . attr($etitle) . "'>";
                                    $apptClosingTags = "</a></p></td></tr>";
                                }
                                else if ($row['pc_recurrtype'] > '0') {
                                    echo "<span style='color: #000000' title='" . attr($etitle) . "'>";
                                    $apptClosingTags = "</span></p></td></tr>";
                                }
                                //
                                echo "<b>" . text($dayname . ", " . $row ['pc_eventDate']) . "&nbsp;";
                                echo text($disphour . ":" . $dispmin . " " . $dispampm) . "</b>";
                                //
                                if ($row['pc_hometext']) {
                                    $etitleCM = $stringCM . "\r\n\r\n" . $etitle;
                                    echo " <span style='cursor:default;color: green' title='" . attr($etitleCM) . "'>CM</span>";
                                }
                                if ($row['pc_recurrtype'] > '0') {
                                    $etitleR = $stringR . "\r\n\r\n" . $etitle;
                                    echo " <img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . attr($etitleR) . "' alt='" . attr($stringR) . "'>";
                                }
                                //
                                echo "<br><b>" . xlt("Category") . ":</b> " . text($row ['pc_catname']) . "<br><b>";
                                echo xlt("Provider") . ":</b> " . text($row ['ufname'] . " " . $row ['ulname']) . "<br><b>";
                                echo xlt("Status") . ":</b> " . text($status_title);
                                //
                                echo $apptClosingTags;
                            }
                            //
                            if ($count == $apptLimit) {
                                echo "<tr><td><p><span style='color:red'>" . xlt("Arbitrary limit reached") . "<br>" . xlt("More appointments may exist") . "</span></p></td></tr>";
                            }
                            //
                            } else {
                                if ($resNotNull) {
                                    echo xlt('No Appointments');
                                }
                            }

                            echo '</tbody></table>';
                        ?>
                            <div style='margin: 5px 0 5px'>
                                <a href='#' onclick="editAppointment('add',<?php echo attr_js($pid); ?>)">
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
                          <iframe src="./report/pat_ledger.php" width="100%" height="475" scrolling="yes"></iframe>
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
</body>
</html>
