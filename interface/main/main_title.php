<?php
include_once("../globals.php");
?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" language="javascript">
function toencounter(rawdata) {
//This is called in the on change event of the Encounter list.
//It opens the corresponding pages.
	document.getElementById('EncounterHistory').selectedIndex=0;
	if(rawdata=='')
	 {
		 return false;
	 }
	else if(rawdata=='New Encounter')
	 {
	 	top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=')
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
<?php if ($GLOBALS['concurrent_layout']) { ?>
    parent.left_nav.setEncounter(datestr, enc, frame);
    parent.left_nav.setRadio(frame, 'enc');
    top.frames[frame].location.href  = '../patient_file/encounter/encounter_top.php?set_encounter=' + enc;
<?php } else { ?>
    top.Title.location.href = '../patient_file/encounter/encounter_title.php?set_encounter='   + enc;
    top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
}

</script>
</head>
<body class="body_title">

<?php
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="middle" class = 'text'><a href='' class="css_button_small" id='new0' style="font-weight:bold" onClick=" return top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php')"><span><?php echo htmlspecialchars( xl('NEW PATIENT'), ENT_QUOTES) ?></span></a></td>
<td valign="middle">
        <div style='margin-left:10px; float:left; display:none' id="current_patient_block">
            <span class='text'><?php xl('Patient','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_patient"><b><?php xl('None','e'); ?></b></span>
        </div>
</td>
<td valign="middle">
        <div style='margin-left:10px; float:left; display:none' id="past_encounter_block">
		<span class='title_bar_top' id="past_encounter"><b><?php echo htmlspecialchars( xl('None'), ENT_QUOTES) ?></b></span></td>
        </div>
</td>
<td valign="middle">
        <div style='margin-left:5px; float:left; display:none' class='text' id="current_encounter_block" >
            <span class='text'><?php xl('Encounter','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_encounter"><b><?php xl('None','e'); ?></b></span>
        </div>
</td>

<td valign="middle">
    <div style='float:right; margin-left:5px'>
        <div style='float:left; margin-top:3px' class = 'text'>
            <a href="javascript:;" onclick="javascript:parent.left_nav.goHome();" ><?php xl('Home','e'); ?></a>
            &nbsp;|&nbsp;
            <a href="../../Documentation/User_Guide/" target="RTop" id="help_link" onclick="top.restoreSession()"> <?php xl('Manual','e'); ?></a>
            <br>
            <span class='text'><?php xl('Logged in','e'); ?></span>:&nbsp;<span class="title_bar_top"><?php echo $res{"fname"}.' '.$res{"lname"};?></span>
            <span style="font-size:0.7em;"> (<?php echo $_SESSION['authGroup']?>)</span>
        </div>
        <div style='float:right;margin-left:5px'>
            <a href="../logout.php?auth=logout" target="_top" class="css_button_small" id="logout_link" onclick="top.restoreSession()" >
                <span><?php xl('Logout', 'e'); ?></span></a>
        </div>
    </div>
</td>


</tr>
</table>

</body>
</html>
