<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormPriorAuth.class.php");
$c = new C_FormPriorAuth();
echo $c->default_action_process($_POST);

global $pid, $encounter;
$owner = $_SESSION["authUser"];

//Added by Sherwin to link the to forms together to produce the desired result
//
//Copy this form date into the misc_billing_options table 
// in order to populate the x12 file with the proper data. 
//
/*
$sql = "SELECT date, prior_auth_number FROM form_prior_auth WHERE pid = $pid ORDER BY id DESC LIMIT 1 ";
$fetch = sqlStatement($sql);
$res = sqlFetchArray($fetch);

$date = $res['date'] ; 
$pa = $res['prior_auth_number'];
 
 $go_sql = "INSERT INTO `form_misc_billing_options` (`id`, `date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `employment_related`, `auto_accident`, `accident_state`, `other_accident`, `outside_lab`, `lab_amount`, `is_unable_to_work`, `date_initial_treatment`, `off_work_from`, `off_work_to`, `is_hospitalized`, `hospitalization_date_from`, `hospitalization_date_to`, `medicaid_resubmission_code`, `medicaid_original_reference`, `prior_auth_number`, `comments`, `replacement_claim`, `box_14_date_qual`, `box_15_date_qual`) VALUES ('', '$date', '$pid', '$owner', 'IBH', '0', '1', '0', '0', '', '0', '0', '0.00', '0', '0000-00-00', '0000-00-00', '0000-00-00', '0', '0000-00-00', '0000-00-00', '', '', '$pa', '', '0', '431', '454');";

 sqlStatement($go_sql);
 
 //Get the ID that was just saved
 $sql = "SELECT id FROM form_misc_billing_options WHERE date LIKE '$date' and pid = '$pid'";
 
 $fetch = sqlStatement($sql);
 $res = sqlFetchArray($fetch);
 $f_id = $res['id'];
 
 $form = "INSERT INTO `forms` (`id`, `date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`) VALUES (NULL, '$date', '$encounter', 'Misc Billing Options', '$f_id', '$pid', '$owner', 'IBH', '1', '0', 'misc_billing_options')";
 
 //Insert Misc Billing form info into table to similate filling out form
 sqlStatement($form);
 
 
 //file_put_contents("sql.txt", $txt);  //troubleshooting
*/

@formJump();
?>
