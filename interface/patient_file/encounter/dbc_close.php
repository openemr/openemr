<?php
include_once("../../../interface/globals.php");

$todayu = date('Y-m-d');

// if we don't have a DBC, obtain the last opened DBC
if ( !$_SESSION['show_axid'] ) {
 $last = lists_diagnoses('last');
 $_SESSION['show_axid'] = $last[0]['ax_id'];
 $_SESSION['dbc_odate'] = $last[0]['ax_odate'];
}

// take dbc content to show some infos
$dbc = content_diagnose($_SESSION['show_axid']);

// ================================================================
// SAVING PART (WITH VALIDATIONS)

if ( isset($_POST['closedbc']) && ($_SESSION['show_axid']) ) {
    $gaf = array('gaf2' => $_POST['box2'], 'gaf3' => $_POST['box3']);

    // validate required fields
    if ( empty($_SESSION['eind']) ) {
        $err_str = 'Your have no date selected.\n';
    } else {
        $chdate = strtotime($_SESSION['eind']);
        $todate = strtotime(date('Y-m-d'));
        if ( $chdate > $todate) $err_str = 'Your date '.$_SESSION['eind'].' is in the future! Please fix.\n';

        $rezdate = vl_eind_event($_SESSION['eind'], $_SESSION['show_axid'], $_SESSION['pid']);
        if ( !$rezdate['bool'] ) $err_str = 'Your closing date is older than last activity date!\n';
    } 

    // only for patients with age >= 4
    if ( has_beginGAF($_SESSION['show_axid']) ) {
        if ( empty($_POST['box2']) )    $err_str .= 'You must choose Hoogste GAF. \n';
        if ( empty($_POST['box3']) )    $err_str .= 'You must choose Eind GAF.\n';
    }

    if ( empty($_POST['rtc']) )     $err_str .= 'You must have a redensluiten code.\n';
    if ( empty($_POST['ztc']) )     $err_str .= 'You must have a zorgtype code.\n';
    else if ( ($_POST['ztc'] == '180115') || ($_POST['ztc'] == '180210') ) {
        // 180115 for code 115; 180210 for code 210 in cl_zorg; Ondertoezichtstelling
        $err_str .= ( vl_ztc_age($ztc, $dbc['ax_odate'], $_SESSION['pid']) ) ? '' : 'The patient was not < 18 yr at the date of DBC opening.\n';
    }

    if ( ($_POST['ztc'] == '180102') && !vl_zorgtype_102($_SESSION['show_axid'], $_SESSION['eind']) ) {
        $err_str .= 'You are not allowed to select this zorgtype (code 180102).';
    }

    // if by mistake we don't have a value for stoornis, then generate one
    if ( !isset($_SESSION['pgroep']) || empty($_SESSION['pgroep']) ) { 
        $s = dt_main(); $stoornis = $_SESSION['pgroep']; 
    } 

    // should we have a followup or no?
    $janee = ( isset($_POST['janee']) ) ? $_POST['janee'] : 1;

    if ( $err_str ) {
        echo "<script>alert('$err_str')</script>";
    } else {
        close_dbc($janee, $_SESSION['pgroep'], $_POST['ztc'], $_POST['rtc'], $gaf);
    }
}
// End SAVING PART
// ================================================================
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="../../../library/js/jquery.js"></script>
<script type="text/javascript" src="../../../library/js/jquery-calendar.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/jquery-calendar.css">

<script type="text/javascript">
$(document).ready(function(){
    popUpCal.regional['nl'] = {
        clearText: 'sterge | ',
        closeText: 'inchide',
        prevText: '&laquo;&nbsp; | ',
        nextText: ' | &nbsp;&raquo;',
        currentText: 'Azi',

        firstDay: 1,
        dayNames: [
                'D', 'L', 'Ma', 'Mi', 'J', 'V', 'S'
        ],
        monthNames: [
                'Januarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Junie',
                'Julie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'
        ],

        dateFormat: 'YMD-'
    };

    //popUpCal.setDefaults(popUpCal.regional['nl']);
    $('#einddatum').calendar({autoPopUp: 'button', buttonImageOnly: false,
	buttonImage: '', buttonText: '...', yearRange: '-1:+1', dateFormat: 'YMD-'});

    $('#follow').hide();	
    $('#einddatum').bind('change', function(){
        $.ajax({
            type: 'POST',
            url: 'as.php',
            data: 'seteinddatum=' + $('#einddatum').val(),
            async: false
        });
        $('#rfsum').html(
            $.ajax({
                type: 'POST',
                url: 'as.php',
                data: 'decision=1',
                async: false
            }).responseText
        );	
        $('#rtcplace').html(
          $.ajax({
            type: 'POST',
            url: 'as.php',
            data: 'rtc=1',
            async: false
          }).responseText
        );
        $('#dbclen').html(
            $.ajax({
                type: 'POST',
                url: 'as.php',
                data: 'len=1',
                async: false
            }).responseText
        );
    });
    
    $('#rtcplace').bind('click', function(){
        $('#rtc').bind('change', function(){
            if ( $('#rtc').val() == 4 ) $('#follow').show();
            else $('#follow').hide();
        }); 
    });

    $('#ztc').bind('change', function(){
        $('#ztctext').html(
          $.ajax({
            type: 'POST',
            url: 'as.php',
            data: 'ztc='+ $('#ztc').val() +'&odate=' + <?php echo str_replace('-', '', $dbc['ax_odate']) ?>,
            async: false
          }).responseText
        );
    });

});
</script>


<script type="text/javascript">
function win() {
  window.opener.location.reload(true);
  self.close();
}
</script>	

</head>

<body bgcolor="#A4FF8B" onunload="win();">

<div id="error"></div>

<form method="POST">

<?php $rdate = vl_eind_event('1', $_SESSION['show_axid'], $_SESSION['pid']); ?>

<p>Close DBC id:<strong><?=$dbc['ax_id']?></strong> opening date: <strong><?=$dbc['ax_odate']?></strong>
Last activity date: <strong><?php echo $rdate['date']?></strong></p>
<div id="dbclen">DBC Length 0 days.</div>

<p><strong>Einddatum(ID1003):</strong><input type="text" size="10" name="einddatum" id="einddatum" value="  "/></p>

<p><strong>Zorgtypecode(ID1004)</strong>:<?php zorgtype_dropdown(); ?></p>
<h5>
For 'Second Opinion' option, the DBC must have direct time &lt;= 250 minutes<br />
For 'Intercollegial consult' all activities must have mag_groep = N and direct time must be &lt; 180
</h5>

<div id="ztctext" style="color: red;"></div>

<!-- GAF PART ======================================== -->

<?php
    // only for patients with age >= 4
    if ( !has_beginGAF($_SESSION['show_axid']) ) $disabled = 'disabled = disabled';
?>

<table>
<tr>
<td width='1%' nowrap>Hoogste GAF</td>
<td><select name="box2" id="box2" <?=$disabled?> >
  <?php
  $rlvone = records_level1('as5', 2);
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
</td></tr>

<tr>
<td width='1%' nowrap>Eind GAF</td>
<td><select name="box3" id="box3" <?=$disabled?> >
  <?php
  $rlvone = records_level1('as5', 3);
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
</td></tr>
</table>

<p><strong>Circuitcode(ID890)</strong>&nbsp;<?=get_circuitcode($_SESSION['show_axid'])?></p>

<!-- redensluiten dropdown -->
<div id="rtcplace"></div>

<div id="follow">
Vervolg DBC openen?
<input type="radio" id="janee" name="janee" value="1"/>Ja &nbsp;
<input type="radio" id="janee" name="janee" checked="checked" value="0"/>Nee   
</div>

<!-- show main diagnose -->
<?php
$un1 = unserialize($dbc['ax_as1']); 
$un2 = unserialize($dbc['ax_as2']);

$nr1 = count($un1['content']);
$nr2 = count($un2['content']);

$mainpos = (int)$un1['mainpos']; // is saved in ax_as1 AND ax_as2 (same value)

// substract 1 for array use (first value is 0 not 1)
if ( $nr1 >= $mainpos) 	$maindiag = $un1['content'][$mainpos-1];
else { $where = $mainpos - $nr1 - 1; $maindiag = $un2['content'][$where]['code']; }; 
?>
<hr />
<p><strong>Main diagnose:</strong></p>
<div style="background-color: #87D172; margin:20px;"><?=what_as($maindiag)?></div>

<?php $tarr = total_time_spent();?>
<p><strong>Total time spent: </strong><?=$tarr['total_time']?> min (Indirect: <?=$tarr['indirect_time']?> min
Travel: <?=$tarr['travel_time']?> min)</p>

<p><?php //verblijf_dropdown(); //later ?></p> 

<div id='rfsum' style="background-color: #87D172; margin:20px;"></div>

<input type="submit" value="Save" name="closedbc"/>
<input type="button" value="Cancel" onclick="javascript:window.close();"/>

</form>

</body>
</html>
