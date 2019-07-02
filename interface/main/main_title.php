<?php
/**
 * main_title.php - The main titlebar, at the top of the 'concurrent' layout.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>

<?php Header::setupHeader();?>
<style type="text/css">
    .hidden {
    display:none;
    }
    .visible{
    display:block;
    }
    a, a:hover, a:visited{
    color: black;
    text-decoration: none;
    }
</style>

<script type="text/javascript" language="javascript">
function toencounter(rawdata, isTherapyGroup) {
//This is called in the on change event of the Encounter list.
//It opens the corresponding pages.
    document.getElementById('EncounterHistory').selectedIndex=0;
    if(rawdata=='')
     {
         return false;
     }
    else if(rawdata=='New Encounter')
     {
        if( parent.left_nav.active_gid == 0){
            // for patient
            top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=');
        } else {
            //for therapy group
            top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newGroupEncounter/new.php?autoloaded=1&calenc=');
        }
        return true;
     }
    else if(rawdata=='Past Encounter List')
     {
        top.window.parent.left_nav.loadFrame2('pel1','RBot','patient_file/history/encounters.php')
        return true;
     }
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];
    var f = top.window.parent.left_nav.document.forms[0];
    frame = 'RBot';
    if (!f.cb_bot.checked) frame = 'RTop'; else if (!f.cb_top.checked) frame = 'RBot';

    top.restoreSession();
    parent.left_nav.setEncounter(datestr, enc, frame);
    top.frames[frame].location.href  = '../patient_file/encounter/encounter_top.php?set_encounter=' + encodeURIComponent(enc);
}

function bpopup() {
 top.restoreSession();
 window.open('../main/about_page.php','_blank', 'width=420,height=350,resizable=1');
 return false;
}

function showhideMenu() {
    var m = parent.document.getElementById("fsbody");
    var targetWidth = '<?php echo $_SESSION['language_direction'] == 'ltr' ? '0,*' : '*,0'; ?>';
    if (m.cols == targetWidth) {
        m.cols = '<?php echo $_SESSION['language_direction'] == 'ltr' ?  attr($GLOBALS['gbl_nav_area_width']) .',*' : '*,' . attr($GLOBALS['gbl_nav_area_width']) ?>';
        document.getElementById("showMenuLink").innerHTML = '<?php echo xla('Hide Menu'); ?>';
    } else {
        m.cols = targetWidth;
        document.getElementById("showMenuLink").innerHTML = '<?php echo xla('Show Menu'); ?>';
    }
}

</script>
</head>
<body class="body_title">
<?php
$res = sqlQuery("select * from users where username=?", array($_SESSION{"authUser"}));
?>
<table id="main-title" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<?php if ($GLOBALS['tiny_logo_1'] || $GLOBALS['tiny_logo_2']) {
    $width_column = "100px";
    if (!$GLOBALS['tiny_logo_1'] || !$GLOBALS['tiny_logo_2']) {
        $width_column = "50px";
    } ?>
    <td align="left" style="width:<?php echo attr($width_column) ?>">
        <div class="tinylogocontainer"><span><?php if ($GLOBALS['tiny_logo_1']) {
            echo $tinylogocode1;
                                             } if ($GLOBALS['tiny_logo_2']) {
                                                 echo $tinylogocode2;
                                             } ?></span></div>
    </td>
<?php } ?>

<td align="left">
    <table cellspacing="0" cellpadding="1" style="margin:0px 0px 0px 3px;">

<?php if (acl_check('patients', 'demo', '', array('write','addonly'))) { ?>
<tr><td style="vertical-align:text-bottom;">
        <a href='' class="css_button_small" style="margin:0px;vertical-align:top;" id='new0' onClick=" return top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php')">
        <span><?php echo xlt('NEW PATIENT'); ?></span></a>
    </td>
    <td style="vertical-align:text-bottom;">
            <a href='' class="css_button_small" style="margin:0px;vertical-align:top;display:none;" id='clear_active' onClick="javascript:parent.left_nav.clearactive();return false;">
            <span><?php echo xlt('CLEAR ACTIVE PATIENT'); ?></span></a>
    </td>
    <td style="vertical-align:text-bottom;">
        <a href='' class="css_button_small" style="margin:0px;vertical-align:top;display:none;" id='clear_active_group' onClick="javascript:parent.left_nav.clearactive();return false;">
            <span><?php echo xlt('CLEAR ACTIVE THERAPY GROUP'); ?></span></a>
    </td>
</tr>
<?php } //end of acl_check('patients','demo','',array('write','addonly') if ?>

    <tr><td valign="baseline"><B>
        <a class="text" style='vertical-align:text-bottom;' href="main_title.php" id='showMenuLink' onclick='javascript:showhideMenu();return false;'><?php echo xlt('Hide Menu'); ?></a></B>
    </td></tr></table>


<?php //RP_MODIFIED 2016-05-06

$open_emr_ver = "$v_major.$v_minor.$v_patch";
$url = "open-emr.org/wiki/index.php/OpenEMR_".$open_emr_ver."_Users_Guide";

?>
</td>
<td style="margin:3px 0px 3px 0px;vertical-align:middle;">
        <div style='margin-left:10px; float:left; display:none' id="current_patient_block">
            <span class='text'><?php echo xlt('Patient'); ?>:&nbsp;</span><span class='title_bar_top' id="current_patient"><b><?php echo xlt('None'); ?></b></span>
        </div>
</td>
<td style="margin:3px 0px 3px 0px;vertical-align:middle;" align="left">
    <table cellspacing="0" cellpadding="1" ><tr><td>
        <div style='margin-left:5px; float:left; display:none' id="past_encounter_block">
            <span class='title_bar_top' id="past_encounter"><b><?php echo xlt('None'); ?></b></span>
        </div></td></tr>
    <tr><td valign="baseline" align="center">
        <div style='display:none' class='text' id="current_encounter_block" >
            <span class='text'><?php echo xlt('Selected Encounter'); ?>:&nbsp;</span><span class='title_bar_top' id="current_encounter"><b><?php echo xlt('None'); ?></b></span>
        </div></td></tr></table>
</td>

<td align="right">
    <table cellspacing="0" cellpadding="1" style="margin:0px 3px 0px 0px;">
        <tr>
            <td align="right" class="text" style="vertical-align:text-bottom;">
                <a href='main_title.php' onclick="javascript:parent.left_nav.goHome();return false;" title = "<?php echo xla('Home'); ?>"><i class='fa fa-home fa-2x top-nav-icons' aria-hidden='true'></i></a>
                <a href="https://<?php echo attr($url); ?>" rel="noopener" target="_blank" id="help_link" title = "<?php echo xla('Manual'); ?>"><i class='fa fa-question fa-2x top-nav-icons' aria-hidden='true'></i></a>
                <a href="" onclick="return bpopup()"  title="<?php echo xla('About'); ?>"><i class='fa fa-info fa-2x top-nav-icons' aria-hidden='true'></i></a>
                <a href="" id="user_settings" onclick="userPreference(); return false;" title="<?php echo xla('User Settings')?>"><i class="fa fa-cog fa-2x top-nav-icons" aria-hidden="true"></i></a>
                <a href="" id="user_password" onclick="changePassword(); return false;" title="<?php echo xla('User Password')?>"><i class="fa fa-unlock-alt fa-2x top-nav-icons" aria-hidden="true"></i></a>
                <a href="../logout.php" target="_top"  id="logout_link" onclick="top.restoreSession()" title = "<?php echo xla('Logout') ?>"><i class="fa fa-sign-out fa-2x top-nav-icons" aria-hidden="true"></i></a>
            </td>
        </tr>
        <tr>
            <td colspan='2' valign="baseline" align='right'>
                <span class="text title_bar_top" title="<?php echo attr(xl('Authorization group').': '.$_SESSION['authGroup']); ?>"><a href='main_title.php' onclick="<?php echo $javascript;?>" title=""><?php echo text($res{"fname"}.' '.$res{"lname"}); ?></a></span>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>

<script type="text/javascript" language="javascript">
parent.loadedFrameCount += 1;


function userPreference() {
    top.restoreSession();
    top.frames['RTop'].location='../super/edit_globals.php?mode=user';
    top.frames['RBot'].location='../main/messages/messages.php?form_active=1';
}
function changePassword() {
    top.restoreSession();
    top.frames['RTop'].location='../usergroup/user_info.php';
    top.frames['RBot'].location='messages/messages.php?form_active=1';
}

</script>

</body>
</html>
