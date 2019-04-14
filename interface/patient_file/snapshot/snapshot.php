<?php
/**
 * View snapshot of a patient - container page.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.js.php");

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
?>
<html>
<head>
    <title><?php echo xlt("Snapshot"); ?></title>
    <?php Header::setupHeader('common'); ?>
    <style>
    .hide_div {
        display:none;
    }
    /*.label-div > a {
    display:none;
    }
    .label-div:hover > a {
       display:inline-block; 
    }*/
    div[id$="_info"] {
        background: #F7FAB3;
        padding: 20px;
        margin: 10px 15px 0px 15px;
    }
    div[id$="_info"] > a {
        margin-left:10px;
    }
    </style>

<script type="text/javascript">
$(document).ready(function(){
    tabbify();
});
<?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
</script>


<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Snapshot'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "snapshot_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class="body_top">

<div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <?php
    if (acl_check('patients', 'med', '', array('write','addonly'))) {?>
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php
                $list_id = "snapshot"; // to indicate nav item is active, count and give correct id
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
        
    <?php
    } ?>
    <div class="row">
        <div class="col-sm-4">
            <select name="sel-snapshot" id = "sel-snapshot" class="form-control">
            <option value="0"><?php echo "--- " . xlt('Select snapshot to load') . " ---"; ?></option>
            <option value="finan~educa-1"><?php echo xlt('Financial and Education Snapshot'); ?></option>
            <option value="physi~alcoh-1"><?php echo xlt('Physical Activity and Alcohol Snapshot'); ?></option>
            <option value="stres~depre-1"><?php echo xlt('Stress and Depression Snapshot'); ?></option>
            <option value="socia-1"><?php echo xlt('Social Connection and Isolation Snapshot'); ?></option>
            <option value="viole-1"><?php echo xlt('Exposure to violence - HARK Snapshot'); ?></option>
            </select>
        </div>
        <div  class="col-sm-2" id='view-dates' name=id='view-dates'>
            <?php //jquery ajax to load form snapshot_dates select box?>
        </div>
        <div class="col-sm-2 hide_div" name="refresh_div" id="refresh_div">
            <button type="button" class="btn btn-default btn-refresh" name='refresh_btn' id='refresh_btn' ><?php echo xlt('Refresh');?></button>
        </div>
    
    </div>
    <br>
    <div class="row">
        <div class="col-sm-12" id="view-snapshot" name="view-snapshot">
            <?php //jquery ajax to load form snapshot_detail_?>
        </div>
    </div>
</div><!--end of container div -->
<?php $oemr_ui->oeBelowContainerDiv();?>
<script>
    var listId = '#' + <?php echo js_escape($list_id); ?>;
    $(document).ready(function(){
        $(listId).addClass("active");
    });
</script>
<script>
$(document).ready(function(){
    $(document).on('change', '#sel-snapshot', function(){
        var ssDate = $('#date-snapshot').val();
        var ssOptionValue = $('#sel-snapshot').val();
        if (ssOptionValue == '0'){
            $('#refresh_div').addClass('hide_div');
            $('#view-dates').addClass('hide_div');
            $('#view-snapshot').addClass('hide_div');
            return false;
        } 
            var ssSelValues = ssOptionValue.split("-");
            var ssSnapshots = ssSelValues[0];
            var ssSelVal = ssSelValues[1];
                  
        if(typeof ssDate =='string'){
            $('#refresh_div').removeClass('hide_div');
            $('#view-dates').removeClass('hide_div');
            $('#view-snapshot').removeClass('hide_div');
            $('#snapshot_details_div').removeClass('hide_div');
            var previuosSnapshot = $('#snapshot_' + ssSelVal +'_type').val();
            if (previuosSnapshot != ssSnapshots){
                //$('#view-dates').load("snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" 
                $('#view-dates').load("snapshot_" + ssSelVal + "/snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" 
                + ssSnapshots + "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>,  function() {
                ssDate = $('#date-snapshot').val();
                
                var fileName = "snapshot_" + ssSelVal + "/snapshot_detail_" + ssSelVal + ".php?ss_date=" + ssDate + "&pid=" + <?php echo $pid;?>
                + "&snapshot=" + ssSnapshots + "&authid=" + <?php echo $_SESSION['authId'];?> +  "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>;
                $('#view-snapshot').load(fileName);
                });
            }
            var fileName = "snapshot_" + ssSelVal + "/snapshot_detail_" + ssSelVal + ".php?ss_date=" + ssDate + "&pid=" + <?php echo $pid;?> 
            + "&snapshot=" + ssSnapshots + "&authid=" + <?php echo $_SESSION['authId'];?> +  "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>;
            $('#view-snapshot').load(fileName);
        } else {
            //$('#view-dates').load("snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" + ssSnapshots 
            $('#view-dates').load("snapshot_" + ssSelVal + "/snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" + ssSnapshots
            + "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>,  function() {
                ssDate = $('#date-snapshot').val();
                var fileName = "snapshot_" + ssSelVal + "/snapshot_detail_" + ssSelVal + ".php?ss_date=" + ssDate + "&pid=" + <?php echo $pid;?> +
                "&snapshot=" + ssSnapshots + "&authid=" + <?php echo $_SESSION['authId'];?> +  "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>;
                if (typeof ssDate =='undefined'){// just for testing delete
                    $('#refresh_div').addClass('hide_div');
                    $('#view-snapshot').load(fileName);
                    $('#snapshot_details_div').addClass('hide_div');
                } else {
                    $('#view-snapshot').load(fileName);
                    $('#refresh_div').removeClass('hide_div');
                    $('#view-dates').removeClass('hide_div');
                    $('#view-snapshot').removeClass('hide_div');
                }
            });
        }
    });
     
    $(document).on('change', '#date-snapshot', function(){
        var ssDate = $('#date-snapshot').val();
        var ssSelValues = $('#sel-snapshot').val().split("-");
        var ssSnapshots = ssSelValues[0];
        var ssSelVal = ssSelValues[1];
        var fileName = "snapshot_" + ssSelVal + "/snapshot_detail_" + ssSelVal + ".php?ss_date=" + ssDate + "&pid=" + <?php echo $pid;?> + "&snapshot=" 
        + ssSnapshots + "&authid=" + <?php echo $_SESSION['authId'];?> +  "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>;
        $('#view-snapshot').load(fileName);
    });
    
    $(document).on('click', '#refresh_btn', function(){
        var ssDate = $('#date-snapshot').val();
        var ssSelValues = $('#sel-snapshot').val().split("-");
        var ssSnapshots = ssSelValues[0];
        var ssSelVal = ssSelValues[1];
        //$('#view-dates').load("snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" + ssSnapshots 
        $('#view-dates').load("snapshot_" + ssSelVal + "/snapshot_" + ssSelVal + "_dates.php?pid=" + <?php echo $pid;?> + "&snapshot=" + ssSnapshots
        + "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>, function() {
            var fileName = "snapshot_" + ssSelVal + "/snapshot_detail_" + ssSelVal + ".php?ss_date=" + ssDate + "&pid=" + <?php echo $pid;?> + "&snapshot=" + ssSnapshots + "&authid=" + <?php echo $_SESSION['authId'];?> + "&csrf_token_form=" + <?php echo js_url(collectCsrfToken()); ?>;
            $('#view-snapshot').removeClass('hide_div');
            $('#view-dates').removeClass('hide_div');
            $('#view-snapshot').load(fileName);
         });
    });
});
</script>

</body>
</html>
