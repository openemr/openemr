<?php

require_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

use OpenEMR\Core\Header;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$logsData = wmtCase::fetchCaseAlertLogs($id);

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Logs'), ENT_NOQUOTES); ?></title>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
</head>
<body>
<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
	<table id="results_table" class="table text table-bordered table-striped table-sm">
		<thead class="thead-dark">
			<tr class="showborder_head">
				<th width="25" align="center"><?php echo xl('Sr.'); ?></th>
				<th><?php echo xl('Delivery Date'); ?></th>
				<th><?php echo xl('Notes'); ?></th>
				<th><?php echo xl('Username'); ?></th>
				<th><?php echo xl('Created Time'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$ci = 1;
			foreach ($logsData as $key => $item) {
				?>
				<tr>
					<td align="center"><?php echo $ci; ?></td>
					<td><?php echo $item['delivery_date']; ?></td>
					<td><?php echo $item['notes']; ?></td>
					<td><?php echo $item['user_name']; ?></td>
					<td><?php echo date('d-m-Y h:i:s',strtotime($item['created_date'])); ?></td>
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
		
		table = jQuery('#results_table').DataTable({
			'initComplete': function(settings){
				//Handle Footer
				handleDataTable(this);
		    },
			'order': [[1, 'asc']],
			'pageLength': 50,
			'bLengthChange': true,
			'ordering': false,
			'autoWidth':true,
			'scrollY': '100vh',
    		'scrollCollapse': true,
    		'responsive': {
			    details: false
			}
		});
		
	});
</script>
</body>
</html>