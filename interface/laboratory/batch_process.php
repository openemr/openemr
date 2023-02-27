<?php
/** **************************************************************************
 *	LABORATORY/BATCH_PROCESS.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technologies, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage core
 *  @version 2.0
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/wmt/wmt.include.php";

use OpenEMR\Core\Header;

?>
<html>
<head>
<?php //html_header_show();?>
<title><?php xl('Batch Result Processing','e'); ?></title>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>


<script>
	function doSubmit() {
		if ($('#lab_id').val() == '') {
			alert("Processor required for execution!!");
			return false;
		}
		else {
			switch ($("#lab_id option:selected").attr('key')) {
				case "quest":
					$("#theform").attr('action', '../forms/quest/batch.php');
					break;
				case "labcorp":
					$("#theform").attr('action', '../forms/labcorp/batch.php');
					break;
				default:
					$("#theform").attr('action', '../forms/laboratory/batch.php');
			}
			$('#theform').submit();
		}
	}
</script>
</head>
<body class="body_top">
	<span class='title'><?php xl('Batch Result Processing','e'); ?></span>

	<form method='post' name='theform' id='theform'
		action='batch_process.php'>
		<input type='hidden' name='process' value='1' />
		<div id="report_parameters">
			<table>
				<tr>
					<td width='800px'>
						<div style='float: left'>

							<table class='text' style='border:none'>
								<tr>
									<td class='label'><?php xl('Processor','e'); ?>: </td>
									<td><select id='lab_id' name='lab_id' class="form-control form-control-sm">
											<option value=''></option>
<?php 
	$result = sqlStatement("SELECT * FROM procedure_providers WHERE DorP != 'D' AND protocol != 'INT' ORDER BY name");
	while ($processor = sqlFetchArray($result)) {
		echo "<option key='".$processor['type']."' value='".$processor['ppid']."'>".$processor['name']."</option>\n";
	}
?>
           		</select></td>
									<td class='label'><?php xl('From','e'); ?>: </td>
									<td><input type='text' name='form_from_date' class="form-control form-control-sm" 
										id="form_from_date" size='10'
										value='<?php echo $form_from_date ?>'
										onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
										title='yyyy-mm-dd' style='display: inline-block;width: 140px;'> <img src='../pic/show_calendar.gif'
										align='absbottom' width='24' height='22' id='img_from_date'
										border='0' alt='[?]' style='cursor: pointer'
										title='<?php xl('Click here to choose a date','e'); ?>'></td>
									<td class='label'><?php xl('To','e'); ?>: </td>
									<td><input type='text' name='form_to_date' id="form_to_date" class="form-control form-control-sm" 
										size='10' value='<?php echo $form_to_date ?>'
										onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
										title='yyyy-mm-dd' style='display: inline-block;width: 140px;'> <img src='../pic/show_calendar.gif'
										align='absbottom' width='24' height='22' id='img_to_date'
										border='0' alt='[?]' style='cursor: pointer'
										title='<?php xl('Click here to choose a date','e'); ?>'></td>
									<td class='label'><?php xl('Include Details','e'); ?>: </td>
									<td><input type='checkbox' id='form_debug' name='form_debug'
										value='1' /></td>
								</tr>
							</table>

						</div>
					</td>
					<td align='left' valign='middle' height="100%">
						<table style='border-left: 1px solid; box-shadow:none; border-bottom:none; background-color:transparent; text-align:right; float:right; width:100px'>
							<tr>
								<td>
									<div style='margin-left: 15px'>
										<a href='#' class='css_button' onclick='doSubmit()'> <span>
						<?php xl('Submit','e'); ?>
					</span>
										</a>

            <?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
            <a href='#' class='css_button' onclick='window.print()'> <span>
							<?php xl('Print','e'); ?>
						</span>
										</a>
            <?php } ?>
          </div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

		</div>
		<!-- end report_parameters -->

		<div class='text'>
			Leave the date fields <b>BLANK</b> for normal processing. Enter dates
			<b>ONLY</b> if previously processed results must be re-processed. <br />
			The dates entered represent the dates the result transactions where
			originally processed by the gateway. <br /> Select whether to display
			processing details using the checkbox and click Submit.
		</div>
		<input type="hidden" name="browser" value="1" />
	</form>
	<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>
</body>

</html>

