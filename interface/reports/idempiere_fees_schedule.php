<?php
/**
 * This is a report to create a patient ledger of charges with payments
 * applied.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    WMT
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Rich Genandt <rgenandt@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/appointments.inc.php');

require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");
require_once("./idempiere_pat_ledger_fun.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$form_code_1 = isset($_REQUEST['form_code_1']) ? $_REQUEST['form_code_1'] : "";

?>
<head>

    <title><?php echo xlt('Fees schedule'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
    <?php Header::setupHeader(['opener', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs']); ?>
</head>
<body class="body_top">
<span class='title' id='title'><?php echo xlt('Fees schedule'); ?></span>

<div id="report_results">
</div>

<div id="mainWrapper">
<span class="noteText">Note: search for multiple codes by using a comma seperator, you can search by part of the code as well.</span>
<form method='post' action='idempiere_fees_schedule.php' id='theform' onsubmit='return top.restoreSession()'>
	<div id="report_parameters">
		<table>
 			<tr>
 				<td width='70%'>
	 				<div>
					    <table class='text'>
					    	<tr>
					    		<td class='control-label' width="120">
						        	<label><?php echo xlt('Procedure Code'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;</label>
						      	</td>
						      	<td>
						        	<input type='text' class='form-control code1-input' name='form_code_1' id="form_code_1" value='<?php echo $form_code_1; ?>' style='max-width: 400px;'>
						      	</td>
					    	</tr>
					    </table>
					</div>
				</td>
				<td align='left' valign='middle' height="100%">
					<table style='border-left:1px solid; width:100%; height:100%' >
				        <tr>
				            <td>
				                <div class="text-center">
				          			<div class="btn-group" role="group">
				                    <a href='#' class='btn btn-secondary btn-save' onclick="checkSubmit();" >
				                    <?php echo xlt('Submit'); ?></a>
				                	</div>
				                </div>
				            </td>
				        </tr>
				    </table>
				</td>
 			</tr>
 		</table>
	</div>
</form>
<div class="table-responsive">
<table id="feesSchedule" class="text table table-sm msg-table" style="width:100%">
    <thead class="thead-light">
        <tr>
            <th><?php xl('Code','e'); ?></th>
            <th><?php xl('Description','e'); ?></th>
            <th><?php xl('Chiro Bill','e'); ?></th>
            <th><?php xl('Chiro Cash','e'); ?></th>
            <th><?php xl('MD-DO Bill','e'); ?></th>
            <th><?php xl('MD-DO Cash','e'); ?></th>
            <th><?php xl('NP-PA Bill','e'); ?></th>
            <th><?php xl('NP-PA Cash','e'); ?></th>
            <th><?php xl('Neuro-Ortho Bill','e'); ?></th>
            <th><?php xl('Neuro-Ortho Cash','e'); ?></th>
            <th><?php xl('InActive','e'); ?></th>
            <th><?php xl('Patient Only Responsible','e'); ?></th>
            <th><?php xl('Date Created','e'); ?></th>
            <th><?php xl('UserCode','e'); ?></th>
            <th width="80"><?php xl('DateModified','e'); ?></th>
            <!-- <th>Type</th> -->
        </tr>
    </thead>
</table>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
	    $('#feesSchedule').DataTable( {
	    	"bProcessing": true,
	        "processing": true,
	        "serverSide": true,
	        "bSort" : false,
	        "searching": false,
	        "ajax":{
		        url :"idempiere_fees_schedule_ajax.php?page=datatable&code1=<?php echo $form_code_1; ?>",
		        type: "POST",
		        error: function(){
		          //$("#post_list_processing").css("display","none");
		        }
		  	}
	    });
    });

    function checkSubmit() {
    	document.forms[0].submit();
    }
</script>
</body>
</html>