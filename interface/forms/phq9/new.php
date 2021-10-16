<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Core\Header;
$returnurl = 'encounter_top.php';
$formid = isset($_GET['id']) ? $_GET['id'] : 0;

// verify whether form data deleted or not
// $form_sql = "SELECT deleted, form_id FROM `forms` WHERE pid = ? AND encounter = ? AND formdir = 'phq9' ORDER BY id DESC LIMIT 1";
// $form_res = sqlStatement($form_sql, array($_SESSION["pid"], $_SESSION["encounter"]));
// if(sqlNumRows($form_res)){
// 	$form_data = sqlFetchArray($form_res);
// 	if($form_data['deleted']){
// 		// deleting record from form_bio_psychosocial table for the corresponding 
// 		$del_sql = "DELETE FROM `form_phq9` WHERE pid = ? AND encounter = ? AND id = ?";
// 		sqlQuery($del_sql, array($_SESSION["pid"], $_SESSION["encounter"],$form_data['form_id']));
// 	}
// }

$data = array();

if($formid){

	$sql = "SELECT * FROM `form_phq9` WHERE pid = ? AND encounter = ? AND id = ?";
	$res = sqlStatement($sql, array($_SESSION["pid"], $_SESSION["encounter"], $formid));

	for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
		$data[$iter] = $row;
	}

}

//$patientDetails=getPatientData($_SESSION["pid"]);
//$name = $patientDetails['fname']." ".$patientDetails['lname'];
$userName = "";
$userId = "";
if($data[0]['evaluator']!=""){
	$userDetails = getProviderInfo($data[0]['evaluator']);
} else {
	$userDetails = getProviderInfo($_SESSION['authUserID']);
}
$userName = $userDetails[0]['fname']." ".$userDetails[0]['lname'];
$userId = $userDetails[0]['id'];
//Array ( [0] => Array ( [id] => 1 [username] => admin@admin.com [lname] => Administrator [fname] => Administrator [authorized] => 1 [info] => [facility] => [suffix] => ) )

?>

<html>

    <head>
        <title><?php echo xlt("ADL"); ?></title>

       <?php Header::setupHeader(['datetime-picker']);?>
	   <style type="text/css" title="mystyles" media="all">
		@media only screen and (max-width: 768px) {
			[class*="col-"] {
				width: 100%;
				text-align:left!Important;
			}
		}

	   </style>
	<?php
	//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
	$use_validate_js = 1;
	require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
	<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>

    <script type="text/javascript">

	$(function(){
		$("input[type='radio']").click(function(){
			var radionAll = $("input[type='radio']");
			var score = 0;
			for(var i =0;i<radionAll.length;i++)
			{
				if(radionAll[i].checked){
					score=score+parseInt(radionAll[i].value);
				}
			}

			var adlDetails ="";	
			if(score=="0"){
				adlDetails =score+" Low (patient very dependent)";
			} else if(score==6){
				 adlDetails=score+" High (patient independent)";
			} else {
				adlDetails=score;
			} 
			$("#adlStatus").html(adlDetails);
		});

		<?php 
		if($data[0]['unabletoevaluate'] == 'true'){
			?>
			$("fieldset:not(:first)").prop("disabled",true);
			$("fieldset:not(:first)").find("input").prop("checked",false);
			$("fieldset:not(:first)").find("label").css("cursor","not-allowed");

			<?php  
		}
		?>

		$('#unable_to_evaluate').on('change', function() {
			/*var val = this.checked ? 'true' : 'false';
			$('#unable_to_evaluate').val(val);*/
			
			if($(this).is(":checked")){
				$('#unable_to_evaluatehide').show();
				//$('#unable_to_evaluate_desc').val("");

				// disabling all of the input
				$("fieldset:not(:first)").prop("disabled",true);
				$("fieldset:not(:first)").find("input").prop("checked",false);
				$("fieldset:not(:first)").find("label").css("cursor","not-allowed");
			}else{
				$('#unable_to_evaluate_desc').val("");
				$('#unable_to_evaluatehide').hide();
				$("fieldset:not(:first)").prop("disabled",false);
				$("fieldset:not(:first)").find("label").css("cursor","auto");
			}
		});
	});

	function updatePoint(name,val){
		$('#'+name+'Point').html('Points: '+val);
	}

    </script>
    </head>

    <body class="body_top" <?php echo $body_javascript; ?>>	
        <div id="container_div">
        	<div class="row">
	            <div class="col-sm-12">
	                <!-- Required for the popup date selectors -->
	                <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>
	                <div class="clearfix">
						<h5><?php echo xlt('PHQ-9 Patient Depression Questionnaire'); ?></h5>
	                </div>
	            </div>
        	</div>
           	<form class="mt-3" id="phq9-form" method='post' action="<?php echo $rootdir ?>/forms/phq9/save.php" name='phq9'>
					<input type="hidden" name="pid" value="<?php echo $_SESSION["pid"]; ?>">
					<input type="hidden" name="encounter" value="<?php echo $_SESSION["encounter"]; ?>">
					<input type="hidden" name="evaluator" class="form-control" style="opacity:1" value="<?php echo $_SESSION['authUserID']; ?>">
					<input type="hidden" name="id" value="<?php echo $formid; ?>">

					<fieldset>
						<div class="form-row align-items">
							<div class="col-sm-2">
								<label class="radio-inline" >
									<input type="checkbox" id="unable_to_evaluate" name="unabletoevaluate" value="true" <?php if($data[0]['unabletoevaluate']=="true"){echo "checked";} ?>> <span style="font-size: medium;">Unable to Evaluate<!--  Due to Patient Cognitive Status and No Legally Authorized Representative Available --></span>
								</label>	
							</div>
							<div class="col-sm" id="unable_to_evaluatehide" style="<?php if($data[0]['unabletoevaluate']=="true"){echo 'display: ;'; }else{echo 'display:none;';}  ?> ">
								<input type="text" id="unable_to_evaluate_desc" style="opacity: 1;"  name="unable_to_evaluate_desc" size="70" value="<?php echo $data[0]['unable_to_evaluate_desc']; ?>" placeholder="Specify" />
							</div>
						</div>
					</fieldset>

					<fieldset>
						<legend><div class="form-row align-items">
							<div class="col-sm-4">
								<span><b style="font-size: medium;">Over the last 2 weeks, how often have you been bothered by any of the following problems?</b>
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<span><b style="font-size: medium;">Not at All - 0</b></span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span><b style="font-size: medium;">Several Days - 1</b></span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span><b style="font-size: medium;">More than half the days - 2</b></span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span><b style="font-size: medium;">Nearly every day - 3</b></span>
							</div>
						</div></legend>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">1) Little interest or pleasure in doing things
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="little_interest" value="0" <?php if($data[0]['little_interest']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="little_interest" value="1" <?php if($data[0]['little_interest']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="little_interest" value="2" <?php if($data[0]['little_interest']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="little_interest" value="3" <?php if($data[0]['little_interest']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">2) Feeling down, depressed, or hopeless
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_down" value="0" <?php if($data[0]['feeling_down']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_down" value="1" <?php if($data[0]['feeling_down']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_down" value="2" <?php if($data[0]['feeling_down']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_down" value="3" <?php if($data[0]['feeling_down']==3){echo "checked";} ?>>
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">3) Trouble falling or staying asleep, or sleeping too much
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="trouble_falling" value="0" <?php if($data[0]['trouble_falling']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="trouble_falling" value="1" <?php if($data[0]['trouble_falling']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="trouble_falling" value="2" <?php if($data[0]['trouble_falling']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="trouble_falling" value="3" <?php if($data[0]['trouble_falling']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">4) Feeling tired or having little energy
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_tired" value="0" <?php if($data[0]['feeling_tired']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_tired" value="1" <?php if($data[0]['feeling_tired']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_tired" value="2" <?php if($data[0]['feeling_tired']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_tired" value="3" <?php if($data[0]['feeling_tired']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">5) Poor appetite or overeating
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="overeating" value="0" <?php if($data[0]['overeating']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="overeating" value="1" <?php if($data[0]['overeating']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="overeating" value="2" <?php if($data[0]['overeating']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="overeating" value="3" <?php if($data[0]['overeating']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">6) Feeling bad about yourself  or that you are a failure or have let yourself or your family down
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_bad" value="0" <?php if($data[0]['feeling_bad']=="0"){echo "checked";} ?>>
									  
								</span>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_bad" value="1" <?php if($data[0]['feeling_bad']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_bad" value="2" <?php if($data[0]['feeling_bad']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="feeling_bad" value="3" <?php if($data[0]['feeling_bad']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">7) Trouble concentrating on things, such as reading the newspaper or watching television
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="television" value="0" <?php if($data[0]['television']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="television" value="1" <?php if($data[0]['television']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="television" value="2" <?php if($data[0]['television']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="television" value="3" <?php if($data[0]['television']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">8) Moving or speaking so slowly that other people could have noticed. Or the opposite being so fidgety or restless that you have been moving around a lot more than usual
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="restless" value="0" <?php if($data[0]['restless']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="restless" value="1" <?php if($data[0]['restless']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="restless" value="2" <?php if($data[0]['restless']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="restless" value="3" <?php if($data[0]['restless']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
								<span style="font-size: small;">9) Thoughts that you would be better off dead, or of hurting yourself
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="hurtingyourself" value="0" <?php if($data[0]['hurtingyourself']=="0"){echo "checked";} ?>>
									  
								</span>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="hurtingyourself" value="1" <?php if($data[0]['hurtingyourself']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="hurtingyourself" value="2" <?php if($data[0]['hurtingyourself']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="hurtingyourself" value="3" <?php if($data[0]['hurtingyourself']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>
						
					</fieldset>
					<fieldset>	
						<div class="form-row align-items">
							<div class="col-sm-4">
								<span style="font-size: small;">10) If you checked off any problems, how difficult have these problems made it for you to do your work, take care of things at home, or get along with other people?
								</span>			
							</div>
							<div class="col-sm" style="text-align: center;">
								<span style="font-size: small;">Not Difficult at all</span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span style="font-size: small;">Somewhat difficult</span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span style="font-size: small;">Very difficult</span>
							</div>
							<div class="col-sm" style="text-align: center;">
								<span style="font-size: small;">Extremely difficult</span>
							</div>
						</div>
						<div class="form-row align-items mb-3">
							<div class="col-sm-4">
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="problems_checked" value="0" <?php if($data[0]['problems_checked']=="0"){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="problems_checked" value="1" <?php if($data[0]['problems_checked']==1){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="problems_checked" value="2" <?php if($data[0]['problems_checked']==2){echo "checked";} ?>>
									  
								</label>	  
							</div>
							<div class="col-sm" style="text-align: center;">
								<label class="radio-inline">
									<input type="radio" name="problems_checked" value="3" <?php if($data[0]['problems_checked']==3){echo "checked";} ?>>
									  
								</label>	  
							</div>
						</div>	
					</fieldset>	
					<fieldset>	
							<div class="form-row align-items"><b style="font-size: medium;">For initial diagnosis:</b></div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">1. Patient completes PHQ-9 Quick Depression Assessment.</span>
								</label>
							</div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">2. If there are at least 4 selections in the shaded section (including Questions #1 and #2), consider a depressive disorder.</span>
								</label>
							</div>
							<div class="form-row align-items"><b style="font-size: medium;">Consider Major Depressive Disorder</b> </div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">			• if there are at least 5 selections in the shaded section (one of which corresponds to Question #1 or #2)</span>
								</label>
							</div>
							<!-- <div class="form-row align-items">
								<span style="font-size: small;">
								Note: Since the questionnaire relies on patient self-report, all responses should be verified by the clinician, and a definitive diagnosis is made on clinical grounds taking into account how well the patient understood the questionnaire, as well as other relevant information from the patient.  Diagnoses of Major Depressive Disorder or Other Depressive Disorder also require impairment of social, occupational, or other important areas of functioning (Question #10) and ruling out normal bereavement, a history of a Manic Episode (Bipolar Disorder), and a physical disorder, medication, or other drug as the biological cause of the depressive symptoms.
								</span>
							</div>-->
							<div class="form-row align-items"><b style="font-size: medium;">Consider Other Depressive Disorder</b> </div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">			• if there are 2-4 selections in the shaded section (one of which corresponds to Question #1 or #2)</span>
								</label>
							</div>
							<div class="form-row align-items">
								<span style="font-size: small;">
								Note: Since the questionnaire relies on patient self-report, all responses should be verified by the clinician, and a definitive diagnosis is made on clinical grounds taking into account how well the patient understood the questionnaire, as well as other relevant information from the patient.  Diagnoses of Major Depressive Disorder or Other Depressive Disorder also require impairment of social, occupational, or other important areas of functioning (Question #10) and ruling out normal bereavement, a history of a Manic Episode (Bipolar Disorder), and a physical disorder, medication, or other drug as the biological cause of the depressive symptoms.
								</span>
							</div>
							<div class="form-row align-items  mt-2"><b style="font-size: medium;">To monitor severity over time for newly diagnosed patients or patients in current treatment for depression:</b> </div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">1. Patients may complete questionnaires at baseline and at regular intervals (eg, every 2 weeks) at home and bring them in at their next appointment for scoring or they may complete the questionnaire during each scheduled appointment.</span>
								</label>
							</div>
							<div class="form-row align-items">
								<label class="radio-inline" ><span style="font-size: small;">2. Refer to the accompanying PHQ-9 Scoring Box to interpret the TOTAL score. 5. Results may be included in patient files to assist you in setting up a treatment goal, determining degree of response, as well as guiding treatment intervention.</span>
								</label>
							</div>
					</fieldset>	
					
				<div class="form-group clearfix">
					<div class="form-row align-items-center">
						<div class="col-sm"></div>
						<div class="col-sm">
							<button type="submit" onclick="top.restoreSession();" class="btn btn-primary btn-save btn-sm"><?php echo xlt('Save'); ?></button>
							<button type="button" class="btn btn-danger btn-cancel oe-opt-btn-separate-left btn-sm" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
						</div>
					</div>	
				</div>
			</form>

        </div>

    </body>

</html>