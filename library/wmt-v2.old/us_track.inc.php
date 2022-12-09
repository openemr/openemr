<?php
if(!isset($dt['us_date'])) $dt['us_date'] = '';
if(!isset($dt['us_type'])) $dt['us_type'] = '';
if(!isset($dt['us_comm'])) $dt['us_comm'] = '';
if(!isset($dt['us_rev'])) $dt['us_rev'] = '';
if(!isset($ultra)) $ultra = array();
if(!isset($pop_form)) $pop_form=false;
if(!isset($unlink_allow)) $unlink_allow=false;
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
echo "		<tr>\n";
echo "			<td class='LabelCenterBorderB' style='width: 90px'>Date</td>\n";
echo "			<td class='LabelCenterBorderLB'>Type</td>\n";
echo "			<td class='LabelCenterBorderLB'>Notes</td>\n";
echo "			<td class='LabelCenterBorderLB' style='width: 90px'>Reviewed</td>\n";
if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super') && $unlink_allow) {
	echo "			<td class='LabelBorderLB' style='width: 175px'>&nbsp;</td>\n";
} else if($unlink_allow) {
	echo "			<td class='LabelBorderLB' style='width: 115px'>&nbsp;</td>\n";
} else if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) {
	echo "			<td class='LabelBorderLB' style='width: 115px'>&nbsp;</td>\n";
} else {
	echo "			<td class='LabelBorderLB' style='width: 65px'>&nbsp;</td>\n";
}
echo "		</tr>\n";
$cnt=1;
if(isset($ultra) && (count($ultra) > 0)) {
	foreach($ultra as $prev) {
		echo "<tr>\n";
		echo "<td class='BodyBorderB'><input name='us_id_$cnt' id='us_id_$cnt' type='hidden' readonly='readonly' value='".$prev['id']."' /><input name='us_num_links_$cnt' id='us_num_links_$cnt' type='hidden' tabindex='-1' value='".$prev['num_links']."' /><input name='us_dt_$cnt' id='us_dt_$cnt' class='FullInput' tabindex='-1' type='text' value='".$prev['begdate']."' title='YYYY-MM-DD' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='us_type_$cnt' id='us_type_$cnt' class='FullInput' tabindex='-1' value='".$prev['title']."' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='us_comm_$cnt' id='us_comm_$cnt' class='FullInput' type='text' tabindex='-1' value='".$prev['comments']."' /></td>\n";
		echo "<td class='BodyBorderLB'><input name='us_rev_$cnt' id='us_rev_$cnt' class='FullInput' type='text' tabindex='-1' value='".$prev['referredby']."' /></td>\n";
		echo "<td class='BodyBorderLB'><div class='wmtListButton'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"updateultra\",\"us_id_\",\"Ultrasound\");' href='javascript:;'><span>Update</span></a></div>";
		if($unlink_allow) {
			echo "<div class='wmtListButton'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"unlinkultra\",\"us_id_\",\"Ultrasound\");' href='javascript:;'><span>Un-Link</span></a></div>\n";
		}
		if(\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super')) {
			echo "<div class='wmtListButton'><a class='css_button_small' tabindex='-1' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"$cnt\",\"$id\",\"delultra\",\"us_id_\",\"Ultrasound\",\"{$prev['num_links']}\");' href='javascript:;'><span>Delete</span></a></div>\n";
		}
		echo "</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
}
$cnt--;
echo "		<tr>\n";
echo "			<td class='BodyBorderB'><input name='us_date' id='us_date' class='FullInput' type='text' value='",$dt['us_date'],"' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='us_type' id='us_type' class='FullInput' type='text' value='".$dt['us_type']."' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='us_comm' id='us_comm' class='FullInput' type='text' value='",$dt['us_comm'],"' /></td>\n";
echo "			<td class='BodyBorderLB'><input name='us_rev' id='us_rev' class='FullInput' type='text' value='",$dt['us_rev'],"' /></td>\n";
echo "			<td class='BodyBorderLB'>&nbsp;</td>\n";
echo "		</tr>\n";
echo "		</tr>\n";
echo "			<td class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"'><a class='css_button' onClick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"\",\"$id\",\"ultra\");' href='javascript:;'><span>Add Another</span></a></td>\n";
echo "			<td class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"' colspan='2'><input name='tmp_us_cnt' id='tmp_us_cnt' type='hidden' tabindex='-1' value='$cnt' />&nbsp;</td>\n";
if($pop_form) {
	echo "			<td colspan='2' class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"'><a class='css_button' href='javascript:;' onclick=\"wmtOpen('../../../custom/document_popup.php?pid=".$pid."', '_blank', 800, 600);\"><span>View Documents</span></a></td>\n";
} else {
	echo "			<td colspan='2' class='",(($frmn=='form_acog_antepartum_D')?'wmtCollapseBarBlack':'wmtCollapseBar'),"'><a class='css_button' href='javascript:;' onclick=\"dlgopen('../../../custom/document_popup.php?pid=".$pid."', 'blank', 800, 600);\"><span>View Documents</span></a></td>\n";
}
echo "		</tr>\n";
echo "		</table>\n";
?>
