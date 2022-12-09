<?php
if(!isset($pap_data)) $pap_data = array();
if(!isset($dt['pt_date'])) $dt['pt_date'] = '';
if(!isset($dt['pt_lab'])) $dt['pt_lab'] = '';
if(!isset($dt['pt_test'])) $dt['pt_test'] = '';
if(!isset($dt['pt_result_nt'])) $dt['pt_result_nt'] = '';
if(!isset($dt['pt_result_text'])) $dt['pt_result_text'] = '';
echo "<table width='100%'	border='0' cellspacing='0' cellpadding='0' style='table-layout: fixed;'>\n";
echo "	<tr>\n";
echo "		<td class='LabelCenterBorderB DateCell'>Date</td>\n";
// echo "		<td class='LabelCenterBorderLB' style='width: 110px;'>&nbsp;</td>\n";
echo "		<td class='LabelCenterBorderB' colspan='2'>Lab/Test/Result</td>\n";
// echo "		<td class='LabelCenterBorderB' style='width: 110px;'>&nbsp;</td>\n";
echo "		<td class='LabelCenterBorderLB' style='width: 28%;'>Comments</td>\n";
echo "		<td class='LabelCenterBorderLB' style='width: 65px;'>&nbsp;</td>\n";
echo "	</tr>\n";
$cnt=1;
$tab_base=100;
foreach($pap_data as $pap) {
	echo "	<tr>\n";
	echo "		<td class='DateCell wmtC'>\n";
	$tmp=($tab_base * $cnt);
	echo "		<input name='pt_date_$cnt' id='pt_date_$cnt' class='wmtDateInput' type='text' value='".$pap['pt_date']."' title='YYYY-MM-DD' tabindex='$tmp'/></td>\n";
	$tmp=($tab_base * $cnt)+10;
	echo "		<td class='BodyBorderL'><select name='pt_lab_$cnt' id='pt_lab_$cnt' class='Input' style='width: 95%;' tabindex='$tmp'>\n";
	echo ListSel($pap['pt_lab'], 'PT_Labs');
	echo "		</select>\n";
	echo "		<input name='pt_id_$cnt' id='pt_id_$cnt' type='hidden' value='".$pap['id']."' /></td>\n";
	$tmp=($tab_base * $cnt)+20;
	echo "		<td class='Body'><select name='pt_test_$cnt' id='pt_test_$cnt' class='Input' style='width: 95%;' tabindex='$tmp'>\n";
	echo ListSel($pap['pt_test'], 'PT_Tests');
	echo "		</select></td>\n";
	$tmp=($tab_base * $cnt)+30;
	echo "		<td class='BodyBorderL' style='text-align: right;'><div style='float: left;'>Comments:</div>";
	echo "		<input name='pt_hpv_result_$cnt' id='pt_hpv_result_$cnt' type='hidden' tabindex='-1' value='".$pap['pt_hpv_result']."' />\n";
	// echo ListSel($pap['pt_hpv_result'],'PT_HPV_Results');
	echo "		</td>\n";
	echo "		<td class='BodyBorderL'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"updatepap\",\"pt_id_\");' href='javascript:;'><span>Update</span></a></td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td>&nbsp;";
	if($pap['pt_document_id']) {
		echo "<div style='float: left; padding-right: 10px;'><a class='css_button' href='".$GLOBALS['webroot']."/controller.php?document&retrieve&patient_id=$pid&document_id=".$pap['pt_document_id']."&as_file=false' target='_blank'><span>View Report</span></a></div>";
	}
	echo "<input name='pt_num_links_$cnt' id='pt_num_links_$cnt' type='hidden' tabindex='-1' value='".$pap['pt_num_links']."' /></td>\n";
	$tmp=($tab_base * $cnt)+40;
	echo "		<td class='BodyBorderLB' colspan='2' rowspan='2'><textarea name='pt_result_text_$cnt' id='pt_result_text_$cnt' rows='2' class='FullInput' tabindex='$tmp'>".$pap['pt_result_text']."</textarea></td>\n";
	$tmp=($tab_base * $cnt)+50;
	echo "		<td class='BodyBorderLB' rowspan='2'>\n";
	echo "		<textarea name='pt_result_nt_$cnt' id='pt_result_nt_$cnt' class='FullInput' rows='2' tabindex='$tmp'>".$pap['pt_result_nt']."</textarea></td>\n";
	if($unlink_allow) {
		echo "		<td class='BodyBorderL'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"unlinkpap\",\"pt_id_\");' href='javascript:;' title='Un-link this pap tracking entry from this visit'><span>Un-Link</span></a></td>\n";
	} else {
		echo "		<td class='BodyBorderL'>&nbsp;</td>\n";
	}
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td class='BodyBorderB'>&nbsp;</td>\n";
	// echo "	<td class='BodyBorderB'>&nbsp;</td>\n";
	if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
		echo "<td class='BodyBorderLB'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"delpap\",\"pt_id_\",\"Pap Track\",\"{$pap['pt_num_links']}\");' href='javascript:;'><span>Delete</span></a></td>\n";
	} else {
		echo "		<td class='BodyBorderLB'>&nbsp;</td>\n";
	}
	echo "</tr>\n";
	$cnt++;
}
echo "	<tr>\n";
echo "		<td class='DateCell wmtC'>\n";
$tmp=$tab_base*$cnt;
echo "		<input name='pt_date' id='pt_date' class='DateInput' type='text' value='".$dt['pt_date']."' title='YYYY-MM-DD'  tabindex='$tmp' /></td>\n";
$tmp=($tab_base*$cnt)+10;
echo "		<td class='BodyBorderL'><select name='pt_lab' id='pt_lab' class='Input' style='width: 95%; ' tabindex='$tmp'>\n";
echo ListSel($dt['pt_lab'], 'PT_Labs');
echo "		</select>\n";
echo "		<input name='tmp_pap_cnt' id='tmp_pap_cnt' type='hidden' tabindex='-1' value='".($cnt - 1)."' /></td>\n";
$tmp=($tab_base*$cnt)+20;
echo "		<td><select name='pt_test' id='pt_test' class='Input' style='width: 95%;' tabindex='$tmp'>\n";
echo ListSel($dt['pt_test'], 'PT_Tests');
echo "		</select></td>\n";
$tmp=($tab_base*$cnt)+30;
echo "		<td class='BodyBorderL' style='text-align: right;'><div style='float: left;'>Comments:</div>";
// echo "		<select name='pt_hpv_result' id='pt_hpv_result' class='Input' tabindex='$tmp' />\n";
// echo ListSel($dt['pt_hpv_result'],'PT_HPV_Results');
echo "		</td>\n";
echo "		<td class='BodyBorderL'>&nbsp;</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td class='Body'>&nbsp;</td>\n";
$tmp=($tab_base*$cnt)+40;
echo "		<td class='BodyBorderLB' colspan='2' rowspan='2'><textarea name='pt_result_text' id='pt_result_text' class='FullInput' rows='2' tabindex='$tmp'>".$dt['pt_result_text']."</textarea></td>\n";
$tmp=($tab_base*$cnt)+50;
echo "		<td class='BodyBorderLB' rowspan='2'>\n";
echo "		<textarea name='pt_result_nt' id='pt_result_nt' class='FullInput' rows='2' tabindex='$tmp'>".$dt['pt_result_nt']."</textarea>\n";
echo "		</td>\n";
echo "		<td class='BodyBorderL'>&nbsp;</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td class='BodyBorderB'>&nbsp;</td>\n";
echo "		<td class='BodyBorderLB'>&nbsp;</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td class='CollapseBar' colspan='3'><a class='css_button' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"\",\"$id\",\"pap\");' href='javascript:;'><span>Add Another</span></a></td>\n";
echo "		<td class='CollapseBar' colspan='2'><a class='css_button' style='float: right; padding-right: 15px;' href='javascript:PopRTO();'><span>Orders/RTO</span></a></td>\n";
echo "	</tr>\n";
echo "</table>\n";
?>
