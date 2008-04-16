<?php
include_once("../../../interface/globals.php");
$today = dateformat();
$time = date('H:i');
$providerid = $_SESSION['authId'];

// check if the current (open) DBC is already sent to insurer
// if no, just edit the DBC
if ( $_SESSION['show_axid'] && $_SESSION['newdbc'] ) {
  if ( !sent_to_insurer() ) {
    load_dbc(); $_SESSION['newdbc'] = FALSE;
  } else {
    // already sent to insurer
    // ? - ce se pune aici?
  }
}

//find provider name, just to display
$q = sprintf('SELECT fname, mname, lname FROM users WHERE id = %d', $providerid);
$r = mysql_query($q) or die(mysql_error());
$pro = mysql_fetch_array($r);
$provider = $pro['fname'] .' '. $pro['mname'] .' '. $pro['lname'];
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="../../../library/js/jquery.js"></script>

<script language="JavaScript">
<!-- hide from JavaScript-challenged browsers
function addiagnose(dia) {
  if ( dia == 1 ) popupWin = window.open('dbc_diagnose_as1.php', 'asremote', 'width=700,height=200'); 
  else if ( dia == 2) popupWin = window.open('dbc_diagnose_as2.php', 'asremote', 'width=700,height=200'); 
  else if ( dia == 3) popupWin = window.open('dbc_diagnose_as3.php', 'asremote', 'width=700,height=200'); 
  else if ( dia == 4) popupWin = window.open('dbc_diagnose_as4.php', 'asremote', 'width=700,height=200'); 
  else if ( dia == 5) popupWin = window.open('dbc_diagnose_as5.php', 'asremote', 'width=700,height=200'); 
  popupWin.moveTo(200,200);
}

// done hiding --></script>

<script type="text/javascript">
$(document).ready(function(){
  $('#resetas1').bind('click', function(){
    $.ajax({
      type: 'POST',
      url: 'as.php',
      data: 'reset=as1',
      async: false
    });
    window.location.reload(true);
 });
 $('#resetas2').bind('click', function(){
    $.ajax({
      type: 'POST',
      url: 'as.php',
      data: 'reset=as2',
      async: false
    });
    window.location.reload(true);
 });
 $('#resetas3').bind('click', function(){
    $.ajax({
      type: 'POST',
      url: 'as.php',
      data: 'reset=as3',
      async: false
    });
    window.location.reload(true);
 });
 $('#resetas4').bind('click', function(){
    $.ajax({
      type: 'POST',
      url: 'as.php',
      data: 'reset=as4',
      async: false
    });
    window.location.reload(true);
 });
 $('#resetas5').bind('click', function(){
    $.ajax({
      type: 'POST',
      url: 'as.php',
      data: 'reset=as5',
      async: false
    });
    window.location.reload(true);
 });
 $('#savediagnose').bind('click', function(){
    var err = $.ajax({ 
      type: 'POST',
      url: 'as.php',
      data: 'check=1',
      async: false
    }).responseText; 
    
    //close window at saving
    if ( err == 'closewindow' ) { 
         self.close();
    } else {
         $('#error').html(err);
         $('#error').fadeOut(10000);
    }
 });
 
  $('#setas').bind('click', function(){
    var errs = $.ajax({ 
      type: 'POST',
      url: 'as.php',
      data: 'posas=' + $("input[@name='mainas']:checked").val(),
      async: false
    }).responseText;

    if ( errs ) alert(errs);
 });

  $('#circuit').bind('change', function(){
    $.ajax({ 
      type: 'POST',
      url: 'as.php',
      data: 'circuit=' + $("#circuit").val(),
      async: false
    }); 
 });

});
</script>

<script type="text/javascript">
function win() {
  //window.opener.location.reload(true);
}
</script>

</head>

<?php //var_dump($_SESSION); ?>
<body bgcolor="#A4FF8B" onunload="win();">
  Date: <?=$today?> <br />
  Time: <?=$time?> <br />
  Provider: <?=$provider?> <br /><br />
 
    <?php
    // -----------------------------------------------
    // CIRCUIT CODE DROPDOWN
    if ( !isset($_SESSION['show_axid']) ) $_SESSION['circuitcode'] = 1; // for new dbc's

    // either we have an old DBC / new one
    if ( isset($_SESSION['show_axid']) ) {
        $sel = ( $_SESSION['circuitcode'] ) ? $_SESSION['circuitcode'] : has_circuit($_SESSION['show_axid']);
    }

    echo circuit_dropdown($sel);
    // -----------------------------------------------
    ?>

<div id="error"></div>

<h4>AS I</h4>
<?php
// list all selected diagnoses for AS 1 
$counter = 1;

if ( isset($_SESSION['as1']) && $_SESSION['as1']) {
  foreach ( $_SESSION['as1'] as $a) {
    $checked = ( $_SESSION['posas'] == $counter ) ? 'checked' : '';
    echo $counter .'. '. what_as($a) .
      '&nbsp;&nbsp;&nbsp;(<input type="radio" name="mainas" id="mainas" value=' .$counter. ' ' .$checked. '/>Hoofddiagnose)<br />';
    $counter++;
  }
} else {
  echo 'Geen diagnose.<br/>' ;
}
?>

<?php //diagnose limit - also, checked in dbc_diagnose_asX files
if ( count($_SESSION['as1']) < 3 ) { ?>
  <a href="javascript:addiagnose(1)" class="text">Click to add a diagnose</a>&nbsp; &nbsp;
<?php } else { ?>
  Click to add a diagnose
<?php } ?>

<a href="#" id="resetas1" >Reset AS1 Diagnoses</a> 



<h4>AS II</h4>
<?php
// list all selected diagnoses for AS 2
if ( isset($_SESSION['as2']) && $_SESSION['as2'] ) {
  foreach ( $_SESSION['as2'] as $a) {
    $check = ( $a['trekken'] == 'on' ) ? 'Trekken van' : '-';
    $checked = ( $_SESSION['posas'] == $counter ) ? 'checked' : '';
    echo $counter .'. '. what_as($a['code']) . ' (' .$check. ').'.
      '&nbsp;&nbsp;&nbsp;(<input type="radio" name="mainas" id="mainas" value=' .$counter. ' ' .$checked. '/>Hoofddiagnose)<br />';
    $counter++;
  }
} else {
  echo 'Geen diagnose.<br/>' ;
}
?>

<?php //diagnose limit - also, checked in dbc_diagnose_asX files
if ( count($_SESSION['as2']) < 3 ) { ?>
  <a href="javascript:addiagnose(2)" class="text">Click to add a diagnose</a>&nbsp; &nbsp;
<?php } else { ?>
  Click to add a diagnose&nbsp; &nbsp;
<?php } ?>
<a href="#" id="resetas2" >Reset AS2 Diagnoses</a> <br />
<a href="#" id="setas" >Set Main Diagnose</a> 





<h4>AS III</h4>
<?php
// list all selected diagnoses for AS 3
if ( isset($_SESSION['as3']) && $_SESSION['as3'] ) {
   echo what_as($_SESSION['as3']) . '<br />';
} else {
  echo 'Geen diagnose.<br/>' ;
}
?>
<a href="javascript:addiagnose(3)" class="text">Click to add a diagnose</a>&nbsp; &nbsp;
<a href="#" id="resetas3" >Reset AS3 Diagnoses</a> 



<h4>AS IV</h4>
<?php
// list all selected diagnoses for AS 4
if ( isset($_SESSION['as4']) && $_SESSION['as4'] ) {
   echo what_as($_SESSION['as4']) . '<br />';
} else {
  echo 'Geen diagnose.<br/>' ;
}
?>
<a href="javascript:addiagnose(4)" class="text">Click to add a diagnose</a>&nbsp; &nbsp;
<a href="#" id="resetas4" >Reset AS4 Diagnoses</a> 



<h4>AS V</h4>
<?php
// list all selected diagnoses for AS 5
if ( isset($_SESSION['as5']) && $_SESSION['as5'] ) {
   echo 'Begin GAF: ' . what_as($_SESSION['as5']['gaf1']) . '<br />';
} else {
  echo 'Geen diagnose.<br/>' ;
}
?>
<a href="javascript:addiagnose(5)" class="text">Click to add a diagnose</a>&nbsp; &nbsp;
<a href="#" id="resetas5" >Reset AS5 Diagnoses</a> 

<br /><br />
<input type="button" value="Diagnose opslaan" name="savediagnose" id="savediagnose"/>

<body>
</html>
