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
function affirm_sheet_report( $pid, $encounter, $cols, $id) {
    $count = 0;
/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_affirm_sheet';


/* an array of all of the fields' names and their types. */
$field_names = array('affirm' => 'textfield','exam_yeast' => 'dropdown_list','exam_gardnerrla' => 'dropdown_list','exam_trichomonas' => 'dropdown_list','lot_number' => 'textfield','exp_date' => 'textfield');/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'affirm' => 
   array( 'field_id' => 'affirm',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exam_yeast' => 
   array( 'field_id' => 'exam_yeast',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'exam_gardnerrla' => 
   array( 'field_id' => 'exam_gardnerrla',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'exam_trichomonas' => 
   array( 'field_id' => 'exam_trichomonas',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'lot_number' => 
   array( 'field_id' => 'lot_number',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exp_date' => 
   array( 'field_id' => 'exp_date',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
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

