<!--
Form Created with formscript.pl (Supplied with the OpenEMR package).
Javascript code added for further calculations.
Author: Mazen Fakih MD. MSc.
e-mail: fakih.mazen@gmail.com
Creation Date: 2017-09-06
-->
<!-- Headers and Include -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Obstetrics_Form");
$returnurl = 'encounter_top.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <meta name="generator" content="Bluefish 2.2.9" >
  <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
  <title></title>
  <style type="text/css">
  img.c1 {cursor:pointer}
  </style>
</head>
<body>
  <style type="text/css">
  @import url(../../../library/dynarch_calendar.css);
  </style>
  <script type="text/javascript" src=
  "../../../library/dialog.js?v=%3C?php%20echo%20$v_js_includes;%20?%3E">
  </script> 
  <script type="text/javascript" src="../../../library/textformat.js">
  </script> 
  <script type="text/javascript" src=
  "../../../library/dynarch_calendar.js">
  </script>
  <?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
  
  <script type="text/javascript" src=
  "../../../library/dynarch_calendar_setup.js">
  </script> 
  <script language='JavaScript'>
  var mypcc = '1'; 
  </script> <a href=
  '%3C?php%20echo%20$GLOBALS['>/interface/patient_file/encounter/<?php echo $returnurl?>'
  onclick='top.restoreSession()'&gt;[do not save]</a>
  <form method="post" action=
  "%3C?php%20echo%20$rootdir;?%3E/forms/Obstetrics_Form/save.php?mode=new"
  name="Obstetrics_Form" onsubmit="return top.restoreSession()" id=
  "Obstetrics_Form">
    <!-- Headers and Include ==Ends -->
    <hr>
    <h1>Obstetrics_Form</h1>
    <hr>
    <input type="submit" name="submit form" value="submit form"><br>
    <br>
    <h3>LMP</h3><span class=
    'text'><?php xl('Lmp:  (yyyy-mm-dd): ','e') ?></span> <input type=
    'text' size='10' name='lmp' id='lmp' onchange='validatelmp()' onkeyup=
    'datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title=
    'yyyy-mm-dd last date of this event'> <img src=
    '../../../interface/pic/show_calendar.gif' align='absbottom' width='24'
    height='22' id='img_lmp' border='0' alt='[?]' class='c1' title=
    'Click here to choose a date' name="img_lmp"> 
    <script>
    Calendar.setup({inputField:'lmp', ifFormat:'%Y-%m-%d', button:'img_lmp'});
    </script> <span class=
    'text'><?php xl('Edc:  (yyyy-mm-dd): ','e') ?></span> <input type=
    'text' size='10' name='edc' id='edc' onchange='validateedc()' onkeyup=
    'datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title=
    'yyyy-mm-dd last date of this event'> <img src=
    '../../../interface/pic/show_calendar.gif' align='absbottom' width='24'
    height='22' id='img_edc' border='0' alt='[?]' class='c1' title=
    'Click here to choose a date' name="img_edc"> 
    <script>
    Calendar.setup({inputField:'edc', ifFormat:'%Y-%m-%d', button:'img_edc'});
    </script><br>
    <h3>Gestational Age</h3>Weeks: <input type="text" name="weeks" id=
    "weeks" onchange='validateedc()' size="3"> Days: <input type="text"
    name="days" id="days" onchange='validateedc()' size="3"><br>
    <input type="button" value="Clear" onclick="clear_all();"><br>
    <h3>Patient History</h3>Supplements:<br>
    <textarea name="supplements" rows="4" cols="40"></textarea><br>
    Treatments:<br>
    <textarea name="treatments" rows="4" cols="40"></textarea><br>
    <h3>Physical Exam</h3>Height of fundus: <input type="text" name=
    "height_of_fundus" size="5"><br>
    <h3>Lab Results</h3><br>
    <h4>CBC</h4>Hemoglobin: <input type="text" name="hemoglobin" size=
    "5"><br>
    Platelets: <input type="text" name="platelets" size="5"><br>
    <h4>Urinalysis</h4>Urinalysis:<br>
    <textarea name="urinalysis" rows="4" cols="40"></textarea><br>
    Urine culture:<br>
    <textarea name="urine_culture" rows="4" cols="40"></textarea><br>
    <h4>Blood Group</h4>Abo and rh: <input type="text" name="abo_and_rh"
    size="3"><br>
    Coombs test: <input type="text" name="coombs_test"><br>
    <h4>Serology</h4>Syphillis RPR: <input type="text" name=
    "syphillis_rpr"><br>
    Hepatitis B: <input type="text" name="hepatitis_b"><br>
    HIV: <input type="text" name="hiv"><br>
    <h4>Cultures</h4>Group B streptococcus culture: <input type="text"
    name="group_b_streptococcus_culture"><br>
    <h4>Other tests</h4>Glucose challenge test: <input type="text" name=
    "glucose_challenge_test"><br>
    Others:<br>
    <textarea name="others" rows="4" cols="40"></textarea><br>
    <h3>Ultrasound</h3>Fetal heart:<br>
    <label><input type="radio" name="fetal_heart" value=
    "Documented">Documented</label><br>
    <label><input type="radio" name="fetal_heart" value="Not Seen">Not
    Seen</label><br>
    Ultrasound notes:<br>
    <textarea name="ultrasound_notes" rows="4" cols="100"></textarea><br>
    <h3>Current Problems</h3>Problem list:<br>
    <textarea name="problem_list" rows="4" cols="100"></textarea><br>
    <h3>Notes</h3>Other notes:<br>
    <textarea name="other_notes" rows="4" cols="100"></textarea>
    <input type="submit" name="submit form" value="submit form">
  </form><a href=
  '%3C?php%20echo%20$GLOBALS['>/interface/patient_file/encounter/<?php echo $returnurl?>'
  onclick='top.restoreSession()'&gt;[do not save]</a> <?php
  formFooter();
  ?> 
  <script language='JavaScript'>

  function clear_all()
  {
        // Get fields
        var lmp = document.getElementById('lmp');
        var edc = document.getElementById('edc');
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
        
        //Clear fields
        lmp.value = ''
        edc.value = ''
        weeks.value = ''
        days.value = ''
  }

  function validatelmp()
  {
        // Get fields
        var lmp = document.getElementById('lmp');
        var edc = document.getElementById('edc');
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
        //if not empty, calculate
        if (lmp.value != '')
        {
                calculatefromlmp();
        }
        if (weeks.value != '' && days.value != '')
        {
                calculatefromweeks();
        }

  }


  function validateedc()
  {
        // Get fields
        var lmp = document.getElementById('lmp');
        var edc = document.getElementById('edc');
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
        //if not empty, calculate
        if (edc.value != '')
        {
                calculatefromedc();
        } 
        if (weeks.value != '' && days.value != '')
        {
                calculatefromweeks();
        }

  }


  function calculatefromlmp() 
  {

  // Today as a date
        var today = new Date();

  // Get edc and lmp fields
        var edc = document.getElementById('edc');
        var lmp = document.getElementById('lmp');
  //remove the '-' from the lmp and divide into y/m/d
        var lmp_elements = lmp.value.split('-');
        var year = parseInt(lmp_elements[0]);
        var month = parseInt(lmp_elements[1]);
        var day = parseInt(lmp_elements[2]);
  //create a date item from the above
        var lmp_dated = new Date(year,month-1,day);

  //Naegele's Rule and split results into y/m/d   
        lmp_dated.setDate(lmp_dated.getDate() + 280);
        var edc_day = lmp_dated.getDate();
        var edc_month = lmp_dated.getMonth()+1;
        var edc_year = lmp_dated.getFullYear();
  //Add the '0' for the month and day and form the final string
        if (edc_month < 10) {edc_month_str = '0' + edc_month;}
        else {edc_month_str = '' + edc_month;}
        if (edc_day < 10) {edc_day_str = '0' + edc_day;}
        else {edc_day_str = '' + edc_day;}
        var edc_str = edc_year + '-' + edc_month_str + '-' + edc_day_str
  //put the edc in the field
        edc.value = edc_str;

  // Get weeks and days fields
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
  //calculate difference between today and lmp
        var curr_week = 280 + Math.floor((Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()) - Date.UTC(lmp_dated.getFullYear(), lmp_dated.getMonth(), lmp_dated.getDate()) ) /(1000 * 60 * 60 * 24));
  //calculate days and weeks
        var gest_days = curr_week % 7;
        var gest_weeks = Math.floor(curr_week / 7);
        weeks.value = gest_weeks;
        days.value = gest_days;
  }

  function calculatefromedc() 
  {

  // Today as a date
        var today = new Date();

  // Get edc and lmp fields
        var edc = document.getElementById('edc');
        var lmp = document.getElementById('lmp');
  //remove the '-' from the edc and divide into y/m/d
        var edc_elements = edc.value.split('-');
        var year = parseInt(edc_elements[0]);
        var month = parseInt(edc_elements[1]);
        var day = parseInt(edc_elements[2]);
  //create a date item from the above
        var edc_dated = new Date(year,month-1,day);

  //Naegele's Rule and split results into y/m/d   
        edc_dated.setDate(edc_dated.getDate() - 280);
        var lmp_day = edc_dated.getDate();
        var lmp_month = edc_dated.getMonth()+1;
        var lmp_year = edc_dated.getFullYear();
  //Add the '0' for the month and day and form the final string
        if (lmp_month < 10) {lmp_month_str = '0' + lmp_month;}
        else {lmp_month_str = '' + lmp_month;}
        if (lmp_day < 10) {lmp_day_str = '0' + lmp_day;}
        else {lmp_day_str = '' + lmp_day;}
        var lmp_str = lmp_year + '-' + lmp_month_str + '-' + lmp_day_str
  //put the lmp in the field
        lmp.value = lmp_str;

  // Get weeks and days fields
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
  //calculate difference between today and lmp
        var lmp_dated = new Date(lmp_year,lmp_month-1,lmp_day);
        var curr_week = 280 + Math.floor((Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()) - Date.UTC(lmp_dated.getFullYear(), lmp_dated.getMonth(), lmp_dated.getDate()) ) /(1000 * 60 * 60 * 24));
  //calculate days and weeks
        var gest_days = curr_week % 7;
        var gest_weeks = Math.floor(curr_week / 7)-40;
        weeks.value = gest_weeks;
        days.value = gest_days;
  }

  function calculatefromweeks()
  {
        // LMP and EDC dates set to today
        var lmp_date = new Date();
        var edc_date = new Date();
        // Get edc and lmp fields
        var edc = document.getElementById('edc');
        var lmp = document.getElementById('lmp');
        // Get weeks and days fields
        var weeks = document.getElementById('weeks');
        var days = document.getElementById('days');
        //Total days from the weeks and days
        var total_days = parseInt(days.value) + (parseInt(weeks.value) * 7);
        //Calculate LMP and EDC
        lmp_date.setDate(lmp_date.getDate() - total_days);

        edc_date.setDate(edc_date.getDate() - total_days + 280);

        //Get LMP and EDC elements
        var lmp_day = lmp_date.getDate();
        var lmp_month = lmp_date.getMonth()+1;
        var lmp_year = lmp_date.getFullYear();

        var edc_day = edc_date.getDate();
        var edc_month = edc_date.getMonth()+1;
        var edc_year = edc_date.getFullYear();

        //Add the '0' for the month and day and form the final LMP string
        if (lmp_month < 10) {lmp_month_str = '0' + lmp_month;}
        else {lmp_month_str = '' + lmp_month;}
        if (lmp_day < 10) {lmp_day_str = '0' + lmp_day;}
        else {lmp_day_str = '' + lmp_day;}
        var lmp_str = lmp_year + '-' + lmp_month_str + '-' + lmp_day_str

        //Add the '0' for the month and day and form the final EDC string
        if (edc_month < 10) {edc_month_str = '0' + edc_month;}
        else {edc_month_str = '' + edc_month;}
        if (edc_day < 10) {edc_day_str = '0' + edc_day;}
        else {edc_day_str = '' + edc_day;}
        var edc_str = edc_year + '-' + edc_month_str + '-' + edc_day_str

        //put the edc in the field
        edc.value = edc_str;
        //put the lmp in the field
        lmp.value = lmp_str;
  }
  </script>
</body>
</html>
