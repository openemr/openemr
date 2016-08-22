<?php
/**
 *
 * QRDA Download 
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */


$fake_register_globals=false;
$sanitize_all_escapes=true;
  
require_once("../interface/globals.php");
require_once "$srcdir/report_database.inc";
require_once("$srcdir/formatting.inc.php");
require_once ("$srcdir/options.inc.php");
require_once("$srcdir/sanitize.inc.php");
require_once("qrda_category1.inc");

$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";
$provider_id = (isset($_GET['provider_id'])) ? trim($_GET['provider_id']) : "";

$report_view = collectReportDatabase($report_id);
$dataSheet = json_decode($report_view['data'],TRUE);
$type_report = $report_view['type'];
$type_report = (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014") ||
                  ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) ? $type_report : "standard";

?>

<html>
	
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>    
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script language="JavaScript">
	var reportID = '<?php echo attr($report_id); ?>';
	var provider_id = '<?php echo attr($provider_id);?>';
	var zipFileArray = new Array();
	var failureMessage = "";
	$(document).ready(function(){
		$("#checkAll").change(function() {
			var checked =  ( $("#checkAll").attr("checked") ) ? true : false;
			$("#thisForm input:checkbox").each(function() {
				$(this).attr("checked", checked);
			});
		});
	});
	
	function downloadSelected() {
		zipFileArray.length = 0;
		var criteriaArray = new Array();
		$("#thisForm input:checkbox:checked").each(function() {
			if ( $(this).attr("id") == "checkAll")
				return;
			criteriaArray.push($(this).attr("id"));
		});
		if ( criteriaArray.length == 0 ) {
			alert("<?php echo xls('Please select at least one criteria to download');?>");
			return false;
		}
		for( var i=0 ; i < criteriaArray.length ; i++) {
			var checkBoxCounterArray = criteriaArray[i].split("check");
			var ruleID = $("#text" + checkBoxCounterArray[1]).val();
			//console.log(ruleID);
			var lastOne = ( ( i + 1 ) == criteriaArray.length ) ? 1 : 0;
			downloadXML(checkBoxCounterArray[1],lastOne,ruleID);
		}
	}
	
	function downloadXML(counter,lastOne) {
		$("#download" + counter).css("display","none");
		$("#spin" + counter).css("display","inline");
		$.ajax({
			type : "POST",
			url: "ajax_download.php",
			data : {
				reportID: reportID,
				counter: counter,
				ruleID: $("#text" + counter).val(),
				provider_id: provider_id
			},
			context: document.body,
			success :
		 function(data){
			// Check if download is complete
			var status = data.substr(0, 8);
			if ( status == "FAILURE:") {
				data = data.substr(8);
				//console.log(data);
				failureMessage += data + "\n";
			} else {
				zipFileArray.push(data);
				$("#checkmark" + counter).css("display","inline");
			}
			$("#download" + counter).css("display","inline");
			$("#spin" + counter).css("display","none");
			if ( lastOne == 1 ) {
				if ( zipFileArray.length ) {
					var zipFiles = zipFileArray.join(",");
					//console.log(zipFiles);
					window.location = 'ajax_download.php?fileName=' + zipFiles;
					zipFileArray.length = 0;
				}
				if ( failureMessage ) {
					console.log(failureMessage);
					alert(failureMessage);
				}
				failureMessage = "";
			}
		 }
		});
	}
	
	function closeMe() {
		window.close();
	}
</script>
<style>
	.downloadIcon:hover {
		cursor: hand;
	}
	.multiDownload {
		
	}
</style>
</head>

<body class="body_top">
<form id="thisForm" name="thisForm">
<table>
	<tr>
		<td><span class="title"><?php echo xlt("Generate/Download QRDA I - 2014"); ?>&nbsp;</span></td>
		<td>
			<a class="css_button multiDownload" href="#" onclick="downloadSelected();"><span><?php echo xlt("Download"); ?></span></a>
			<a class="css_button" href="#" onclick="closeMe();"><span><?php echo xlt("Close"); ?></span></a>
		</td>
	</tr>
</table>
<br/>
<div id="report_results" style="width:95%">
<table class="oemr_list text">
	<thead>
		<th scope="col" class="multiDownload">
			<input type="checkbox" name="checkAll" id="checkAll"/>
			<div style="display:none" id=downloadAll>
				<img class='downloadIcon' src='<?php echo $GLOBALS['webroot'];?>/images/downbtn.gif' onclick=downloadAllXML(); />
			</div>
			<div style='display:none' id=spinAll>;
				<img src='<?php echo $GLOBALS['webroot'];?>/interface/pic/ajax-loader.gif'/>
			</div>
		</th>
		<th scope="col">
		 <?php echo xlt('Title'); ?>
		</th>

		<th scope="col">
		 <?php echo xlt('Download'); ?>
		</th>
		<th scope="col">&nbsp;&nbsp;&nbsp;</th>
	</thead>
	<tbody>
		<?php
			$counter = 0;
			foreach ($dataSheet as $row) {
			if (isset($row['is_main']) || isset($row['is_sub'])) {
				if ( count($cqmCodes) && in_array($row['cqm_nqf_code'],$cqmCodes) ) {
					continue;
				}
				echo "<tr>";
				$cqmCodes[] = $row['cqm_nqf_code'];
				echo "<td class=multiDownload>";
				echo "<input id=check" . attr($counter) . " type=checkbox />";
				echo "</td>";
				echo "<td class='detail'>";
				if (isset($row['is_main'])) {
					echo "<b>".generate_display_field(array('data_type'=>'1','list_id'=>'clinical_rules'),$row['id'])."</b>";
					$tempCqmAmcString = "";
					if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
					  if (!empty($row['cqm_pqri_code'])) {
						$tempCqmAmcString .= " " .  xl('PQRI') . ":" . $row['cqm_pqri_code'] . " ";
					  }
					  if (!empty($row['cqm_nqf_code'])) {
						$tempCqmAmcString .= " " .  xl('NQF') . ":" . $row['cqm_nqf_code'] . " ";
					  }
					}
					if (!empty($tempCqmAmcString)) {
						echo "(".text($tempCqmAmcString).")";
					}
				} else {
					echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$row['action_category']);
					echo ": " . generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$row['action_item']);
				}
				echo "<input type=hidden id=text" . attr($counter) . " name=text" . attr($counter) . " value='" . attr($row['cqm_nqf_code']) . "'/>";
				echo "</td>";
				echo "<td align=center>";
				echo "<div id=download" . attr($counter) . ">";
				echo "<img class='downloadIcon' src='" . $GLOBALS['webroot'] . "/images/downbtn.gif' onclick=downloadXML(" . attr($counter) . ",1); />";
				echo "</div>";
				echo "<div style='display:none' id=spin" . attr($counter) . ">";
				echo "<img src='" . $GLOBALS['webroot'] . "/interface/pic/ajax-loader.gif'/>";
				echo "</div>";
				echo "</td>";
				echo "<td>";
				echo "<div style='display:none' id=checkmark" . attr($counter) . ">";
				echo "<img src='" . $GLOBALS['webroot'] . "/images/checkmark.png' />";
				echo "</div>";
				echo "</td>";
				echo "</tr>";
				$counter++;
			}
		} ?>
	</tbody>
</table>
</div>

</form>
</body>
</html>
