<?php
include_once("../globals.php");
?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_title">

<?php
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<td>
    <div style='float:left;margin-top:2px'>
        <div style='margin-left:10px; float:left; display:none' id="current_patient_block">
            <span class='text'><?php xl('Patient','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_patient"><b><?php xl('None','e'); ?></b></span>
        </div>
        <div style='margin-left:5px; float:left; display:none' class='text' id="current_encounter_block" >
            <span class='text'>| <?php xl('Encounter','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_encounter"><b><?php xl('None','e'); ?></b></span>
        </div>
    </div>
</td>

<td valign="middle">
    <div style='float:right; margin-left:5px'>

        <div style='float:left; margin-top:3px' class = 'text'>
            <a href="javascript:;" onclick="javascript:parent.left_nav.goHome();" ><?php xl('Home','e'); ?></a>
            &nbsp;|&nbsp;
            <a href="../../Documentation/User_Guide/" target="RTop" id="help_link" onclick="top.restoreSession()"> <?php xl('Manual','e'); ?></a>
            &nbsp;|&nbsp;
        </div>

        <div style='float:left'>
        <span class='text'><?php xl('Logged in','e'); ?></span>:&nbsp;<span class="title_bar_top"><?php echo $res{"fname"}.' '.$res{"lname"};?></span>
        <span style="font-size:0.7em;"> (<?php echo $_SESSION['authGroup']?>)</span>
        </div>
        <div style='float:left;margin-left:5px'><a href="../logout.php?auth=logout" target="_top" class="css_button_small" id="logout_link"><span><?php xl('Logout', 'e'); ?></span></a></div>
    </div>
</td>


</tr>
</table>

</body>
</html>
