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
function pelvic_soap_report( $pid, $encounter, $cols, $id) {
    $count = 0;
/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_pelvic_soap';


/* an array of all of the fields' names and their types. */
$field_names = array('pelvic_complaints' => 'checkbox_combo_list','pelvic_exam' => 'checkbox_combo_list','pelvic_assessment' => 'checkbox_combo_list','pelvic_plan' => 'checkbox_combo_list','plan_discussion' => 'textarea');/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'pelvic_complaints' => 
   array( 'field_id' => 'pelvic_complaints',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Complaints' ),
 'pelvic_exam' => 
   array( 'field_id' => 'pelvic_exam',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Exam' ),
 'pelvic_assessment' => 
   array( 'field_id' => 'pelvic_assessment',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Assessment' ),
 'pelvic_plan' => 
   array( 'field_id' => 'pelvic_plan',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Plan' ),
 'plan_discussion' => 
   array( 'field_id' => 'plan_discussion',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' )
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

