<?php //Templates List
use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

	$care_from = ""; $care_to = ""; $work_on = ""; $remarks = ""; $patient_name = ""; $print_form = false;
	if(isset($_GET['print_form']) && $_GET['print_form'] == 1){
		$care_from = $_GET['care_from'];
		$care_to = $_GET['care_to'];
		$work_on = $_GET['work_on'];
		$remarks = $_GET['remarks'];
		$pid = $_SESSION['pid'];
		$patient_name = $_GET['patient_name'];
		$print_form = true;
	}
?>
<html>
	<head>
		<TITLE><?php echo xl('Demographics Form Option'); ?></TITLE>
		<?php Header::setupHeader(['opener', 'datetime-picker']);?>
        <link rel="stylesheet" href="{$WEBROOT}/interface/forms/assessment_form/templates.css" type="text/css">
    <style>
        input[type="text"], textarea, select{
            display: block;
            width: 100%;
            border: 1px solid #DDD;
            padding: 5px;
            border-radius:4px;
        }

        label{
            margin-top: 10px;
            display:block;
        }

        .print_area_wrap{
            max-width: 600px;
            margin: 0px auto;
            font-size: 13px;
        }
        .print_area_wrap h3, .print_area_wrap h4{
            text-align: center;
        }
        .print_area_wrap p{
            padding-bottom: 6px;
        }
        .print_area_wrap span{
            display: inline-block;
        }
        .print_area_wrap .text_underline{
            border-bottom: 1px solid #000;
        }
        .print_area_wrap .remarks_para{
            background: url('../../../images/underline.png');
            min-height: 140px;
        }
        @media print{
            .hide_print{
                display: none;
            }
        }
    </style>
	</head>
	<body class="body_top">
		<?php if($print_form == false){ ?>
		<span class="title"><?php echo xl('Form/Letters'); ?></span><br/>
		<div class="form_letters_wrap">
			<div id="form_letters_inner">
				<form id="form_letters_form" action="" method="post">
					<table style="width:100%;">
						<tr class="row">
							<td class="col-sm-4" style="width:33%;">
								<label>Care From</label>
								<input type="text" class="datepicker" name="care_from" id="care_from" />
							</td>
							<td class="col-sm-4" style="width:33%;">
								<label>Care To</label>
								<input type="text" class="datepicker" name="care_to" id="care_to" />
							</div>
							<td class="col-sm-4" style="width:33%;">
								<label>Back to Work/School On</label>
								<input type="text" class="datepicker" name="work_on" id="work_on" />
							</td>
						</tr>
						<tr>
							<td colspan="3" class="col-sm-12">
								<label>Remarks</label>
								<a href="../assessment_form/form_option.php?option_id=remarks" class='iframe medium_modal' id="remarks_link" >
								<textarea name="remarks" id="remarks" rows="10" ></textarea>
								</a>
							</td>
						</tr>
						<tr>
							<td colspan="3" class="col-sm-12"><br/>
								<?php
								$psql = sqlQuery("SELECT concat(fname,' ',lname) as pname FROM patient_data WHERE pid='$pid'");
								$patient_name = $psql['pname'];
								?>
								<input type="hidden" name="patient_name" id="patient_name" value="<?php echo $patient_name; ?>">
								<button type="button" class="css_btn_small" style="float:right;" onclick="closeForm();"><?php echo xl('Cancel'); ?></button>
								<button type="submit" name="print_form_submit" id="print_form_submit" class="css_btn" style="float:right;"><?php echo xl('Print'); ?></button>
							</td>
						</tr>
					</table><br/>
				</form>
			</div>
		<?php } elseif($print_form == true) { ?>
			<div class="print_area_wrap">
				<h3>Certification to Return<br/>To Work/School</h3>
				<p>
					<span class="text_underline" style="width:530px;"><?php echo $patient_name; ?></span> &nbsp; <span>IIAS BEEN</span>
				</p>
				<p>
					<span>UNDER MY CARE FROM</span> &nbsp; <span class="text_underline" style="width:188px;"><?php echo $care_from; ?></span> &nbsp;
					<span>TO</span> &nbsp; <span class="text_underline" style="width:188px;"><?php echo $care_to; ?></span> &nbsp; <span>AND</span>
				</p>
				<p>
					<span>IS ABLE TO RETURN TO WORK/SCHOOL ON</span> &nbsp; <span class="text_underline" style="width:325px;"><?php echo $work_on; ?></span>
				</p><br/>
				<h4>LIMITATIONS/REMARKS</h4>
				<p class="remarks_para">
					<?php echo $remarks; ?>
				</p><br/><br/>
				<div>
					<p style="width:130px;text-align:center;border-top:1px solid #000;float:left;">Nurse/Doctor</p>
					<p style="width:130px;text-align:center;border-top:1px solid #000;float:right;">Date</p>
				</div>
				<div style="clear:both"></div>
				<div style="clear:both">
					<br/>
					<button type="button" class="css_btn_small hide_print" style="float:right;" onclick="window.close();"><?php echo xl('Close'); ?></button>
					<button type="button" class="css_btn_small hide_print" style="float:right;" onclick="window.print();"><?php echo xl('Print'); ?></button>
				</div>
			</div>
		<?php } ?>
		</div>
		<script>
		 $(document).ready(function() {
             $(".datepicker").datetimepicker({
                 <?php $datetimepicker_timepicker = false; ?>
                 <?php $datetimepicker_showseconds = false; ?>
                 <?php $datetimepicker_formatInput = false; ?>
                 <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                 <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
             });
             $(".medium_modal").on('click', function(e) {
                 e.preventDefault();e.stopPropagation();
                 let title = <?php echo xlj('Place title'); ?>;
                 dlgopen('', '', 850, 460, '', '', {
                     buttons: [
                         {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                     ],
                     allowResize: true,
                     allowDrag: true,
                     dialogId: '',
                     type: 'iframe',
                     url: $(this).attr('href')
                 });
             });
			});
			<?php if($print_form == false){ ?>
			$(document).ready(function(){
				$("#print_form_submit").on("click", function(){
					var care_from = $('#care_from').val();
					var care_to = $('#care_to').val();
					var work_on = $('#work_on').val();
					var remarks = $('#remarks').val();
					var patient_name = $('#patient_name').val();
					if(care_from == "" || care_to == "" || work_on == "" || remarks == "" || patient_name == ""){
						alert("Please enter all the info");
						return false;
					}
					var loc = window.location.href;
					loc = loc.replace('#', '');
					window.open(loc + '?care_from='+care_from+'&care_to='+care_to+'&work_on='+work_on+'&remarks='+remarks+'&patient_name='+patient_name+'&print_form=1', '_blank', 'resize');
					dlgclose();
				});
            });

			function closeForm(){
				window.close();
			}
			<?php } ?>

			<?php if($print_form == true){ ?>
				window.print();
			<?php } ?>
			function updateValue(id, value)
			{
				// this gets called from the popup window and updates the field with a new value
				var current = document.getElementById(id).value;
				var newValue = current + value;
				document.getElementById(id).value = newValue;
				document.getElementById(id).focus();
			}
		</script>
	</body>
</html>
