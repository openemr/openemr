<?php
    /**
     *  /interface/main/mobile/SMS.php
     *
     *  Live SMS interface for OpenEMR via MedEx
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
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
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
require_once('../../globals.php');
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once("m_functions.php");
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Core\Header;

$MedEx = new MedExApi\MedEx('medexbank.com');

$detect = new Mobile_Detect;
$device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version = $detect->getScriptVersion();

$desktop ="";
$categories = array();
$display="sms";
$doc =array();

if (!empty($_GET['desktop'])) {
    $desktop = $_GET['desktop'];
}

// If “Go to full website” link is clicked, redirect mobile user to main website
if (!empty($_SESSION['desktop']) || ($device_type == 'computer')) {
    $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
    header("Location:" . $desktop_url);
}

?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>

<body style="background-color: #fff;" >
<?php common_header($display); ?>
<div class="container-fluid">
    
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
            <img src="<?php echo $webroot; ?>/public/images/SMSBot.png" id="head_img" alt="OpenEMR <?php echo xla('SMS Bot'); ?>">
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
            
                    <?php
   // echo "<pre>".$GLOBALS['medex_enable'];
    
if ($GLOBALS['medex_enable'] == '1') {
    $logged_in = $MedEx->login('1');
    //var_dump($logged_in['status']);
    $logged_in['status']['display']="mob";
    $MedEx->display->SMS_bot($logged_in['status']);
} else {
    echo '<span class="well">'. xlt("Your site is not enabled for SMS through MedEx").'</span>';
}
?>      </div></div></div>
        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7  jumbotronA custom-file-upload">
            <div id="preview" class="">
                <div id="Content" class="Content">
                    <div id="PID_contact" class="line_1_style">
                        <span style="position:relative; float:left;"><i class="left fa fa-signal"></i>&nbsp;MedEx</span>
                       
                        <span style="font-weight:600;margin:0px auto;"><?php
                                $now_time 		= date("Y-m-d H:i:s");
                                echo oeFormatTime($now_time); ?> </span>
                        <?php
                            if ($count_news) {
                                ?>
                                <span style="position: relative;
									float:right;
									margin-right:10px;
									animation: blink 3s infinite;
									color: red;font-weight:600;">
									    <span onclick="goNews();">
										    <blink><i class="fa fa-bolt red" >&nbsp;<?php echo xlt('New'); ?></i></blink>
									    </span>
							        </span>
                                <?php
                            }
                        ?>
                    </div>
                    
                    <div id="nav_contact" class="line_2_style">
                            <?php
                                if (empty($data['show'])) { ?>
                                
									<span  class="pname" id="pname">
									<?php
                                        $name =  $fname." ".$lname;
                                        $name = (strlen($name) > 20) ? substr($name,0,17).'...' : $name;
                                        echo text($name." ".$who['p_phone_cell']);
                                    ?>
									</span>
                                    <span id="new_SMS_icon" class="pname" style="position: absolute;right: 5px;"
                                           onclick="goNew_SMS();">
									    <i class="fa fa-users"></i>
								    </span>
                                    <?php
                                } else if ($data['show']=="new") {
                                    ?>
                                    <span style="position:relative;float:left;top: 3px;" onclick="goBack();"><span class="glyphicon glyphicon-chevron-left"></span></span>
                      
									<span style="position:relative;margin: 0px 30px 0px 0px;">
										<?php echo xlt('New Messages'); ?>
									</span>
                         
                                    <span style="position: relative;float: right;" onclick="goNew_SMS();">
									<i class="fa fa-users"></i>
								</span>
                                    <?php
                                } else {
                                    if ($data['show'] != 'list') {
                                        echo '<span style="position: relative;float: left;top: 4px;" onclick="goNews();">
										<i class="fa fa-list"></i>
										</span>
									';
                                    }
                        
                                    ?>
                        
                             
									<span style="position:relative;margin: 0px 30px 0px 0px;">
										<?php
                                            if ($_REQUEST['dfrom']) {
                                                $name =  text($_REQUEST['dfrom']." through ".$_REQUEST['dto']);
                                                //$name = (strlen($name) > 23) ? substr($name,0,20).'...' : $name;
                                                echo $name;
                                            } else {
                                                echo xlt("Messaging Center");
                                            }
                                        
                                        ?>
									</span>
                              
                                    <span style="position: relative;float: right;" onclick="goNew_SMS();">
									<i class="fa fa-users"></i>
								</span>
                        
                                    <?php
                                } ?>
                        </div>
                </div>
                <div id="search_data_right">
                    <a href="https://medexbank.com/index.html" class="logo">
                        <img src="https://medexbank.com/images/MedEx2.1.png"
                             target="_blank"></a><br />
                    <h2>SMS: <?php echo xlt('Patient Search'); ?></h2>
                        <ul class="text-left" style="margin:0px auto 30px;width: 50%;">
                            <li id="short_search"> <?php echo xlt('min 3 characters required'); ?></li>
                            <li> <?php echo xlt('search first or last names'); ?></li>
                            <li> <?php echo xlt('select Patient to begin SMS session'); ?></li>
                        </ul>
                </div>
            
                <div id="message_data_right"></div>
                <div id="search_SMS" class="line_bottom_style">
                    <input style="width: 210px;
                                                        height:29px;
                                                        margin: 2px 5px;
                                                        background-color: #fff;
                                                        border-radius: 5px;
                                                        padding: 4px;
                                                        position: relative;
                                                        float: left;
                                                        max-height: 150px;
      overflow-x: hidden;
      overflow-y: auto;border: 1px solid #ccc;"
                           name="outpatient" id="outpatient" placeholder="<?php echo xla('Search by name'); ?>..." autofocus />
                    <input type="submit" class="fa fa-arrow-up btn btn-primary pull-right"
                           style="font-size: 1em;"
                           src="#"
                           id="sms_search"
                           onclick="search4SMS()"
                           value="<?php echo xla('Search'); ?>" />
                </div>
                <div id="NEW_SMS" class="line_bottom_style">
                    <input type="submit" class="fa fa-arrow-up btn btn-primary"
                           style="position:relative;float:right;top: -2px;margin-right:15px;
                                                    padding: 6px;font-weight:700;color:white;font-size: 1em;"
                           src="#"
                           id="sms_submit"
                           onclick="sendSMS();"
                           value="<?php echo xla('Send'); ?>" />
                    <input type="hidden" id="local_pid" value="" />
                    <input type="hidden" id="msg_last_updated" value="0" />
                    <input type="hidden" id="medex_uid" value="" />
                    <textarea style="width: calc(100% - 80px);
                                                        height:29px;
                                                        margin: 2px 5px;
                                                        background-color: #fff;
                                                        border-radius: 5px;
                                                        padding: 4px 6px 3px;
                                                        position: relative;
                                                        float: right;
                                                        resize: none;" value=""
                                  name="sms_text" id="sms_text" placeholder="<?php echo xla('Text Message'); ?>" autofocus></textarea>
                </div>
            </div>
        </div>
    
</div>
<script>
    var pid;
    function goForward(pid) {
        top.restoreSession();
        $("#NEW_SMS").show();
        $('#search_data_right').hide();
        $('#message_data_right').show();
        $("#new_SMS_icon").show();
    
        if (pid =='') return false;
        $('#message_data_right').html('<div class="text-center">\n'+
'                        <i class="fa fa-spinner fa-pulse fa-fw" style="font-size: 140px; color: #0000cc; padding: 20px"></i>\n'+
'                        <h2><?php echo xlt('Loading data'); ?>...</h2>\n'+
'                    </div>');
        
        
        $("#message_data div").css("background-color", "transparent");
        //get the name/number for display
        $.ajax({
                   type: "POST",
                   url: "m_save.php?go=sms_search&term="+pid
               }).done(function(result) {
                console.log(result);
                results = JSON.parse(result);
                if (!results.mobile) {
                    results.mobile = results.home_phone + "  - <?php echo xlt('Home'); ?>";
                } else {
                    results.mobile = results.mobile + " - <?php echo xlt('Mobile'); ?>";
                }
                $('#pname').html(results.value+' @'+results.mobile);
                pid = results.pid;
                $("#local_pid").val(results.pid);
            $("#msg_last_updated").val(results.msg_last_updated);
            $("#medex_uid").val(results.medex_uid);
    
            //if (results.allow == 'No') {
              //  $("#sms_search").prop('disabled', true);
            //}
            // $data['mobile'] = $frow['phone_cell'];
            //        $data['allow'] = $frow['hipaa_allowsms'];
            //
            //$("#local_pid").val(results.pid);
            
        });
        
        //go get the messages + lastupdated for refreshTable
         $.ajax({
                    type: "POST",
                   url: "m_save.php?nomenu=1",
                   data: {
                       pid 		: pid,
                       go       : 'SMS_bot',
                       SMS_bot  : '1',
                       r        : '1'
                   }
               }).done(function(result) {
                   console.log(result);
                   //results = JSON.parse(result);
        
                
             $('#message_data_right').html(result);
             $("#message_data_right").scrollTop(function() { return this.scrollHeight; });
             $("#search_SMS").hide();
             $("#search_data_right").hide();
             $("#NEW_SMS").show();
             $("#message_data_right").show();
    
             timing = 5000;
             refreshTable(pid,5000);
         });
    
    }
    function goNew_SMS() {
        $('#message_data_right').hide();
        $('#search_data_right').show();
        $("#search_SMS").show();
        $("#NEW_SMS").hide();
        $("#new_SMS_icon").hide();
        $("#pname").html('');
        pid='';
    }
    
    function sendSMS() {
        top.restoreSession();
        $("#sms_text").html('<i class="fa-spinner"></i>');
        //$("#sms_text").attr('placeholder','...');
        alert('here it comes');
   // alert(pid);
    //if (pid < 2) { return; }
        $.ajax({
                   type: "POST",
                   url: "m_save.php?nomenu=1",
                   data: {
                       pid 		: $("#local_pid").val(),
                       action   : 'send_SMS',
                       SMS_bot 	: '1',
                       msg_txt	: $("#sms_text").val(),
                       r 		: '1'
                   }
               }).done(function(result) {
            $("#sms_text").val('');
            $("#sms_text").attr('placeholder','...');
            $('#message_data_right').html(result);
            $("#message_data_right").scrollTop(function() { return this.scrollHeight; });
        
            timing = 5000;
            refreshTable($("#local_pid").val(),5000);
        });
    }
    <?php common_js(); ?>
    $(document).ready(function () {
        $("#NEW_SMS").hide();
        $("#new_SMS_icon").hide();
        var myDiv = document.getElementById("message_data");
        window.scrollTo(0, myDiv.innerHeight);
        $("#message_data").scrollTop($("#message_data")[0].scrollHeight);
        $("#search_SMS").show();
        $("#search_data_right").show();
        $("#message_data_right").hide();
        $("#message_data div").click(function() {
            $(this).css("background", "#fffef1");
        });
    });
</script>
<?php
common_footer($display);

