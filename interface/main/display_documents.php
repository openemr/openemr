<?php
/**
 * Displays the documents
 * Only Lab documents for now.
 *
 * Copyright (C) 2014 Ensoftek
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Hema Bandaru <hemab@drcloudemr.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once('../globals.php');
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/payment_jav.inc.php");

//ini_set("display_errors","on");

$DateFormat = DateFormatRead();
$curdate = date_create(date("Y-m-d"));
date_sub($curdate, date_interval_create_from_date_string("7 days"));
$sub_date = date_format($curdate, 'Y-m-d');

// Set the default dates for Lab document search
$form_from_doc_date = ( $_GET['form_from_doc_date'] ) ? $_GET['form_from_doc_date'] : oeFormatShortDate(fixDate($_GET['form_from_doc_date'],$sub_date));
$form_to_doc_date = ( $_GET['form_to_doc_date'] ) ? $_GET['form_to_doc_date'] : oeFormatShortDate(fixDate($_GET['form_to_doc_date'],date("Y-m-d")));

if($GLOBALS['date_display_format'] == 1) {
   $title_tooltip = "MM/DD/YYYY";
} elseif($GLOBALS['date_display_format'] == 2) {
   $title_tooltip = "DD/MM/YYYY";
} else {
   $title_tooltip = "YYYY-MM-DD";
}

$display_div = "style='display:block;'"; 
$display_expand_msg = "display:none;"; 
$display_collapse_msg = "display:inline;";

?>

<html>
<head>

<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot'] ?>/library/js/qtip/jquery.qtip.min.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.8.6.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/qtip/jquery.qtip.js"></script>

<script type="text/javascript">
    var global_date_format = '<?php echo $DateFormat; ?>';
	$(document).ready(function() {
		$("#docdiv a").each(function() {
			$(this).qtip({
				content : '<iframe class="qtip-box" src="' + $(this).attr('title') + '" />',
				hide : {
					delay : 20,
					fixed : true
				},
				position : {
					at : 'bottom left',
					viewport : $(window),
					adjust: {
						x: 20
					},
				},
				style: 'qtip-style'
			})
		})
	});
	
	function validateDate(fromDate,toDate){
		var frmdate = $("#" + fromDate).val();
		var todate = $("#" + toDate).val();
		if ( (frmdate.length > 0) && (todate.length > 0) ) {
			if ( DateCheckGreater(frmdate, todate, global_date_format) == false ){
				alert("<?php xl('To date must be later than From date!','e'); ?>");
				return false;
			}
		}
		document.location='<?php echo $GLOBALS['webroot']; ?>/interface/main/display_documents.php?' + fromDate + '='+frmdate+'&' + toDate + '='+todate;                
	}	

	function expandOrCollapse(type,prefixString) {
		if(type == 1 ) {
			$("#" + prefixString + "filterdiv").show();
			$("#" + prefixString + "div").show();
			$("#" + prefixString + "collapse").show();
			$("#" + prefixString + "expand").hide();
		} else {
			$("#" + prefixString + "filterdiv").hide();
			$("#" + prefixString + "div").hide();
			$("#" + prefixString + "collapse").hide();
			$("#" + prefixString + "expand").show();
		}
	}

</script>

<style type="text/css">
.qtip-box{
	width : 100%;
	height : 95%;
}

.qtip-style {
	width: 75%;
	max-width: 75%;
	height: 50%;
	max-height: 50%;
}
.qtip {
	max-width : 100%;
}

.linkcell {
	max-width: 250px ;
	text-overflow: ellipsis;
	overflow: hidden;
	valign : absbottom;
}

</style>
</head>

<body class="body_top">

<div>
	<span class='title'><?php echo text('Lab Documents'); ?></span>
   	<span id='docexpand' onclick='expandOrCollapse(1,"doc")' style='cursor:pointer;<?php echo $display_expand_msg ?>'>(expand)</span>
    <span id='doccollapse' onclick='expandOrCollapse(2,"doc")' style='cursor:pointer;<?php echo $display_collapse_msg ?>'>(collapse)</span>
	<br><br>
    <div id='docfilterdiv'<?php echo $display_div; ?>>
	<table style="margin-left:10px; " width='40%'>
		<tr>
			<td scope="row" class='label'><?php echo text('From'); ?>:</td>
			<td><input type='text' name='form_from_doc_date' id="form_from_doc_date"
				size='10' value='<?php echo $form_from_doc_date ?>' readonly="readonly" title='<?php echo $title_tooltip ?>'> 
				<img alt="Date Selector" src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_doc_date'
				border='0' alt='[?]' style='cursor: pointer' title='<?php echo text('Click here to choose a date'); ?>'></td>
			<script>
					Calendar.setup({inputField:"form_from_doc_date", ifFormat:global_date_format, button:"img_from_doc_date"});
			</script>
			<td class='label'><?php echo text('To'); ?>:</td>
			<td><input type='text' name='form_to_doc_date' id="form_to_doc_date"
				size='10' value='<?php echo $form_to_doc_date ?>' readonly="readonly" title='<?php echo $title_tooltip ?>'> 
				<img alt="Date Selector" src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_doc_date'
				border='0' alt='[?]' style='cursor: pointer' title='<?php echo text('Click here to choose a date'); ?>'></td> 
			<script>
				Calendar.setup({inputField:"form_to_doc_date", ifFormat:global_date_format, button:"img_to_doc_date"});
			</script>
			<td>
				<span style='float: left;' id="docrefresh">
					<a href='#' class='css_button'  onclick='return validateDate("form_from_doc_date","form_to_doc_date")'> <span><?php echo text('Refresh'); ?> </span></a> 
				</span>
			</td>
		</tr>
	</table>	
	</div>
</div>

<div id='docdiv' <?php echo $display_div; ?>>
	<?php        
	$current_user = $_SESSION["authId"];
	$date_filter = '';
	if ($form_from_doc_date) {
		$form_from_doc_date = DateToYYYYMMDD($form_from_doc_date);
		$date_filter = " DATE(d.date) >= '$form_from_doc_date' ";
	}
	if ($form_to_doc_date) {
		$form_to_doc_date = DateToYYYYMMDD($form_to_doc_date);
		$date_filter .= " AND DATE(d.date) <= '$form_to_doc_date'";
	}
	// Get the category ID for lab reports.
	$query = "SELECT rght FROM categories WHERE name = ?";
	$catIDRs = sqlQuery($query,array($GLOBALS['lab_results_category_name']));
	$catID = $catIDRs['rght'];
	
	$query = "SELECT d.*,CONCAT(pd.fname,' ',pd.lname) AS pname,GROUP_CONCAT(n.note ORDER BY n.date DESC SEPARATOR '|') AS docNotes, 
		GROUP_CONCAT(n.date ORDER BY n.date DESC SEPARATOR '|') AS docDates FROM documents d 
		INNER JOIN patient_data pd ON d.foreign_id = pd.pid 
		INNER JOIN categories_to_documents ctd ON d.id = ctd.document_id AND ctd.category_id = " . $catID . " 
		LEFT JOIN notes n ON d.id = n.foreign_id 
		WHERE " . $date_filter . " GROUP BY d.id ORDER BY date DESC";
	$resultSet = sqlStatement($query);
	?>
	
	<table border="1" cellpadding=3 cellspacing=0>
	<tr class='text bold'>
		<th align="left" width="10%"><?php echo text('Date'); ?></th>
		<th align="left" class="linkcell" width="20%" ><?php echo text('Name'); ?></th>
		<th align="left" width="20%"><?php echo xlt('Patient'); ?></th>
		<th align="left" width="30%"><?php echo text('Note'); ?></th>
		<th width="10%"><?php echo text('Encounter#'); ?></th>
	</tr>
	<?php
	if (sqlNumRows($resultSet)) { 
		while ( $row = sqlFetchArray($resultSet) ) { 
			$url = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . $row["foreign_id"] . "&document_id=" . $row["id"] . '&as_file=false';
			// Get the notes for this document.
			$notes = array();
			$note = '';
			if ( $row['docNotes'] ) {
				$notes = explode("|",$row['docNotes']);
				$dates = explode("|", $row['docDates']);
			}
			for ( $i = 0 ; $i < count($notes) ; $i++ )
				$note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "<br />";
			?>
			<tr class="text">
				<td><?php echo oeFormatShortDate(date('Y-m-d', strtotime($row['date']))); ?> </td>
				<td class="linkcell">
					<a id="<?php echo $row['id']; ?>" title='<?php echo $url; ?>'><?php echo basename($row['url']); ?></a>
				</td>
				<td><?php echo attr($row['pname']); ?> </td>
				<td><?php echo $note; ?> &nbsp;</td>
				<td align="center"><?php echo ( $row['encounter_id'] ) ? $row['encounter_id'] : ''; ?> </td>
			</tr>
		<?php } ?>
	<?php } ?>
	</table>
</div>
</body>
</html>