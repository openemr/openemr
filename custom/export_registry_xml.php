<?php

 // Copyright (C) 2011 Ensoftek
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This program exports report to PQRI 2009 XML format.


require_once("../interface/globals.php");
require_once("../library/patient.inc.php");
require_once "../library/options.inc.php";
require_once("../library/clinical_rules.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//Remove time limit, since script can take many minutes
set_time_limit(0);

// Set the "nice" level of the process for these reports. When the "nice" level
// is increased, these cpu intensive reports will have less affect on the performance
// of other server activities, albeit it may negatively impact the performance
// of this report (note this is only applicable for linux).
if (!empty($GLOBALS['cdr_report_nice'])) {
    proc_nice($GLOBALS['cdr_report_nice']);
}

function getLabelNumber($label)
{

    if (strlen($label) == 0) {
        return "1";
    }

    $tokens = explode(" ", $label);

    $num_tokens = sizeof($tokens);
    if ($tokens[$num_tokens - 1] != null) {
        if (is_numeric($tokens[$num_tokens - 1])) {
            return $tokens[$num_tokens - 1];
        }
    }

    return "1";
}

function getMeasureNumber($row)
{
    if (!empty($row['cqm_pqri_code']) || !empty($row['cqm_nqf_code'])) {
        if (!empty($row['cqm_pqri_code'])) {
            return $row['cqm_pqri_code'];
        }

        if (!empty($row['cqm_nqf_code'])) {
            return $row['cqm_nqf_code'];
        }
    } else {
        return "";
    }
}


// Collect parameters (set defaults if empty)
$target_date = (isset($_GET['target_date'])) ? trim($_GET['target_date']) : date('Y-m-d H:i:s');
$nested = (isset($_GET['nested'])) ? trim($_GET['nested']) : 'false';
$xml = new PQRIXml();

// Add the XML parent tag.
$xml->open_submission();

// Add the file audit data
$xml->add_file_audit_data();

// Add the registry entries
if ($nested == 'false') {
    $xml->add_registry('A');
} else {
    $xml->add_registry('E');
}


// Add the measure groups.
if ($nested == 'false') {
        // Collect results (note using the batch method to decrease memory overhead and improve performance)
    $dataSheet = test_rules_clinic_batch_method('collate_outer', 'cqm_2011', $target_date, 'report', '', '');
} else {
        // Collect results (note using the batch method to decrease memory overhead and improve performance)
    $dataSheet = test_rules_clinic_batch_method('collate_inner', 'cqm_2011', $target_date, 'report', 'cqm', 'plans');
}

$firstProviderFlag = true;
$firstPlanFlag = true;
$existProvider = false;

if ($nested == 'false') {
     $xml->open_measure_group('X');
}

foreach ($dataSheet as $row) {
    //print_r($row);
    if (isset($row['is_main']) || isset($row['is_sub'])) {
        if (isset($row['is_main'])) {
            // Add PQRI measures
            $pqri_measures = array();
            $pqri_measures['pqri-measure-number'] =  getMeasureNumber($row);
            $pqri_measures['patient-population'] = getLabelNumber($row['population_label']);
            $pqri_measures['numerator'] = getLabelNumber($row['numerator_label']);
            $pqri_measures['eligible-instances'] = $row['pass_filter'];
            $pqri_measures['meets-performance-instances'] = $row['pass_target'];
            $pqri_measures['performance-exclusion-instances'] =  $row['excluded'];
            $performance_not_met_instances = (int)$row['pass_filter'] - (int)$row['pass_target'] - (int)$row['excluded'];
            $pqri_measures['performance-not-met-instances'] = (string)$performance_not_met_instances;
            $pqri_measures['performance-rate'] = $row['percentage'];
            $pqri_measures['reporting-rate'] = (($row['pass_filter'] - $row['excluded']) / $row['pass_filter']) * 100;
                $pqri_measures['reporting-rate'] = $pqri_measures['reporting-rate'] . '%';
            $xml->add_pqri_measures($pqri_measures);
        } else { // $row[0] == "sub"
        }
    } elseif (isset($row['is_provider'])) {
        if ($firstProviderFlag == false) {
             $xml->close_provider();
        }

         // Add the provider
        $physician_ids = array();
        if (!empty($row['npi']) || !empty($row['federaltaxid'])) {
            if (!empty($row['npi'])) {
                $physician_ids['npi'] = $row['npi'];
            }

            if (!empty($row['federaltaxid'])) {
                $physician_ids['tin'] = $row['federaltaxid'];
            }
        }

         $physician_ids['encounter-from-date'] = '01-01-' . date('Y', strtotime($target_date));
         $physician_ids['encounter-to-date'] = '12-31-' . date('Y', strtotime($target_date));

         $xml->open_provider($physician_ids);
         $firstProviderFlag = false;
         $existProvider = true;
    } else { // isset($row['is_plan'])
        if ($firstPlanFlag == false) {
            if ($firstProviderFlag == false) {
                $xml->close_provider();
            }

            if ($nested == 'true') {
                $xml->close_measure_group();
            }
        }

        if ($nested == 'true') {
            $xml->open_measure_group($row['cqm_measure_group']);
        }

         $firstPlanFlag = false;
         $firstProviderFlag = true; // Reset the provider flag
    }
}

if ($existProvider == true) {
    $xml->close_provider();
    $xml->close_measure_group();
}

$xml->close_submission();

?>

<html>
<head>
<?php Header::setupHeader('opener'); ?>
<title><?php echo xlt('Export PQRI Report'); ?></title>
</head>
<body>

<p><?php echo xlt('The exported data appears in the text area below. You can copy and paste this into an email or to any other desired destination.'); ?></p>

<center>
<form>

<textarea rows='50' cols='500' style='width:95%' readonly>
<?php echo $xml->getXml(); ?>
</textarea>

<p><input type='button' value='<?php echo xla('OK'); ?>' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
