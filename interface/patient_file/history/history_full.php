<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

$CPR = 4; // cells per row

// Check authorization.
$thisauth = acl_check('patients', 'med');
if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}
if ($thisauth != 'write' && $thisauth != 'addonly')
  die(htmlspecialchars(xl("Not authorized"),ENT_NOQUOTES));
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header ?>" type="text/css">

<style>
.control_label {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}
</style>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>

<script LANGUAGE="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

function validate(f) {
<?php generate_layout_validation('HIS'); ?>
 return true;
}

function submitme() {
 var f = document.forms[0];
 if (validate(f)) {
  top.restoreSession();
  f.submit();
 }
}

function submit_history() {
    document.forms[0].submit();
}

//function for selecting the smoking status in radio button based on the selection of drop down list.
function radioChange(rbutton)
{
     if (rbutton == 1 || rbutton == 2)
     {
     document.getElementById('radio_tobacco[current]').checked = true;
     }
     else if (rbutton == 3)
     {
     document.getElementById('radio_tobacco[quit]').checked = true;
     }
     else if (rbutton == 4)
     {
     document.getElementById('radio_tobacco[never]').checked = true;
     }
     else if (rbutton == 5 || rbutton == 9)
     {
     document.getElementById('radio_tobacco[not_applicable]').checked = true;
     }
     else if (rbutton == '')
     {
     var radList = document.getElementsByName('radio_tobacco');
     for (var i = 0; i < radList.length; i++) {
     if(radList[i].checked) radList[i].checked = false;
     }
     }
}

//function for selecting the smoking status in drop down list based on the selection in radio button.
function smoking_statusClicked(cb) 
{    
     if (cb.value == 'currenttobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 1;
     }
     else if (cb.value == 'nevertobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 4;
     }
     else if (cb.value == 'quittobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 3;
     }
     else if (cb.value == 'not_applicabletobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 6;
     }
}
</script>

<script type="text/javascript">
/// todo, move this to a common library
$(document).ready(function(){
    tabbify();
});
</script>

<style type="text/css">
div.tab {
	height: auto;
	width: auto;
}
</style>

</head>
<body class="body_top">

<?php
$result = getHistoryData($pid);
if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);
}

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 " .
  "ORDER BY group_name, seq");
?>

<form action="history_save.php" name='history_form' method='post' onsubmit='return validate(this)'>
    <input type='hidden' name='mode' value='save'>

    <div>
        <span class="title"><?php echo htmlspecialchars(xl('Patient History / Lifestyle'),ENT_NOQUOTES); ?></span>
    </div>
    <div style='float:left;margin-right:10px'>
  <?php echo htmlspecialchars(xl('for'),ENT_NOQUOTES);?>&nbsp;<span class="title"><a href="../summary/demographics.php"><?php echo htmlspecialchars(getPatientName($pid),ENT_NOQUOTES); ?></a></span>
    </div>
    <div>
        <a href="javascript:submit_history();" class='css_button'>
            <span><?php echo htmlspecialchars(xl('Save'),ENT_NOQUOTES); ?></span>
        </a>
        <a href="../summary/demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onclick="top.restoreSession()">
            <span><?php echo htmlspecialchars(xl('Cancel'),ENT_NOQUOTES); ?></span>
        </a>
    </div>

    <br/>

    <!-- history tabs -->
    <div id="HIS" style='float:none; margin-top: 10px; margin-right:20px'>
        <ul class="tabNav" >
           <?php display_layout_tabs('HIS', $result, $result2); ?>
        </ul>

        <div class="tabContainer">
            <?php display_layout_tabs_data_editable('HIS', $result, $result2); ?>
        </div>
    </div>
</form>

<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot']."/library/options_listadd.inc"; ?>

</body>

<script language="JavaScript">
<?php echo $date_init; // setup for popup calendars ?>
</script>

</html>
