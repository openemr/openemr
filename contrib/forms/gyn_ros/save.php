<?php
/* this page is intended to be the 'action=' target of a form object.
 * it is called to save the contents of the form into the database
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for ??? */
require_once($GLOBALS['srcdir'].'/forms.inc');
/* for formDataCore() */
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_gyn_ros';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Gyn Review of Systems';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'gyn_ros';

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
 die($form_name.': Access Denied.');
}
/* perform a squad check for pages touching patients, if we're in 'athletic team' mode */
if ($GLOBALS['athletic_team']!='false') {
  $tmp = getPatientData($pid, 'squad');
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}

/* an array of all of the fields' names and their types. */
$field_names = array('cardio_reviewed' => 'checkbox_list','cardio_note' => 'textarea','gastro_reviewed' => 'checkbox_list','gastro_note' => 'textarea','Urinary_reviewed' => 'checkbox_list','Urinary_note' => 'textarea','cns_reviewed' => 'checkbox_list','cns_note' => 'textarea','other_reviewed' => 'checkbox_list','other_note' => 'textarea','complications_reviewed' => 'checkbox_list','complications_note' => 'textarea','lmpdate' => 'date','cycle_int' => 'dropdown_list','cycle_int_note' => 'textarea','flowfhcount' => 'textfield','flowhrs' => 'textfield','pmb' => 'checkbox_list','vag_discharge' => 'checkbox_list','vag_discharge_note' => 'textarea','vag_itching' => 'checkbox_list','vag_itching_note' => 'textarea','vag_odor' => 'checkbox_list','vag_odor_note' => 'textarea','vag_irratation' => 'checkbox_list','vag_irratation_note' => 'textarea','vag_spotting' => 'checkbox_list','vag_spotting_note' => 'textarea','priortreatment' => 'checkbox_list','priortreatment_note' => 'textarea','pain_menses' => 'dropdown_list','pain_level' => 'textfield','pain_location' => 'dropdown_list','pain_lenth' => 'textfield','pain_drug_resp' => 'textfield','pain_intercourse' => 'checkbox_list','pain_intercourse_time' => 'textarea');
/* an array of the lists the fields may draw on. */
$lists = array('cardio_reviewed' => 'yesno', 'gastro_reviewed' => 'yesno', 'Urinary_reviewed' => 'yesno', 'cns_reviewed' => 'yesno', 'other_reviewed' => 'yesno', 'complications_reviewed' => 'yesno', 'cycle_int' => 'menses_cycle', 'pmb' => 'yesno', 'vag_discharge' => 'yesno', 'vag_itching' => 'yesno', 'vag_odor' => 'yesno', 'vag_irratation' => 'yesno', 'vag_spotting' => 'yesno', 'priortreatment' => 'yesno', 'pain_menses' => 'menses_pain', 'pain_location' => 'menses_pain_location', 'pain_intercourse' => 'yesno');

/* get each field from $_POST[], storing them into $field_names associated with their names. */
foreach($field_names as $key=>$val)
{
    $pos = '';
    $neg = '';
    if ($val == 'textbox' || $val == 'textarea' || $val == 'provider' || $val == 'textfield')
    {
            $field_names[$key]=$_POST['form_'.$key];
    }
    if ($val == 'date')
    {
        $field_names[$key]=$_POST[$key];
    }
    if (($val == 'checkbox_list'))
    {
        $field_names[$key]='';
        if (isset($_POST['form_'.$key]) && $_POST['form_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
            $lres=sqlStatement("select * from list_options where list_id = '".$lists[$key]."' ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres))
            {
                if (is_array($_POST['form_'.$key]))
                    {
                        if ($_POST['form_'.$key][$lrow[option_id]])
                        {
                            if ($field_names[$key] != '')
                              $field_names[$key]=$field_names[$key].'|';
	                    $field_names[$key] = $field_names[$key].$lrow[option_id];
                        }
                    }
            }
        }
    }
    if (($val == 'dropdown_list'))
    {
        $field_names[$key]='';
        if (isset($_POST['form_'.$key]) && $_POST['form_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
            $lres=sqlStatement("select * from list_options where list_id = '".$lists[$key]."' ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres))
            {
                if ($_POST['form_'.$key] == $lrow[option_id])
                {
                    $field_names[$key]=$lrow[option_id];
                    break;
                }
            }
        }
    }
}

/* at this point, field_names[] contains an array of name->value pairs of the fields we expected from the form. */

/* escape form data for entry to the database. */
foreach ($field_names as $k => $var) {
  $field_names[$k] = formDataCore($var);
}

if ($encounter == '') $encounter = date('Ymd');

if ($_GET['mode'] == 'new') {
    /* NOTE - for customization you can replace $_POST with your own array
     * of key=>value pairs where 'key' is the table field name and
     * 'value' is whatever it should be set to
     * ex)   $newrecord['parent_sig'] = $_POST['sig'];
     *       $newid = formSubmit($table_name, $newrecord, $_GET['id'], $userauthorized);
     */

    /* make sure we're at the beginning of the array */
    reset($field_names);

    /* save the data into the form's encounter-based table */
    $newid = formSubmit($table_name, $field_names, $_GET['id'], $userauthorized);
    /* link this form into the encounter. */
    addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
}

elseif ($_GET['mode'] == 'update') {
    /* make sure we're at the beginning of the array */
    reset($field_names);

    /* update the data in the form's table */
    $success = formUpdate($table_name, $field_names, $_GET['id'], $userauthorized);
    /* sqlInsert('update '.$table_name." set pid = {".$_SESSION['pid']."},groupname='".$_SESSION['authProvider']."',user='".$_SESSION['authUser']."',authorized=$userauthorized,activity=1,date = NOW(), where id=$id"); */
}


$_SESSION['encounter'] = $encounter;

formHeader('Redirecting....');
/* defaults to the encounters page. */
formJump();

formFooter();
?>

