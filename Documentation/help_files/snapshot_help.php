<?php
/**
 * Message Center Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @version 1.0.0
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 
use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Snapshot Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <center><h2><a name='entire_doc'><?php echo xlt("Snapshot Help");?></a></h2></center>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Patient related clinical data is ideally stored as structured data in an electronic medical record in order for easy retrieval and analysis");?>.
                    
                    <p><?php echo xlt("In OpenEMR patient data can be encounter based or non encounter based");?>.
                    
                    <p><?php echo xlt("The encounter based data is generally used to document ongoing medical problems");?>.
                    
                    <p><?php echo xlt("Certain data like personal history, family history are more static and are non encounter based");?>.
                    
                    <p><?php echo xlt("In certain situations where data changes but not that often either of the two current approaches is not adequate");?>.
                    
                    <p><?php echo xlt("The snapshot feature is designed to address this issue. It aims to capture the data at a particular moment and keep it as structured data that is easily accessible");?>.
                    
                    <p><?php echo xlt("This feature as currently implemented is used to track 8 psychosocial and behavioral issues or domains");?>.
                    
                    <p><?php echo xlt("They are - Financial resource strain, Education, Stress, Depression, Physical activity, Alcohol use, Social connection and isolation and Exposure to violence - intimate partner violence");?>.
                    
                    <p><?php echo xlt("These eight domains together provide a comprehensive picture of the patient that can facilitate care management and coordination");?>.
                    
                    <p><?php echo xlt("It also fulfills one of the requirements for 2015 Edition Health Information Technology (Health IT) Certification Criteria");?>.
                    
                    <p><?php echo xlt("Clicking on the Snapshot tab in the Medical Dashboard will open the Snapshot landing page");?>.
                    
                    <p><?php echo xlt("Select a snapshot to load");?>.
                    
                    <p><?php echo xlt("If there are any previous snapshots they are loaded up automatically in descending order of dates");?>.
                    
                    <p><?php echo xlt("If there are none an Add button will become visible that will let you create the initial snapshot");?>.
                    <button type="button" class="btn btn-default btn-add btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("This feature is designed to allow creating of a single snapshot of any particular type for the current day");?>.</strong>
                    
                    <p><?php echo xlt("You can create a snapshot of each type for that particular day");?>.
                    
                    <p><?php echo xlt("After creating the initial snapshot click the Refresh button twice to update the display");?>.
                    <button type="button" class="btn btn-default btn-refresh btn-sm oe-no-float"><?php echo xlt("Refresh"); ?></button>
                    
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("Be aware that the refresh button will also refresh the date drop-down box to reflect the latest date");?>.
                    
                    <p><?php echo xlt("Previous snapshots can be edited by clicking the Edit button");?>.
                    <button type="button" class="btn btn-default btn-edit btn-sm oe-no-float"><?php echo xlt("Edit"); ?></button>
                    
                    <p><?php echo xlt("After editing the snapshot click on the Refresh button once");?>.
                    <button type="button" class="btn btn-default btn-refresh btn-sm oe-no-float"><?php echo xlt("Refresh"); ?></button>
                    
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("Users with administrator privileges can delete entries by clicking the Delete button in the Edit mode");?>.
                    <button type="button" class="btn btn-default btn-delete btn-sm oe-no-float"><?php echo xlt("Delete"); ?></button>
                    
                    <p><?php echo xlt("There are 5 snapshots to choose from");?>.
                    
                    <ul id="top_section">
                        <li><a href="#section1"><?php echo xlt("Financial and Education Snapshot");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Physical Activity and Alcohol Snapshot");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Stress and Depression Snapshot");?></a></li>
                        <li><a href="#section4"><?php echo xlt("Social Connection and Isolation Snapshot");?></a></li>
                        <li><a href="#section5"><?php echo xlt("Exposure to violence - HARK Snapshot");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Financial and Education Snapshot"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("A single-item question is used to determine the patient's overall financial resource strain");?>.
                    
                    <p><?php echo xlt("Patients are asked to select one answer - very hard, hard, somewhat hard, or not very hard");?>.
                    
                    <p><?php echo xlt("The Coronary Artery Risk Development in Young Adults (CARDIA) study found the single-item question to be a valid measure of general financial resource strain");?>.
                    
                    <p><?php echo xlt("Evidence from the CARDIA study demonstrated the value of measuring the difficulty of paying for basics over time, because there appeared to be a cumulative effect e.g. incident hypertension");?>.
                    
                    <p><?php echo xlt("Education level  indicates the maximum level of education achieved");?>.
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Physical Activity and Alcohol Snapshot"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><strong><?php echo xlt("PHYSICAL ACTIVITY"); ?> :</strong>
                    
                    <p><?php echo xlt("Energy expenditure is expressed by multiples of the metabolic equivalent of task - MET, where 1 MET is the rate of energy expenditure while sitting at rest");?>.
                    
                    <p><?php echo xlt("Light-intensity activity is non-sedentary waking behavior that requires less than 3.0 METs includes walking at a slow or leisurely pace, 2 mph or less, cooking activities, or light household chores");?>.
                    
                    <p><?php echo xlt("Moderate-intensity activity requires 3.0 to less than 6.0 METs includes walking briskly, 2.5 to 4 mph, playing doubles tennis, or raking the yard");?>.
                    
                    <p><?php echo xlt("Vigorous-intensity activity requires 6.0 or more METs includes jogging, running, carrying heavy groceries or other loads upstairs, shoveling snow, or participating in a strenuous fitness class");?>.
                    
                    <p><?php echo xlt("Many adults do no vigorous-intensity physical activity");?>.
                    
                    <p><?php echo xlt("Physical activity is tracked by the number of days a week and the number of minutes the patient indulges in at least moderate physical activity");?>.
                    
                    <p><?php echo xlt("There are four levels of aerobic physical activity");?>:
                        <ul>
                            <li><strong><?php echo xlt("Inactive"); ?></strong> - <?php echo xlt("is not getting any moderate or vigorous-intensity physical activity beyond basic movement from daily life activities"); ?></li>
                            <li><strong><?php echo xlt("Insufficiently active"); ?></strong> - <?php echo xlt(" less than 150 minutes of moderate-intensity physical activity a week or 75 minutes of vigorous-intensity physical activity or the equivalent combination"); ?></li>
                            <li><strong><?php echo xlt("Active"); ?></strong> - <?php echo xlt(" is doing the equivalent of 150 minutes to 300 minutes of moderate-intensity physical activity a week"); ?></li>
                            <li><strong><?php echo xlt("Highly active"); ?></strong> - <?php echo xlt(" is doing the equivalent of more than 300 minutes of moderate-intensity physical activity a week"); ?></li>
                        </ul>
                    
                    <p><?php echo xlt("Active level of physical activity meets the key guideline target range for adults and highly active level of physical activity exceeds it");?>.
                    
                    <p><?php echo xlt("More information on physical activity guidelines can be obtained by following this link");?>.
                    <a href="https://health.gov/paguidelines/second-edition/pdf/Physical_Activity_Guidelines_2nd_edition.pdf" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    
                    <p><strong><?php echo xlt("ALCOHOL USE"); ?> :</strong>
                    
                    <p><?php echo xlt("Alcohol use is assessed by three questions and a score is calculated from the answers");?>.
                    
                    <p><?php echo xlt("The Alcohol Use Disorders Identification Test C (AUDIT-C) is scored on a scale of 0-12 where the higher the score, the more likely the patient`s drinking is hazardous");?>.
                    
                    <p><?php echo xlt("A score of 4 or more for men and 3 or more for women is considered positive for hazardous drinking or active alcohol use disorders");?>.
                    
                    <p><?php echo xlt("If the points are all from Question 1 alone where points from questions 2 and 3 are 0, it is likely the patient is drinking below recommended limits");?>.
                    
                    <p><?php echo xlt("The care provider may review the patients alcohol intake over that past few months to confirm accuracy");?>.
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Stress and Depression Snapshot"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><strong><?php echo xlt("STRESS"); ?> :</strong>
                    
                    <p><?php echo xlt("A single-question stress measure converged with items on psychological symptoms and sleep disturbances and with validated measures of well-being scored on a five-point Likert scale");?>.
                    
                    <p><?php echo xlt("This question has been primarily tested in Scandinavian populations and can be linked to the PROMIS Emotional Distress (Depression and Anxiety) Short Form scales");?>.
                    
                    <p><?php echo xlt("As of 2015, there is no clinical cutoff for determining when interventions, such as referral to stress management, are warranted");?>.
                    
                    <p><strong><?php echo xlt("DEPRESSION"); ?> :</strong>
                        
                    <p><?php echo xlt("The Patient Health Questionnaire (PHQ) is a diagnostic tool for mental health disorders used by health care professionals that is quick and easy for patients to complete");?>.
                    
                    <p><?php echo xlt("The first two items of the PHQ-9 are used as a screener for depression and are referred to as PHQ-2");?>.
                    
                    <p><?php echo xlt("The calculated score ranges from 0 to 6");?>.
                    
                    <p><?php echo xlt("A positive to either question indicates the need for further testing");?>.
                    
                    <p><?php echo xlt("More information is available by following this link");?>
                    <a href="https://www.phqscreeners.com/select-screener/36" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Social Connection and Isolation Snapshot"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Socially isolated men and women have worse unadjusted survival curves than less socially isolated individuals");?>.
                    
                    <p><?php echo xlt("Social isolation scores range from 0 to 4, with 0 representing the highest level of social isolation and 4 representing the lowest level");?>.
                    
                    <p><?php echo xlt("1 point is awarded for each of the following");?>:
                        <ul>
                            <li><?php echo xlt("being married or living together with someone in a partnership at the time of questioning"); ?></li>
                            <li><?php echo xlt("averaging 3 or more interactions per week with other people"); ?></li>
                            <li><?php echo xlt("attending church or religious services 4 or more times per year"); ?></li>
                            <li><?php echo xlt("reporting that they participate in a club or organization such as a church group, union, fraternal or athletic group, or school group"); ?></li>
                        </ul>
                    
                    <p><?php echo xlt("Scores of 0 or 1 are the most socially isolated participants");?>.
                    
                    <p><?php echo xlt("More information is available by following this link");?>
                    <a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3871270/" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </div>
            </div>
            <div class= "row" id="section5">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Exposure to violence - HARK Snapshot"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Intimate partner violence (IPV) including physical, sexual and emotional violence, causes short and long term ill-health");?>.
                    
                    <p><?php echo xlt("Also known as the Humiliation, Afraid, Rape, and Kick questionnaire - HARK");?>.
                    
                    <p><?php echo xlt("Hark is an archaic verb that means 'to listen attentively'");?>.
                    
                    <p><?php echo xlt("Each HARK question is equal to 1 point for a positive answer with a total score range from 0 to 4");?>.
                    
                    <p><?php echo xlt("A HARK score of ≥1 indicates the patient is affected by interpersonal violence - IPV");?>.
                    
                    <p><?php echo xlt("More information is available by following this link");?>
                    <a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC2034562/" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </div>
            </div>
        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>
        
        <script>
        // better script for tackling nested divs
           $('.show_hide').click(function() {
                var elementTitle = $(this).prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                //$('.hideaway').toggle('1000');
                $(this).parent().parent().closest('div').children('.hideaway').toggle('1000');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                    $(this).toggleClass('fa-eye-slash fa-eye');
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                    $(this).toggleClass('fa-eye fa-eye-slash');
                }
                $(this).prop('title', elementTitle);
            });
        </script>
    </body>
</html>
