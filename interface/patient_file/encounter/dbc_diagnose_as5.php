<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="../../../library/js/jquery.js"></script>

<script type="text/javascript">
function win() {
  window.opener.location.reload(true);
    self.close();
}
</script>
                                                                                
<?php
include_once("../../../interface/globals.php");

if ( isset($_POST['saveas']) ) { 
  $as1 = $_POST['box1']; $as2 = $_POST['box2']; $as3 = $_POST['box3'];
  //$_SESSION['as5'] = array('gaf1' => $as1, 'gaf2' => $as2, 'gaf3' => $as3);
  $_SESSION['as5'] = array('gaf1' => $as1, 'gaf2' => 0, 'gaf3' => 0);
}
?>

</head>

<body onunload="win();" bgcolor="#A4FF8B">
  <p>Choose your AS V diagnose</p>

 <form method="post" target="_self">
<table border=0 cellspacing=0 cellpadding=0 >
<tr>
  <td width='1%' nowrap>Begin GAF
  <select name="box1" id="box1">
  <?php
  $rlvone = records_level1('as5', 1);
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
</td></tr>

<!--
<tr>
<td width='1%' nowrap>Hoogste GAF
 <select name="box2" id="box2">
  <?php
  $rlvone = records_level1('as5', 2);
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
</td></tr>       

<tr>
<td width='1%' nowrap>Eind GAF
 <select name="box3" id="box3">
  <?php
  $rlvone = records_level1('as5', 3);
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
</td></tr>
-->       
</table>
<input type="submit" value="Choose" name="saveas"/>
<input type="button" value="Close" onclick="window.close();" />
</form>
                                                          
  
</body>
</html>
