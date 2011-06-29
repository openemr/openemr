<?php
include_once("../globals.php");
?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
      .hidden {
        display:none;
      }
      .visible{
        display:block;
      }
</style>
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
function showhideMenu() {
	var m = parent.document.getElementById("fsbody");
    var targetWidth = '0,*';
    if (m.cols == targetWidth) {
      m.cols = '<?php echo $GLOBALS['gbl_nav_area_width'] ?>,*';
      document.getElementById("showMenuLink").style.display = 'inline';
      document.getElementById("hideMenuLink").style.display = 'none';
    }
    else {
      m.cols = targetWidth;
      document.getElementById("showMenuLink").style.display = 'none';
      document.getElementById("hideMenuLink").style.display = 'inline';
    }
}
</script>
</head>
<body class="body_title">

<?php
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table style="border-width:1em;" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="top" align="left" class = 'text'>
<?php if ($GLOBALS['concurrent_layout']) { ?>
<table cellspacing="0" cellpadding="0"  width="100%" height="100%"><tr><td valign="top" align="center" >
<a href='' class="css_button_small" id='new0' style="font-weight:bold;margin: 0px 0px 0px 0px;" onClick=" return top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php')">
	<span><?php echo htmlspecialchars( xl('NEW PATIENT'), ENT_QUOTES) ?></span></a>
</td></tr>
<tr><td valign="middle" align="left" ><B>
      <a id='showMenuLink' class='text' onclick='javascript:showhideMenu();'><?php xl('Hide Menu','e'); ?></a>
      <a id='hideMenuLink' class='text' style='display:none;' onclick='javascript:showhideMenu();'><?php xl('Show Menu','e'); ?></a>
</B></td></tr>
</table>
<?php } else { ?>
&nbsp;
<?php } ?>
</td>
<td valign="middle">
        <div style='margin-left:10px; float:left; display:none' id="current_patient_block">
            <span class='text'><?php xl('Patient','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_patient"><b><?php xl('None','e'); ?></b></span>
        </div>
</td>
<td valign="middle">
        <div style='margin-left:5px; float:left; display:none' id="past_encounter_block">
		<span class='title_bar_top' id="past_encounter"><b><?php echo htmlspecialchars( xl('None'), ENT_QUOTES) ?></b></span>
        </div><BR>
        <div style='margin-left:10px; float:left; display:none' class='text' id="current_encounter_block" >
            <span class='text'><B><?php xl('Selected Encounter','e'); ?></B>:&nbsp;</span><span class='title_bar_top' id="current_encounter"><b><?php xl('None','e'); ?></b></span>
        </div>
</td>

<td valign="top" align="right" class = 'text'>
    <div style='float:right; margin-left:5px'>
        <div style='float:right; margin-top:3px' class = 'text'>
            <a href="javascript:;" onclick="javascript:parent.left_nav.goHome();" ><?php xl('Home','e'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="../../Documentation/User_Guide/index.html" target="_blank" id="help_link" onclick="top.restoreSession()"> <?php xl('Manual','e'); ?></a>
            <div style='float:right;margin-left:15px'>
	            <a href="../logout.php?auth=logout" target="_top" class="css_button_small" id="logout_link" onclick="top.restoreSession()" >
                <span><?php xl('Logout', 'e'); ?></span></a>
        	</div>
    	</div>
        <br>
            <a class="title_bar_top"><?php echo $res{"fname"}.' '.$res{"lname"};?></a>
            <a style="font-size:0.7em;"> (<?php echo $_SESSION['authGroup']?>)</a>
    </div>
</td>


</tr>
</table>

</body>
</html>
