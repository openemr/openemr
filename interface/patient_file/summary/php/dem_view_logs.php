<?php

use OpenEMR\Core\Header;

require_once("../../../globals.php");
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\Demographicslib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$logsData = Demographicslib::fetchAlertLogs($pid);

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Logs'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>
	</script>
</head>
<body>
<div class="table-responsive table-container datatable-container c-table-bordered footer-p o-overlay">
	<table id="table_results" class="table valign-middle" class="border-0 display">
		<thead class="thead-dark">
			<tr class="showborder_head">
				<th width="50" align="center">Sr.</th>
				<th>New Value</th>
				<th>Old Value</th>
				<th>Username</th>
				<th>DateTime</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$ci = 1;
			foreach ($logsData as $key => $item) {
				?>
				<tr>
					<td align="center"><?php echo $ci; ?></td>
					<td><?php echo $item['new_value']; ?></td>
					<td><?php echo $item['old_value']; ?></td>
					<td><?php echo $item['user_name']; ?></td>
					<td width="180"><?php echo date('d-m-Y h:i:s',strtotime($item['date'])); ?></td>
				</tr>
				<?php
				$ci++;
			}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var table = jQuery('#table_results').dataTable({
			'initComplete': function(settings){
				 //Handle Footer
				 handleDataTable(this);
		     },
			'searching': false,
			'pageLength': 10,
			'bLengthChange': true,
			'order': [[2, 'asc']],
			'ordering': false,
			'paging': true,
			'scrollY': '100vh',
    		'scrollCollapse': true,
    		'scrollCollapse': true,
    		'responsive': {
			    details: false
			}
			<?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
		});
	});
</script>