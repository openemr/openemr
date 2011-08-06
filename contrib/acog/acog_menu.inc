<?php

  if (($_SESSION['encounter'] == '') && ($encounter == '')){
    $_SESSION['encounter'] = '0';
  }
  if ( stristr($_SERVER['HTTP_USER_AGENT'], "Mozilla") && (!(stristr($_SERVER['HTTP_USER_AGENT'], "compatible"))) ){
    $height_tag="min-height";
  } else {
    $height_tag="height";
  }
?>
<div id="jtf" style="float: left; position: absolute; top: 10px; left:0;width:230px;margin-left:-214px;z-index:1;"
  onMouseOver="MM_changeProp('jtf','','style.marginLeft','0px','DIV');" onMouseOut="getMouseXY;MM_changeProp('jtf','','style.marginLeft','-214px','DIV');">
  <img src="../../pic/hdrjtoform.png" width="18" height="119" align="right">
  <div style="padding: 2px; border: 2px solid black; border-left:none; border-right:1px solid black; margin-right: 17px; <?php echo $height_tag; ?>: 111px; background-color: white;">
    <h2 style="margin-bottom: 4px;"><img src="../../pic/menu/doc1.png" height="16" width="16" align="left">&nbsp;Form navigator</h2>
    <p><a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=physician_history">PHYSICIAN HISTORY </a> | <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=medical_decision">MEDICAL DECISION</a> | <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=physical_examination">PHYSICAL EXAMINATION </a> | <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=patient_intake_history">PATIENT INTAKE HISTORY </a>| <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=plist">PROBLEM LIST</a> | <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=immunization_record">IMMUNIZATION RECORD </a>| <a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=routine_screening_record">ROUTINE SCREENING RECORD </a>|<a href="<?php echo $rootdir; ?>/patient_file/encounter/load_form.php?formname=high_risk_screening_record"> HIGH-RISK SCREENING RECORD </a>
    <?php echo $addmenu; ?></p></div>
  </div>
  <div id="jtef" style="position: absolute; top: 115px; left:0;width:200px;margin-left:-184px;z-index:2;"
  onMouseOver="MM_changeProp('jtef','','style.marginLeft','0px','DIV');" onMouseOut="MM_changeProp('jtef','','style.marginLeft','-184px','DIV');"  ><img src="../../pic/hdrjtoeform.png" width="18" height="142" align="right">
  <div style="padding: 2px; border: 2px solid black; border-left:none; border-right:1px solid black; margin-right: 17px; <?php echo $height_tag; ?>: 134px; background-color: white;">
    <h2 style="margin-bottom: 4px;"><img src="../../pic/menu/doc1.png" height="16" width="16" align="left">&nbsp;Encounter</h2>
    <small>Encounter for: <?php echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'}; ?></small>
<div style="width:98%; overflow: hidden;">
<?php

if ($result = getFormByEncounter($pid, $encounter, "id,date,form_id,form_name,formdir,user")) {
	foreach ($result as $iter) {		
		$form_info = getFormInfoById($iter['id']);
		$user = $iter['user'];
		$user = getNameFromUsername($user);
		echo "<p><a target=_self href='$rootdir/patient_file/encounter/view_form.php?formname=".$iter{"formdir"}."&id=".$iter{"form_id"}."'>".$iter{"form_name"}."</a> by <small>".$user['fname']." ".$user['lname'] ."</small></p>\n";
	}	
}
?>	
</div></div></div>
<style type="text/css">
<!--
.tabmenu{
  width: 90%;
  height: 16px;
  padding: 1px;
  text-align: top;
}
.tabmenu a{
  font-size: 10px ! important;
  text-transform: uppercase;
  text-decoration: none;
}
.tabmenu:hover{
 background-color: #ccccff;
}
.tabmenu a:hover{
  text-decoration: none; 
}
-->
</style>
<div id="atab" style="float: left; position: absolute; top: 242px; left:0;width:230px;margin-left:-214px;z-index:3;"
  onMouseOver="MM_changeProp('atab','','style.marginLeft','0px','DIV');" onMouseOut="getMouseXY;MM_changeProp('atab','','style.marginLeft','-214px','DIV');">
  <img src="../../pic/hdractions.png" width="18" height="89" align="right">
  <div style="padding: 2px; border: 2px solid black; border-left:none; border-right:1px solid black; margin-right: 17px; <?php echo $height_tag; ?>: 111px; background-color: white;">
    <h2 style="margin-bottom: 4px;"><img src="../../pic/menu/doc1.png" height="16" width="16" align="left">&nbsp;Form actions</h2>
    <div class="tabmenu"><img src="../../pic/menu/save.png" height="16" width="16" align="left">&nbsp;<a href="javascript:document.my_form.submit();">Save</a></div>
    <div class="tabmenu"><img src="../../pic/menu/trash.png" height="16" width="16" align="left">&nbsp;<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>">Cancel</a></div>
    <?php if ($id != '') {?>
    <hr width="90%" align="left">
    <div class="tabmenu"><img src="../../pic/menu/print.png" height="16" width="16" align="left">&nbsp;<a href="<?php echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<?php echo $id; ?>&formname=<?php echo $formname; ?>" target="_blank">Printable form</a></div>
    <div class="tabmenu"><img src="../../pic/menu/mail.png" height="16" width="16" align="left">&nbsp;<a href="<?php echo $rootdir; ?>/patient_file/encounter/send_form.php?id=<?php echo $id; ?>&formname=<?php echo $formname; ?>">Email form</a></div>
    <div class="tabmenu"><img src="../../pic/menu/pdf.png" height="16" width="16" align="left">&nbsp;<a href="<?php echo $rootdir; ?>/patient_file/encounter/pdf_form.php?id=<?php echo $id; ?>&formname=<?php echo $formname; ?>" target="_blank">Open as PDF</a></div>
    <?php } else { ?>
    <p><br>
    Save filled form to make additional actions (printable, email, pdf) visible.
    </p>
    <?php } ?>
  </div>
</div>