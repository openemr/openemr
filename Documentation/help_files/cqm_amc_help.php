<?php

/**
* Add Edit Transaction Dashboard Help.
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author Ranganath Pathak <pathak@scrs1.org>
* @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");

?>
<!DOCTYPE html>
<html>
<head>
<?php
    Header::setupHeader();
?>
    <title><?php echo xlt("Automated Measures Calculation Help");?></title>
</head>
<body>
<div class="container oe-help-container">
    <div>
        <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Automated Measures Calculation (AMC) Help");?></a></h2>
    </div>
    <div class= "row">
        <div class="col-sm-12">
            <h3><?php echo xlt("Running a report"); ?></h3>
            <p><?php echo xlt("Automated Measure Calculations are used for reporting measures in the USA to CMS"); ?>.</p>
            <p><?php echo xlt("The most recent 2015 RuleSet calculations only applies to the MIPS Promoting Interoperability"); ?>.</p>
            <p><?php echo xlt("The AMC calculations in this software only uses the MIPS Promoting Interopability calculation methods and does NOT use the Medicaid calculation methods"); ?>.</p>
            <p><?php echo xlt("Both the Eligible Individual Clinician (EC) calculation method and the Eligible Group Clinician calculation method are supported"); ?>.</p>
            <h4><?php echo xlt("Eligible Individual Clinician (EC) calculation method"); ?></h4>
            <div class="alert alert-info">
                <p><?php echo xlt("Note that for the Automated Measure Calculations to correctly calculate each EC must have both their NPI and TIN number setup"); ?>.</p>
            </div>
            <ol>
                <li><?php echo xlt("Choose the promotion period for the calculation by setting the begin and end date for the report"); ?></li>
                <li>
                    <?php echo xlt("Make sure to choose the correct Rule Set for your promotion period"); ?>.
                    <?php echo xlt("The most current Rule Set is selected by default"); ?>.
                </li>
                <li>
                    <?php echo xlt("Choose the Provider you wish to run for the calculation"); ?>.
                </li>
                <li>
                    <?php echo xlt("Once all the report settings have been chosen press the Submit button"); ?>.
                </li>
            </ol>
            <hr />
            <h4><?php echo xlt("Eligible Group Clinician (EC) calculation method"); ?></h4>
            <div class="alert alert-info">
                <p><?php echo xlt("Note that for the Automated Measure Calculations to correctly calculate each EC must have have their NPI number setup correctly and be connected to a default Billing Facility"); ?>.</p>
                <p><?php echo xlt("The default billing facility must have the tax id number (TIN) that each EC will be connected to for billing purposes"); ?>.</p>
            </div>
            <ol>
                <li><?php echo xlt("Choose the promotion period for the calculation by setting the begin and end date for the report"); ?></li>
                <li>
                    <?php echo xlt("Make sure to choose the correct Rule Set for your promotion period"); ?>.
                    <?php echo xlt("The most current Rule Set is selected by default"); ?>.
                </li>
                <li>
                    <?php echo xlt("For the provider dropdown choose Group Calculation option"); ?>.
                </li>
                <li>
                    <?php echo xlt("Once all the report settings have been chosen press the Submit button"); ?>.
                </li>
            </ol>
        </div>
    </div>
    <div class= "row">
        <div class="col-sm-12">
            <h3><?php echo xlt("Report Results"); ?></h3>
            <p><?php echo xlt("Once a report has generated the results will be displayed on the report screen"); ?></p>
            <p><?php echo xlt("Prior reports can be accessed from the Reports Results page"); ?></p>
            <p><?php echo xlt("Each Report Column is explained below"); ?></p>
            <dl>
                <dt><?php echo xlt("Title"); ?></dt>
                <dd><?php echo xlt("The name and government regulation of the Automated Measure Calculation rule used"); ?>.</dd>
                <dt><?php echo xlt("Total Patients"); ?></dt>
                <dd><?php echo xlt("The total number of patients that were processed for this report"); ?>.</dd>
                <dt><?php echo xlt("Denominator"); ?></dt>
                <dd><?php echo xlt("The number of records (patients,encounters,referrals) that met the denominator calculation inclusion criteria"); ?>.
                    <?php echo xlt("Selecting this number will open a report with all of the patients that had data included in the denominator"); ?>.
                </dd>
                <dt><?php echo xlt("Numerator"); ?></dt>
                <dd>
                    <?php echo xlt("The number of records (patients,encounters,referrals) that successfully passed the rule criteria"); ?>.
                    <?php echo xlt("Selecting this number will open a report with all of the patients that had data that passed in the numerator"); ?>.
                </dd>
                <dt><?php echo xlt("Failed"); ?></dt>
                <dd>
                    <?php echo xlt("The number of records (patients,encounters,referrals) that failed the numerator rule criteria"); ?>.
                    <?php echo xlt("Selecting this number will open a report with all of the patients that had data that failed in the numerator"); ?>.
                </dd>
                <dt><?php echo xlt("Performance Percentage"); ?></dt>
                <dd>
                    <?php echo xlt("The pass/total percentage that was calculated from dividing the numerator by the denominator"); ?>.
                </dd>
            </dl>
            <p><?php echo xlt("Select the AMC Detailed Report button to see a detailed report of patients and patient actions for each AMC measure"); ?></p>
            <p><?php echo xlt("Be aware that the AMC Detailed Report can take a long time to generate if the period selected includes a large amount of patient data"); ?></p>
        </div>
    </div>
</div>
</body>
</html>