<?php
/**
 * View snapshot details of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
    require_once("../../../globals.php");
    require_once("$srcdir/options.inc.php");
    $ss_csrf_token = collectCsrfToken();
if (!verifyCsrfToken($_GET["csrf_token_form"])) {
    csrfNotVerified();
}
if (isset($_GET['ss_date'])) {
    $ss_date = $_GET['ss_date'];
    $ss_pid = $_GET['pid'];
    $ss_author = $_GET['authid'];
    $ss_snapshot = $_GET['snapshot'];
    if (strpos($ss_snapshot, "~") > 0) {
        $array_snapshots = explode('~', $ss_snapshot);
        $array_show_div = $array_snapshots; // to be used in jQuery to show relevant divs
        $snapshot_elem = count($array_snapshots);
        $array_query = trim(str_repeat("? ", $snapshot_elem));// trims last blank space
        $array_query = str_replace(" ", ",", $array_query);// to get the correct number of question marks for the array
        array_push($array_snapshots, $ss_date, $ss_pid, 1); // to get he correct number and values for the parametets in the array
    } else {
        $array_snapshots = array();
        array_push($array_snapshots, $ss_snapshot, $ss_date, $ss_pid, 1);
        $array_show_div = array($ss_snapshot);// to be used in jQuery to show relevant divs
        $array_query = "?";
    }
    $sql = "SELECT
                    pbd.id,
                    CASE
						WHEN STRCMP(pbd.loinc_ans_code, 'SCORE') = 0 THEN pbd.loinc_ans_value
						WHEN STRCMP(pbd.loinc_ans_code, 'OEA') = 0 THEN pbd.loinc_ans_value
						ELSE lo.title
					END AS input_value,
                    pbd.loinc_ans_code,
                    pbd.loinc_ans_value,
                    pbc.form_link
                FROM
                    psychosocial_behavior_data AS pbd
                INNER JOIN psychosocial_behavior_codes AS pbc
                ON
                    pbd.loinc_que_code = pbc.loinc_que_code
                LEFT OUTER JOIN list_options AS lo
                ON
                    pbd.loinc_ans_code = lo.codes
                WHERE
                    SUBSTRING(pbc.form_link, 1, 5) IN($array_query) AND
                    pbd.date = ? AND pbd.pid = ? AND pbd.active = ?";
                
    $values = sqlStatement($sql, $array_snapshots);
            
    if (sqlNumRows($values)) {
        $numrows = sqlNumRows($values);
                                
        while ($value = sqlFetchArray($values)) {
            $value_array [$value['form_link']] = $value['input_value'];
        }
    }
} else {
    $ss_date = null;
}
    
?>
<script>
    $(document).ready(function (){
    
        $("#edit_button").on('click', function() {
            
            var editTitle = '<i class="fa fa-pencil" style="width:20px;" aria-hidden="true"></i> ' + '<?php echo xlt("Edit Mode"); ?> ';
            url = 'snapshot_1/snapshot_detail_1_add.php?type=edit&pid=<?php echo attr($ss_pid);?>&snapshot=<?php echo attr($ss_snapshot);?>&author=<?php echo attr($ss_author);?>&ss_date=<?php echo attr($ss_date);?>&csrf_token_form=' + <?php echo js_url($ss_csrf_token); ?>;
        
            dlgopen(url, '_blank', 800, 750, false, editTitle);
            
        }); 
        
        $("#add_button").on('click', function() {
            var addTitle = '<i class="fa fa-pencil" style="width:20px;" aria-hidden="true"></i> ' + '<?php echo xlt("Add Mode"); ?> ';
            url = 'snapshot_1/snapshot_detail_1_add.php?type=add&pid=<?php echo attr($ss_pid);?>&snapshot=<?php echo attr($ss_snapshot);?>&author=<?php echo attr($ss_author);?>&csrf_token_form=' + <?php echo js_url($ss_csrf_token); ?>;
            dlgopen(url, '_blank', 800, 750, false, addTitle);
            setTimeout(function(){
                $('#refresh_div').removeClass('hide_div');
                $('#view-dates').removeClass('hide_div'); 
            }, 1000);
            
        });
    });
  
</script>
<form name="psychosocial-behavior" id="psychosocial-behavior" action="snapshot_detail_1.php" method="get">
    <div class="btn-group">
        <button type="button" class="btn btn-default btn-add" name='add_btn' id='add_button' ><?php echo xlt('Add');?></button>
        <?php
        if ($ss_date != 'undefined') { ?>
        <button type="button" class="btn btn-default btn-edit" name='edit_btn' id='edit_button' ><?php echo xlt('Edit');?></button>
        <?php
        } ?>
    </div>
    <br>
    <br>
    <div name='snapshot_details_div' id='snapshot_details_div' class='hide_div'>
        <fieldset class='hide_div' id='finan' name='finan'>
            <legend> <?php echo xlt("Financial resource strain") . " - " . $ss_date;?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("How hard is it for you to pay for the very basics like food, housing, medical care, and heating"); ?>?
                <input type='hidden' name='financial-q' id='financial-q' value='76513-1'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'financial';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='financial_a' id='financial_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='educa' name='educa'>
            <legend> <?php echo xlt("Education") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("What is the highest grade or level of school you have completed or the highest degree you have received"); ?>?
                <input type='hidden' name='education_q' id='education_q' value='63504-5'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'education';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='education_a' id='education_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='stres' name='stres'>
            <legend> <?php echo xlt("Stress") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("These days do you feel stress - tense, restless, nervous, or anxious, or 
                unable to sleep at night because your mind is troubled all the time");
                ?>?
                <input type='hidden' name='stress_q' id='stress_q' value='76542-0'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'stress';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='stress_a' id='stress_a' class='form-control'  readonly='readonly' style='cursor: no-drop;'
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='depre' name='depre'>
            <legend> <?php echo xlt("Depression") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("Little interest or pleasure in doing things in last 2 week"); ?>?
                <input type='hidden' name='depression_1_q' id='depression_1_q' value='44250-9'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'depression_1';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='depression_1_a' id='depression_1_a' class='form-control'  readonly='readonly' style='cursor: no-drop;'
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("Feeling down, depressed, or hopeless in last 2 weeks"); ?>?
                <input type='hidden' name='depression_2_q' id='depression_2_q' value='44255-8'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'depression_2';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='depression_2_a' id='depression_2_a' class='form-control'  readonly='readonly' style='cursor: no-drop;'
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class='clearfix'>
                <div class="col-sm-8">
                    <p> <?php echo xlt("Total score reported"); ?>: <a href="#depr_score_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" > <i class="fa fa-question-circle text-primary" aria-hidden="true"></i></a>
                    <input type='hidden' name='depression_score_q' id='depression_score_q' value='55758-7'>
                </div>
                <div class="col-sm-2">
                    <?php
                        $key_name = 'depression_score';
                        $score_val = null;
                    foreach ($value_array as $key => $val) {
                        if ($key == $key_name) {
                            $score_val = $val;
                            break;
                        }
                    }
                    ?>
                    <input type='text' name='depression_score' id='depression_score' class='form-control'  readonly='readonly' style='cursor: no-drop;'
                     title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
                </div>
            </div>
            <div id="depr_score_info" class="collapse">
                <a href="#depr_score_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                <p><?php echo xlt("The Patient Health Questionnaire (PHQ) is a diagnostic tool for mental health disorders used by health care professionals that is quick and easy for patients to complete");?>
                <p><?php echo xlt("The two questions are used as a screener for depression and are referred to as PHQ-2");?>
                <p><?php echo xlt("A positive to either question indicates the need for further testing");?>
                <p><?php echo xlt("The recommended cutpoint is a score of 3 or greater, which should require further testing");?></p>
                <p><?php echo xlt("More information is available by following this link");?>  <a href='https://www.phqscreeners.com/select-screener/36' class='oe-text-black' rel="noopener" target='_blank'><i class="fa fa-external-link" aria-hidden="true"></i></a></p>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='physi' name='physi'>
            <legend> <?php echo xlt("Physical activity") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("How many days of moderate to strenuous exercise, like a brisk walk, did you do in the last 7 days"); ?>?
                <input type='hidden' name='physical_activity_1_q' id='physical_activity_1_q' value='68515-6'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'physical_activity_1';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='physical_activity_1_a' id='physical_activity_1_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("On those days that you engage in moderate to strenuous exercise, how many minutes, on average, do you exercise"); ?>?
                <input type='hidden' name='physical_activity_2_q' id='physical_activity_2_q' value='68516-4'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'physical_activity_2';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='physical_activity_2_a' id='physical_activity_2_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='alcoh' name='alcoh'>
            <legend> <?php echo xlt("Alcohol Use Disorder Identification Test") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("How often do you have a drink containing alcohol"); ?>?
                <input type='hidden' name='alcohol_1_q' id='alcohol_1_q' value='68518-0'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'alcohol_1';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='alcohol_1_a' id='alcohol_1_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("How many standard drinks containing alcohol do you have on a typical day"); ?>?
                <input type='hidden' name='alcohol_2_q' id='alcohol_2_q' value='68519-8'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'alcohol_2';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='alcohol_2_a' id='alcohol_2_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("How often do you have 6 or more drinks on 1 occasion"); ?>?
                <input type='hidden' name='alcohol_3_q' id='alcohol_3_q' value='68520-6'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'alcohol_3';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='alcohol_3_a' id='alcohol_3_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class='clearfix'>
                <div class="col-sm-8">
                    <p> <?php echo xlt("Total score"); ?>:  <a href="#alco_score_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" > <i class="fa fa-question-circle text-primary" aria-hidden="true"></i></a>
                    <input type='hidden' name='alcohol_score_q' id='alcohol_score_q' value='75626-2'>
                </div>
                <div class="col-sm-2">
                    <?php
                        $key_name = 'alcohol_score';
                        $score_val = null;
                    foreach ($value_array as $key => $val) {
                        if ($key == $key_name) {
                            $score_val = $val;
                            break;
                        }
                    }
                    ?>
                    <input type='text' name='alcohol_score' id='alcohol_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                     title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
                </div>
            </div><div id="alco_score_info" class="collapse">
                <a href="#alco_score_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                <p><?php echo xlt("The Alcohol Use Disorders Identification Test C (AUDIT-C) is scored on a scale of 0-12 where the higher the score, the more likely the patient's drinking is hazardous");?>
                <p><?php echo xlt("A score of 4 or more for men and 3 or more for women is considered positive for hazardous drinking or active alcohol use disorders");?>
                <p><?php echo xlt("If the points are all from Question 1 alone where 2 and 3 are 0, it is likely the patient is drinking below recommended limits");?>
                <p><?php echo xlt("The care provider may review the patients alcohol intake over that past few months to confirm accuracy");?></p>
                <p><?php echo xlt("More information is available by following this link");?>  <a href='https://www.ncbi.nlm.nih.gov/pubmed/12695273' class='oe-text-black' rel="noopener" target='_blank'><i class="fa fa-external-link" aria-hidden="true"></i></a></p>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='socia' name='socia'>
            <legend> <?php echo xlt("Social connection and isolation panel") . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("Are you now married, widowed, divorced, separated, never married or living with a partner"); ?>?
                <input type='hidden' name='social_1_q' id='social_1_q' value='63503-7'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'social_1';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='social_1_a' id='social_1_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("In a typical week, how many times do you talk on the telephone with family, friends, or neighbors"); ?>?
                <input type='hidden' name='social_2_q' id='social_2_q' value='76508-1'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'social_2';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='social_2_a' id='social_2_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("How often do you get together with friends or relatives"); ?>?
                <input type='hidden' name='social_3_q' id='social_3_q' value='76509-9'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'social_3';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='social_3_a' id='social_3_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("How often do you attend church or religious services per year"); ?>?
                <input type='hidden' name='social_4_q' id='social_4_q' value='76510-7'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'social_4';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='social_4_a' id='social_4_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("Do you belong to any clubs or organizations such as church groups unions, fraternal or athletic groups, or school groups"); ?>?
                <input type='hidden' name='social_5_q' id='social_5_q' value='76511-5'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'social_5';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='social_5_a' id='social_5_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class='clearfix'>
                <div class="col-sm-8">
                    <p> <?php echo xlt("Social isolation score"); ?>: <a href="#soci_score_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" > <i class="fa fa-question-circle text-primary" aria-hidden="true"></i></a>
                    <input type='hidden' name='social_score_q' id='social_score_q' value='76512-3'>
                </div>
                <div class="col-sm-2">
                    <?php
                        $key_name = 'social_score';
                        $score_val = null;
                    foreach ($value_array as $key => $val) {
                        if ($key == $key_name) {
                            $score_val = $val;
                            break;
                        }
                    }
                    ?>
                    <input type='text' name='social_score' id='social_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                     title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
                </div>
            </div>
            <div id="soci_score_info" class="collapse">
                <a href="#soci_score_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                <p><?php echo xlt("Social isolation scores range from 0 to 4, with 0 representing the highest level of social isolation and 4 representing the lowest level");?>
                <p><?php echo xlt("1 point is awarded for each of the following: being married or living together with someone in a partnership at the time of questioning, averaging 3 or more interactions per week with other people , attending church or religious services 4 or more times per year, and reporting that they participate in a club or organization such as a church group, union, fraternal or athletic group, or school group");?>
                <p><?php echo xlt("Scores of 0 or 1 are the most socially isolated participants");?>
                <p><?php echo xlt("Socially isolated men and women have worse unadjusted survival curves than less socially isolated individuals");?>
                <p><?php echo xlt("More information is available by following this link");?>  <a href='https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3871270/' class='oe-text-black' rel="noopener" target='_blank'><i class="fa fa-external-link" aria-hidden="true"></i></a></p>
            </div>
        </fieldset>
        <fieldset class='hide_div' id='viole' name='viole'>
            <legend> <?php echo xlt("Exposure to violence") . " (" . xlt("intimate partner violence") . ")" . " - " . $ss_date;
            ; ?></legend>
            <div class="col-sm-8">
                <p> <?php echo xlt("Within the last year, have you been humiliated or emotionally abused in other ways by your partner or ex-partner"); ?>?
                <input type='hidden' name='violence_1_q' id='violence_1_q' value='76500-8'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'violence_1';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='violence_1_a' id='violence_1_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("Within the last year, have you been afraid of your partner or ex-partner"); ?>?
                <input type='hidden' name='violence_2_q' id='violence_2_q' value='76501-6'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'violence_2';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='violence_2_a' id='violence_2_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("Within the last year, have you been raped or forced to have any kind of sexual activity by your partner or ex-partner"); ?>?
                <input type='hidden' name='violence_3_q' id='violence_3_q' value='76502-4'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'violence_3';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='violence_3_a' id='violence_3_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class="col-sm-8">
                <p> <?php echo xlt("Within the last year, have you been kicked, hit, slapped, or otherwise physically hurt by your partner or ex-partner"); ?>?
                <input type='hidden' name='violence_4_q' id='violence_4_q' value='76503-2'>
            </div>
            <div class="col-sm-2">
                <?php
                    $key_name = 'violence_4';
                    $score_val = null;
                foreach ($value_array as $key => $val) {
                    if ($key == $key_name) {
                        $score_val = $val;
                        break;
                    }
                }
                ?>
                <input type='text' name='violence_4_a' id='violence_4_a' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                 title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
            </div>
            <div class='clearfix'>
                <div class="col-sm-8">
                    <p> <?php echo xlt("Total score"); ?>: <a href="#viol_score_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" > <i class="fa fa-question-circle text-primary" aria-hidden="true"></i></a>
                    <input type='hidden' name='violence_score_q' id='violence_score_q' value='76504-0'>
                </div>
                <div class="col-sm-2">
                    <?php
                        $key_name = 'violence_score';
                        $score_val = null;
                    foreach ($value_array as $key => $val) {
                        if ($key == $key_name) {
                            $score_val = $val;
                            break;
                        }
                    }
                    ?>
                    <input type='text' name='violence_score' id='violence_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                     title='<?php echo xla('Click Edit button to change value'); ?>' placeholder='<?php echo xla("No current value");?>' value='<?php echo $score_val;?>'>
                </div>
            </div>
            <div id="viol_score_info" class="collapse">
                <a href="#viol_score_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                <p><?php echo xlt("Also known as the Humiliation, Afraid, Rape, and Kick questionnaire [HARK]");?>
                <p><?php echo xlt("Each HARK question is equal to 1 point if the patient answers 'yes' with a total score range from 0 to 4");?>
                <p><?php echo xlt("A HARK score of ≥1 indicates the patient is affected by interpersonal violence (IPV)");?>
                <p><?php echo xlt("Intimate partner violence causes short and long term ill-health");?>
                <p><?php echo xlt("More information is available by following this link");?>  <a href='https://www.ncbi.nlm.nih.gov/pmc/articles/PMC2034562/' class='oe-text-black' rel="noopener" target='_blank'><i class="fa fa-external-link" aria-hidden="true"></i></a></p>
            </div>
        </fieldset>
    </div>
</form>
<script>
$(document).ready(function(){
        var arrShowDiv = <?php echo json_encode($array_show_div); ?>;
        $.each(arrShowDiv, function(index, chunk) {
            $('#' + chunk).removeClass('hide_div');
        })
    });
</script>
<script>
$(document).ready(function (){
    var ssDate = <?php echo $ss_date; ?>;
    if (ssDate){
        $('#snapshot_details_div').removeClass('hide_div');
    }
    
});
</script>
<script>
    //jqury-ui tooltip
    $(document).ready(function() {
        //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
        // the relevant css file needs to be called i.e. jquery-ui-darkness - to get a black tooltip
        $('.icon-tooltip').attr("title", "<?php echo xla('Click to see more information'); ?>").tooltip({
            show: {
                delay: 700,
                duration: 0
            }
        });
        $('#enter-details-tooltip').attr( "title", "<?php echo xla('Additional help to fill out this form is available by hovering over labels of each box and clicking on the dark blue help ? icon that is revealed'); ?>" + ". " + "<?php echo xla('On mobile devices tap once on the label to reveal the help icon and tap on the icon to show the help section'); ?>.").tooltip();
    });
</script>