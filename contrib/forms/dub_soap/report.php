<?php
/*
 * this file's contents are included in both the encounter page as a 'quick summary' of a form, and in the medical records' reports page.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');
/* The name of the function is significant and must match the folder name */
function dub_soap_report( $pid, $encounter, $cols, $id) {
    $count = 0;
/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_dub_soap';


/* an array of all of the fields' names and their types. */
$field_names = array('menstrual_history' => 'checkbox_combo_list','previous_sonogram' => 'textfield','previous_blood_work' => 'textfield','previous_needs_rx' => 'textfield','other' => 'textarea','dub_objective' => 'checkbox_combo_list','a_dub' => 'checkbox_combo_list','plan_lab_work' => 'checkbox_combo_list','plan_tests_procedures' => 'checkbox_combo_list','plan_medications' => 'checkbox_combo_list');/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'menstrual_history' => 
   array( 'field_id' => 'menstrual_history',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Menstrual_History' ),
 'previous_sonogram' => 
   array( 'field_id' => 'previous_sonogram',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'previous_blood_work' => 
   array( 'field_id' => 'previous_blood_work',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'previous_needs_rx' => 
   array( 'field_id' => 'previous_needs_rx',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'other' => 
   array( 'field_id' => 'other',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' ),
 'dub_objective' => 
   array( 'field_id' => 'dub_objective',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Objective' ),
 'a_dub' => 
   array( 'field_id' => 'a_dub',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Diagnosis' ),
 'plan_lab_work' => 
   array( 'field_id' => 'plan_lab_work',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Lab_Work' ),
 'plan_tests_procedures' => 
   array( 'field_id' => 'plan_tests_procedures',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Tests_Procedures' ),
 'plan_medications' => 
   array( 'field_id' => 'plan_medications',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'DUB_Medications' )
 );
/* an array of the lists the fields may draw on. */
$lists = array();
    $data = formFetch($table_name, $id);
    if ($data) {

        echo '<table><tr>';

        foreach($data as $key => $value) {
            if ($key == 'id' || $key == 'pid' || $key == 'user' ||
                $key == 'groupname' || $key == 'authorized' ||
                $key == 'activity' || $key == 'date' || 
                $value == '' || $value == '0000-00-00 00:00:00' ||
                $value == 'n')
            {
                /* skip built-in fields and "blank data". */
	        continue;
            }

            /* display 'yes' instead of 'on'. */
            if ($value == 'on') {
                $value = 'yes';
            }

            /* remove the time-of-day from the 'date' fields. */
            if ($field_names[$key] == 'date')
            if ($value != '') {
              $dateparts = split(' ', $value);
              $value = $dateparts[0];
            }
            
            if ( $field_names[$key] == 'checkbox_combo_list' ) {
                $value = generate_display_field( $manual_layouts[$key], $value );
            }

            /* replace underscores with spaces, and uppercase all words. */
            /* this is a primitive form of converting the column names into something displayable. */
            $key=ucwords(str_replace('_',' ',$key));
            $mykey = $key;
            $myval = $value;
            echo '<td><span class=bold>'.xl("$mykey").': </span><span class=text>'.xl("$myval").'</span></td>';


            $count++;
            if ($count == $cols) {
                $count = 0;
                echo '</tr><tr>' . PHP_EOL;
            }
        }
    }
    echo '</tr></table><hr>';
}
?>

