<?php
/*
 * this file's contents are included in both the encounter page as a 'quick summary' of a form, and in the medical records' reports page.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');

/* The name of the function is significant and must match the folder name */
function gyn_ros_report( $pid, $encounter, $cols, $id) {
    $count = 0;
/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_gyn_ros';


/* an array of all of the fields' names and their types. */
$field_names = array('cardio_reviewed' => 'checkbox_list',
	'cardio_note' => 'textarea',
	'gastro_reviewed' => 'checkbox_list',
	'gastro_note' => 'textarea',
	'Urinary_reviewed' => 'checkbox_list',
	'Urinary_note' => 'textarea',
	'cns_reviewed' => 'checkbox_list',
	'cns_note' => 'textarea',
	'other_reviewed' => 'checkbox_list',
	'other_note' => 'textarea',
	'complications_reviewed' => 'checkbox_list',
	'complications_note' => 'textarea',
	'lmpdate' => 'date',
	'cycle_int' => 'dropdown_list',
	'cycle_int_note' => 'textarea',
	'flowfhcount' => 'textfield',
	'flowhrs' => 'textfield',
	'pmb' => 'checkbox_list',
	'vag_discharge' => 'checkbox_list',
	'vag_discharge_note' => 'textarea',
	'vag_itching' => 'checkbox_list',
	'vag_itching_note' => 'textarea',
	'vag_odor' => 'checkbox_list',
	'vag_odor_note' => 'textarea',
	'vag_irratation' => 'checkbox_list',
	'vag_irratation_note' => 'textarea',
	'vag_spotting' => 'checkbox_list',
	'vag_spotting_note' => 'textarea',
	'priortreatment' => 'checkbox_list',
	'priortreatment_note' => 'textarea',
	'pain_menses' => 'dropdown_list',
	'pain_level' => 'textfield',
	'pain_location' => 'dropdown_list',
	'pain_lenth' => 'textfield',
	'pain_drug_resp' => 'textfield',
	'pain_intercourse' => 'checkbox_list',
	'pain_intercourse_time' => 'textarea');

/* an array of the lists the fields may draw on. */
$lists = array("menses_pain","menses_cycle","menses_pain_location");
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
            
            // print $field_names[$key] . '=' . $value . "<br />\n";  //debug

            // Lists
            if ($key == 'cycle_int') {
                if ($value != '') {
                    $value = get_list_options('menses_cycle', $value);
                }
            }

            if ($key == 'pain_menses') {
                if ($value != '') {
                    $value = get_list_options('menses_pain', $value);
                }
            }

            if ($key == 'pain_location') {
                if ($value != '') {
                    $value = get_list_options('menses_pain_location', $value);
                }
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

// Get list Options
function get_list_options($list_id='',$option_id='',$field='title')
{

//      @TODO this is not working for some reason
//        $row = sqlQuery("SELECT ? FROM list_options " .
//          "WHERE list_id = ? AND option_id = ?", array($field,$list_id,$option_id) );

     $row = sqlQuery("SELECT title FROM list_options " .
           "WHERE list_id = '$list_id' AND option_id = '$option_id'");

    // print var_dump($row[$field]) . "<br/>"; //debug

    return($row[$field]);
}

?>

