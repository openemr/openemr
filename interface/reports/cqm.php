<?php

/**
 * CDR reports.  Handles the generation and display of CQM/AMC/patient_alerts/standard reports
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: This needs a complete makeover


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/report_database.inc";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\PractitionerService;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Report")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$amc_report_types = CertificationReportTypes::getReportTypeRecords();

// See if showing an old report or creating a new report
$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";

// Collect the back variable, if pertinent
$back_link = (isset($_GET['back'])) ? trim($_GET['back']) : "";

// If showing an old report, then collect information
$heading_title = "";
$help_file_name = "";
if (!empty($report_id)) {
    $report_view = collectReportDatabase($report_id);
    $date_report = $report_view['date_report'];
    $type_report = $report_view['type'];

    $is_amc_report = CertificationReportTypes::isAMCReportType($type_report);
    $is_cqm_report = ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014");
    $type_report = ($is_amc_report || $is_cqm_report) ? $type_report : "standard";
    $rule_filter = $report_view['type'];

    if ($is_amc_report) {
        $begin_date = $report_view['date_begin'];
        $labs_manual = $report_view['labs_manual'];
    }

    $target_date = $report_view['date_target'];
    $plan_filter = $report_view['plan'];
    $organize_method = $report_view['organize_mode'];
    $provider  = $report_view['provider'];
    $pat_prov_rel = $report_view['pat_prov_rel'];


    $amc_report_data = $amc_report_types[$type_report] ?? array();
    $dataSheet = formatReportData($report_id, $report_view['data'], $is_amc_report, $is_cqm_report, $type_report, $amc_report_data);
} else {
  // Collect report type parameter (standard, amc, cqm)
  // Note that need to convert amc_2011 and amc_2014 to amc and cqm_2011 and cqm_2014 to cqm
  // to simplify for when submitting for a new report.
    $type_report = (isset($_GET['type'])) ? trim($_GET['type']) : "standard";

    $is_amc_report = CertificationReportTypes::isAMCReportType($type_report);
    $is_cqm_report = ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014");

    if (($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
        $type_report = "cqm";
    }

  // Collect form parameters (set defaults if empty)
    if ($is_amc_report) {
        $begin_date = (isset($_POST['form_begin_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_begin_date'])) : "";
        $labs_manual = (isset($_POST['labs_manual_entry'])) ? trim($_POST['labs_manual_entry']) : "0";
    }

    $target_date = (isset($_POST['form_target_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_target_date'])) : date('Y-m-d H:i:s');
    $rule_filter = (isset($_POST['form_rule_filter'])) ? trim($_POST['form_rule_filter']) : CertificationReportTypes::DEFAULT;
    $plan_filter = (isset($_POST['form_plan_filter'])) ? trim($_POST['form_plan_filter']) : "";
    $organize_method = (empty($plan_filter)) ? "default" : "plans";
    $provider  = trim($_POST['form_provider'] ?? '');
    $pat_prov_rel = (empty($_POST['form_pat_prov_rel'])) ? "primary" : trim($_POST['form_pat_prov_rel']);
    $dataSheet = [];
}

$show_help = false;
if ($type_report == "standard") {
    $heading_title = xl('Standard Measures');
} else if ($type_report == "cqm") {
    $heading_title = xl('Clinical Quality Measures (CQM)');
} else if ($type_report == 'cqm_2011') {
    $heading_title = 'Clinical Quality Measures (CQM) - 2011';
} else if ($type_report == "cqm_2014") {
    $heading_title = 'Clinical Quality Measures (CQM) - 2014';
} else if ($is_amc_report) {
    $heading_title = $amc_report_types[$type_report]['title'];
    $show_help = true;
    $help_file_name = "cqm_amc_help.php";
}

$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();

$formData = [
    'type_report' => $type_report
    ,'heading_title' => $heading_title
    ,'date_report' => isset($date_report) ? oeFormatDateTime($date_report, "global", true) : ''
    ,'report_id' => $report_id ?? null
    ,'show_help' => $show_help
    ,'oemrUiSettings' =>  [
        'heading_title' => xl('Add/Edit Patient Transaction'),
        'include_patient_name' => false,
        'expandable' => false,
        'expandable_files' => array(),//all file names need suffix _xpd
        'action' => "conceal",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "cqm.php",//only for actions - reset, link and back
        'show_help_icon' => $show_help,
        'help_file_name' => $help_file_name
    ]
    ,'csrf_token' => CsrfUtils::collectCsrfToken()
    ,'widthDyn' => '610px'
    ,'is_amc_report' => $is_amc_report
    ,'dis_text' => (!empty($report_id) ? "disabled='disabled'" : "")
    ,'begin_date' => isset($begin_date) ? oeFormatDateTime($begin_date, 0, true) : ""
    ,'target_date' => oeFormatDateTime($target_date, 0, true)
    ,'target_date_label' => ($is_amc_report ? xl('End Date') : xl('Target Date'))
    ,'rule_filters' => []
    ,'show_plans' => !$is_amc_report
    ,'plans' => []
    ,'providerReportOptions' => [
        ['value' => '', 'selected' => false, 'label' => '-- ' . xl('All (Cumulative)') . ' --']
        ,['value' => 'collate_outer', 'selected' => $provider == 'collate_outer', 'label' => xl('All (Collated Format A)')]
        ,['value' => 'collate_inner', 'selected' => $provider == 'collate_inner', 'label' => xl('All (Collated Format B)')]
    ]
    ,'providerRelationship' => [
        ['value' => 'primary', 'selected' => $pat_prov_rel == 'primary', 'label' => xl('Primary')]
        ,['value' => 'encounter', 'selected' => $pat_prov_rel == 'encounter', 'label' => xl('Encounter')]
    ]
    ,'show_manual_labs' => false
    ,'labs_manual' => $labs_manual ?? 0
    ,'display_submit' => empty($report_id)
    ,'display_pqri_btns' => $type_report == 'cqm' && !empty($report_id)
    ,'display_amc_details' => !empty($report_id) && $is_amc_report
    ,'display_back_link' => $back_link == 'list'
    ,'display_qrda_btns' => !empty($report_id) && $type_report == 'cqm_2014'
    ,'display_new_report_btn' => !empty($report_id) && $back_link != 'list'
    , 'collate_outer' => $provider == 'collate_outer'
    , 'datasheet' => $dataSheet
];
if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
    $formData['widthDyn'] = '410px';
    $formData['rule_filters'] = [
        ['value' => 'cqm', 'selected' => $type_report == 'cqm', 'label' => xl('All Clinical Quality Measures (CQM)')]
        ,['value' => 'cqm_2011', 'selected' => $type_report == 'cqm_2011', 'label' => xl('2011 Clinical Quality Measures (CQM)')]
        ,['value' => 'cqm_2014', 'selected' => $type_report == 'cqm_2014', 'label' => xl('2014 Clinical Quality Measures (CQM)')]
    ];
    $formData['plans'] = [
        ['value' => '', 'selected' => false, 'label' => '-- ' . xl('Ignore') . ' --']
        ,['value' => 'cqm', 'selected' => $plan_filter == 'cqm', 'label' => xl('All Official Clinical Quality Measures (CQM) Measure Groups')]
        ,['value' => 'cqm_2011', 'selected' => $plan_filter == 'cqm_2011', 'label' => xl('2011 Official Clinical Quality Measures (CQM) Measure Groups')]
        ,['value' => 'cqm_2014', 'selected' => $plan_filter == 'cqm_2014', 'label' => xl('2014 Official Clinical Quality Measures (CQM) Measure Groups')]
    ];
} else if ($is_amc_report) {
    // latest AMC doesn't have collate options
    if (empty($report_id)) {
        // truncate to just the first option
        $formData['providerReportOptions'] = [
            $formData['providerReportOptions'][0]
        ];
        $formData['rule_filters'] = [
            ['value' => CertificationReportTypes::DEFAULT, 'selected' => true
                , 'label' => $amc_report_types[CertificationReportTypes::DEFAULT]['ruleset_title']]
        ];
        // modern AMC only deals with encounter based relationships
        $formData['providerRelationship'] = [
            $formData['providerRelationship'][1]
        ];
    } else {
        // old AMC had a manual labs input for MIPS
        $formData['show_manual_labs'] = true;
        // need to handle historical data
        foreach ($amc_report_types as $key => $report_type) {
            $formData['rule_filters'][] = ['value' => $key, 'selected' => $type_report == $key
                , 'label' => $amc_report_types[$key]['ruleset_title']];
        }
    }
    $formData['providerReportOptions'][] = ['value' => 'group_calculation', 'selected' => $provider == 'group_calculation'
        , 'label' => xl('All EP/EC Group Calculation')];
} else if ($type_report == 'standard') {
    $formData['rule_filters'] = [
        ['value' => 'passive_alert', 'selected' => $type_report == 'passive_alert', 'label' => xl('Passive Alert Rules')]
        ,['value' => 'active_alert', 'selected' => $type_report == 'active_alert', 'label' => xl('Active Alert Rules')]
        ,['value' => 'patient_reminder', 'selected' => $type_report == 'patient_reminder', 'label' => xl('Patient Reminder Rules')]
    ];
    $formData['plans'] = [
        ['value' => '', 'selected' => false, 'label' => '-- ' . xl('Ignore') . ' --']
        ,['value' => 'value', 'selected' => $plan_filter == 'normal', 'label' => xl('Active Plans')]
    ];
}

// we need to grab the providers and add them to the provider dropdown
$practitionerService = new PractitionerService();
$result = $practitionerService->getAll();
if ($result->hasData()) {
    foreach ($result->getData() as $practitioner) {
        $formData['providerReportOptions'][] = ['value' => $practitioner['id'], 'selected' => $provider == $practitioner['id']
            , 'label' => $practitioner['lname'] . ',' . $practitioner['fname']];
    }
}
echo $twig->render('reports/cqm/cqm.html.twig', $formData);
exit;
