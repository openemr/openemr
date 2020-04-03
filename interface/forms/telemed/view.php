<?php
/**
 * transfer summary form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

require_once("$srcdir/MedEx/API.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$MedEx = new MedExApi\MedEx('MedExBank.com');

$form_name   = "telemed";
$form_folder = "telemed";
$Form_Name   = "TeleHealth Visit";
$action      = $_REQUEST['action'];
$id          = $_REQUEST['id'];
$display     = $_REQUEST['display'];
$pid         = $_SESSION['pid'];
$refresh     = $_REQUEST['refresh'];


formHeader("TeleHealth Visit");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_telemed", $formid) : array();

$query          = "SELECT * FROM patient_data where pid=?";
$pat_data       =  sqlQuery($query, array($pid));

$query10 = "select  *,form_encounter.date as encounter_date
               from forms,form_encounter,form_telemed
               where
                    forms.deleted != '1'  and
                    forms.formdir='telemed' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_telemed.id and
                    form_telemed.id =? ";

$encounter_data =sqlQuery($query10, array($id));
$tm_duration = $encounter_data['tm_duration'];
$tm_subj = $encounter_data['tm_subj'];
$tm_obj = $encounter_data['tm_obj'];
$tm_imp = $encounter_data['tm_imp'];
$tm_plan = $encounter_data['tm_plan'];

$dated = new DateTime($encounter_data['encounter_date']);
$dated = $dated->format('Y-m-d');
$visit_date = oeFormatShortDate($dated);

$query = "select * from openemr_postcalendar_events where pc_pid=? and pc_eventDate=?";
$appt = sqlQuery($query, array($pid,$encounter_data['encounter_date']));
$reason = $appt['pc_hometext'];

$query          = "SELECT * FROM users where id = ?";
$prov_data      = sqlQuery($query, array($appt['pc_aid']));
$id = $form_id;

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet"  href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">

    <?php Header::setupHeader(''); ?>
    <?php $GLOBALS['medex_enable']='';
        if (($GLOBALS['medex_enable'] == '1')) {
            $logged_in = $MedEx->login('1');
            if (empty($_REQUEST['nomenu'])) {
                $MedEx->display->navigation($logged_in);
            }
            //ask MedEx to build a room and send room keys based on my plan (JWT or otherwise);
            $data['pid']            = $pid;
            $data['providerID']     = $appt['pc_aid'];
            $data['eid']            = $appt['pc_eid'];
            $data['token']          = $logged_in['token'];
            $data['pat_fname']      = $pat_data['fname'];
            $data['pat_lname']      = $pat_data['lname'];
            $data['pat_email']      = $pat_data['email'];
            $data['pat_mobile']     = $pat_data['mobile'];
            $data['pat_allowemail'] = $pat_data['hipaa_allowemail'];
            $data['pat_allowsms']   = $pat_data['hipaa_allowsms'];
    
            $MedEx->curl->setUrl($MedEx->getUrl('custom/TM_bot'));
            $MedEx->curl->setData($data);
            $MedEx->curl->makeRequest();
            $TM = $MedEx->curl->getResponse();
        } else {
            $token = openssl_random_pseudo_bytes(4);
            $token = bin2hex($token);
            //Brady: add a global to link to other tele-providers like Doxy.me or Zoom?
            //For now this links to public servers jitsi-meet  maintainers server.
            //Room must be locked to be HIPAA compliant.
            $TM['url_provider'] = 'https://meet.jit.si/'.$token;
            $TM['url_patient'] = 'https://meet.jit.si/'.$token;
            $TM['label_url_patient'] = 'https://meet.jit.si/'.$token;
            $TM['label_url_provider'] = 'Open Jitsi-Meet'; //xlt below
        }
        
        $visit_header = "TeleVisit";
        if ($logged_in) {
            $visit_header = "TeleMedEx Visit: secure session";
        } else {
            $visit_header = "TeleHealth Visit: Jitsi.org server";
        }
        $arrOeUiSettings = array(
            'heading_title' => xl($visit_header),
            'include_patient_name' => false,// use only in appropriate pages
            'expandable' => false,
            'expandable_files' => array(),//all file names need suffix _xpd
            'action' => "",//conceal, reveal, search, reset, link or back
            'action_title' => "",
            'action_href' => "",//only for actions - reset, link or back
            'show_help_icon' => false,
            'help_file_name' => "Tele_help.php"
        );
        
        $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>

</head>

<body class="body_top">
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 flex">
                
                <div class="row">
                    <ol start="1"><li> <?php
                                if ($GLOBALS['medex_enable']) {
                                    echo xlt('When you are ready to begin your TeleMedEx session');
                                } else {
                                    echo xlt('When you are ready to begin your Jitsi-Meet session');
                                }
                            ?>: </li>
                            <ul>
                                <li> <?php echo xlt('Start the session timer if desired'); ?></li>
                                <li> <?php
                                        if ($GLOBALS['medex_enable']) {
                                            echo xlt('Open your TeleMedEx Waiting Room');
                                        } else {
                                            echo xlt('Open your Jitsi-Meet session')."</li>";
                                            echo "<span class='oe-bold-black small'> ".xlt('Only the first person to enter can lock the room - it should be you')."...</span>";
                                        }
                                        ?>.</li>
                                &nbsp;
                                     <div class="text-center">
                                        <button class="btn btn-primary" onclick="timer();" id="startT"><?php echo xlt('Start Timer'); ?></button>
                                        <button class="btn btn-primary" onclick="window.open('<?php echo $TM['url_provider']; ?>', '_blank', 'height=400,width=750');">
                                            <i class=" fa fa-user-md"></i>
                                            <?php echo $TM['label_url_provider']; ?></button>
                                    </div>
                            </ul>&nbsp; <br /> &nbsp;
                        <br />
                    </ol>

                </div>
                <div class="row">
                    <ol start="2">
                        <li> <?php
                                if ($GLOBALS['medex_enable']) {
                                    echo xlt('Share your Waiting Room URL with your patient');
                                } else {
                                    echo xlt('Share your Jitsi-Meet Random Room URL with your patient');
                                }
                            ?>.</li>
                        <ul>
                            <li><span class="oe-bold-black"><i class="fa fa-user"></i> <?php echo $TM['url_patient']; ?>  &nbsp; <i class="fa fa-copy js-copy-pat-btn" title="Click to copy to clipboard"></i></span></li>
                            <li><?php
                                if ($GLOBALS['medex_enable']) {
                                    echo xlt('The patient will follow this link and sign into the waiting room');
                                } else {
                                    echo xlt('The patient will follow this link to meet you in the Room');
                                }
                                    ?>.</li>
                            <?php if ($GLOBALS['medex_enable']) { ?>
                            <li>  The MedEx Waiting Room will alert you they have arrived.</li>
                            <li>  Click <em>Create the Exam Room</em> to begin the TeleMedEx Visit</li>
                            <?php } ?>
                        </ul>
                    </ol>
                </div>
                <div class="row">
                    <ol start="3">
                        <li> <?php echo xlt('Once the patient has joined, lock the Exam Room'); ?>:</li>
                            <ul>
                                <li> <?php echo xlt('Click the')." <i class=\"glyphicon glyphicon-glyphicon-info-sign\" data-unicode=\"e086\"></i> ". xlt('icon (bottom right in the telemedicine window)'); ?></li>
                                <li> <?php echo xlt('Click on')."  <span class='bold'>".xlt('Add Password'); ?></span></li>
                                <li> <?php echo xlt('Type in the password and press Enter to lock the room'); ?></li>
                                <li> <?php echo xlt('No one can re-enter without this password'); ?></li>
                                <li> <?php echo xlt('Share the password with the patient should you encounter network problems'); ?></li>
                            </ul>
                    </ol>
                </div>
                <div class="row">
                    <ol start="4">
                        <li> <?php echo xlt('The room is destroyed when you end the session'); ?>. </li>
                        <li> Return to this form to document and save the encounter as a TeleHealth Visit</li>
                    </ol>
                </div>
            </div>
            <div class="text-center col-sm-6 offset-1">
                <form method='post' name='tm_form' action='<?php echo $rootdir."/forms/telemed/save.php?id=" . attr_url($formid); ?>'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="card card-body bg-light text-center">
                    <div class="card-text">
                        <table id="printableArea" border="0">
                            <tr>
                                <td align="left"><?php echo xlt('Patient'); ?>:</td>
                                <td>
                                    <label> <?php
                                            if (is_numeric($pid)) {
                                                $result = getPatientData($pid, "fname,lname,squad");
                                                echo text($result['fname'])." ".text($result['lname']);
                                            }
                                            $patient_name=($result['fname'])." ".($result['lname']);
                                        ?>
                                    </label>
                                    <?php echo xlt('DOB'); ?>:
                                    <label class="forms-data"> <?php if (is_numeric($pid)) {
                                            $result = getPatientData($pid, "*");
                                            echo text($result['DOB']);
                                        }
                                            $dob=($result['DOB']);
                                        ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td align="text-right"><?php echo xlt('Duration'); ?>:</td>
                                <td><input type="text"
                                           id="duration"
                                           name="tm_duration"
                                           class="small"
                                           placeholder="<?php echo xla('Visit duration '); ?>"
                                           value="<?php echo attr($tm_duration); ?>">
                                    <span class="btn btn-primary" id="stop"><?php echo xlt('Stop'); ?></span>
                                    <span class="btn btn-primary" id="clear"><?php echo xlt('Reset'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td align="text-right" class="forms"><?php echo xlt('Subjective'); ?>:</td>
                                <td class="forms">
                                    <textarea type="text"  rows="1" cols="40"
                                              name="tm_subj"
                                              class=" form-control"
                                              id="subjective"><?php echo text($obj['subjective']);?><?php echo text($tm_subj); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td align="text-right" class="forms"><?php echo xlt('Objective'); ?>:</td>
                                <td class="forms">
                                    <textarea type="text"  rows="2" cols="40"
                                              name="tm_obj"
                                              class=" form-control"
                                              id="objective"><?php echo text($obj['objective']);?><?php echo text($tm_obj); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td align="text-right" class="forms"><?php echo xlt('Impression'); ?>:</td>
                                <td class="forms">
                                    <textarea type="text" rows="3" cols="40"
                                              name="tm_imp"
                                              class=" form-control"
                                              id="assessment"><?php echo text($obj['assessment']);?><?php echo text($tm_imp); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td align="text-right" class="forms"><?php echo xlt('Plan'); ?>:</td>
                                <td class="forms">
                                    <textarea type="text" rows="3" cols="40"
                                              class=" form-control"
                                              name="tm_plan"
                                              id="plan"><?php echo text($obj['plan']);?><?php echo text($tm_plan); ?></textarea>
                                </td>
                            </tr>
                            <tr><td align="center" colspan="2"><button class="btn btn-primary" style="float:unset;" id="save"><?php echo xlt('Save');?></button>
                                    </td></tr>
                        </table>
                        
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    function copyTextToClipboard(text) {
        if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            console.log('Async: Copying to clipboard was successful!');
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
        });
    }
    
    var copyPatBtn = document.querySelector('.js-copy-pat-btn')
    copyPatBtn.addEventListener('click', function(event) {
        copyTextToClipboard('<?php echo $TM['url_patient']; ?>');
    });
    
    var duration = document.getElementById('duration'),
        startT = document.getElementById('startT'),
        stop = document.getElementById('stop'),
        clear = document.getElementById('clear'),
        seconds = 0, minutes = 0, hours = 0,
        t;
    
    function add() {
        seconds++;
        if (seconds >= 60) {
            seconds = 0;
            minutes++;
            if (minutes >= 60) {
                minutes = 0;
                hours++;
            }
        }
        
        duration.value = (hours ? (hours > 9 ? hours : "0" + hours) : "00") + ":" + (minutes ? (minutes > 9 ? minutes : "0" + minutes) : "00") + ":" + (seconds > 9 ? seconds : "0" + seconds);
        
        timer();
    }
    function timer() {
        //alert('timer started');
        t = setTimeout(add, 1000);
    }
    

    /* Start button */
    //$("#startT").onclick = timer();

    /* Stop button */
    stop.onclick = function() {
        clearTimeout(t);
    }

    /* Clear button */
    clear.onclick = function() {
        duration.value = "00:00:00";
        seconds = 0; minutes = 0; hours = 0;
    }

    function printDiv(divname)
    {
        var printContents = document.getElementById(divname).innerHTML;
        var originalContents = document.body.innerHTML;
        
        document.body.innerHTML = originalContents;
        window.print();
    }
</script>
</html>