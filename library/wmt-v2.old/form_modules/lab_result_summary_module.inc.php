<?php
include_once($GLOBALS['srcdir'].'/options.inc.php');
$sql = "
			SELECT
				prp.procedure_order_id as `order_number`,
				po.provider_id,
				po.date_ordered,
				prs.result_code,
				prs.result_text,
				prs.facility,
				prs.result,
				lo.title,
				prs.units,
				prs.result_status,
				prp.report_status AS status,
				prs.procedure_result_id,
				pt.name as `name`,
				pt.procedure_type_id as `type`,
				prs.date as `date`,
				prs.range as `range`,
				prs.abnormal as `abnormal`,
				prs.comments as `comments`,
				pt.lab_id AS `lab`,
				poc.procedure_code AS order_code,
				poc.procedure_name AS order_text
			FROM
				procedure_result AS prs
			LEFT JOIN procedure_report AS prp
				ON prs.procedure_report_id = prp.procedure_report_id
			LEFT JOIN procedure_order AS po
				ON prp.procedure_order_id = po.procedure_order_id
			LEFT JOIN procedure_order_code AS poc
				ON poc.procedure_order_id = po.procedure_order_id
				AND poc.procedure_order_seq = prp.procedure_order_seq
			LEFT JOIN procedure_type AS pt
				ON pt.lab_id = po.lab_id
				AND pt.procedure_code = prs.result_code
				AND pt.procedure_type = 'res'
				AND prs.result_code != '' AND prs.result_code IS NOT NULL
			LEFT JOIN list_options AS lo
				ON lo.list_id = 'proc_unit' AND pt.units = lo.option_id
			WHERE po.patient_id=? 
				AND po.portal_flag = 1
				AND prs.result NOT LIKE 'DNR' 
				AND prs.result_text NOT LIKE '%Report Text%'
			GROUP BY prs.result_code, prp.procedure_order_id
			ORDER BY po.date_ordered DESC";

$res = sqlStatement( $sql, array($pid) );
//DEBUG
//if ($pid == '3422') {
//echo "<pre>";
//var_dump($res);
//}
//DEBUG
if (sqlNumRows( $res ) > 0) {
?>

	<style>
	.datebar {
		cursor: pointer;
		cursor: hand;
	}
	</style>
	
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<th><?php echo xlt('Order Date'); ?></th>
			<th><?php echo xlt('Order Name'); ?></th>
			<th><?php echo xlt('Result Name'); ?></th>
			<th><?php echo xlt('Flag'); ?>&nbsp;</th>
			<th><?php echo xlt('Value'); ?></th>
			<th style='min-width: 70px'><?php echo xlt('Range'); ?></th>
		</tr>
<?php
	$even = false;
	$last = '';
	while( $row = sqlFetchArray( $res ) ) {
		if ($even) {
			$class = "class1_even";
			$even = false;
		} else {
			$class = "class1_odd";
			$even = true;
		}
		// RON $date=explode('-',$row['date_ordered']);
		$time = strtotime( $row['date_ordered'] );
		$date = date( 'Y-m-d', $time );
		
		if ($date != $last) {
			if ($last) $hide = true;
			$last = $date;
			echo "<tr class='datebar' id='$date' style='background-color:#3f51b5'>";
			echo "<td colspan='6' style='color:#fff;font-weight:bold;text-align:center;padding:6px'>" . htmlspecialchars( $date ) . "</td>";
			echo "</tr>";
		}
		
		echo "<tr class='" . $class . " box" . $date . " labbox' ";
		if ($hide) echo "style='display:none'";
		echo ">";
		echo "<td style='min-width:70px' class='no_phone'>" . htmlspecialchars( $date ) . "</td>";
		echo "<td class='no_phone'>" . htmlspecialchars( $row['order_text'], ENT_NOQUOTES ) . "</td>";
		echo "<td>" . htmlspecialchars( $row['result_text'], ENT_NOQUOTES ) . "</td>";
		echo "<td style='font-weight:bold'>" . generate_display_field( array(
				'data_type' => '1',
				'list_id' => 'proc_res_abnormal' 
		), $row['abnormal'] ) . "</td>";
		$result = $row['result'] . ' ' . $row['units'];
		if (strlen( $result ) > 50)
			$result = trim( substr( $result, 0, 50 ) ) . "...";
		echo "<td>" . htmlspecialchars( $result, ENT_NOQUOTES ) . "</td>";
		$range = $row['range'];
		if ($range == 'NON-REACTIVE')
			$range = 'NON';
		if (strlen( $range ) > 10)
			$range = trim( substr( $range, 0, 10 ) );
		echo "<td>" . htmlspecialchars( $range, ENT_NOQUOTES ) . "</td>";
		echo "</tr>";
	}
	
	echo "</table>";

} else {

	echo "<div style='width:100%' class='summary_item'>" . xlt( "No Results" ) . "</div>";

}
?>
<script>
	$(document).ready(function() {
		$(".datebar").click(function() {
			$(".labbox").css("display","none");
			$(".box" + this.id).toggle();
		});
	});
</script>
