<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormAssessmentForm.class.php");
$c = new C_FormAssessmentForm();
echo $c->default_action_process($_POST);
if(isset($_GET['print_assessment_form']) && $_GET['print_assessment_form'] == 1){
	
}else{
	
}
@formJump();
?>
