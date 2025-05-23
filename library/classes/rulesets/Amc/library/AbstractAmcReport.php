<?php

/**
 * AbstractAmcReport class
 *
 * Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
 * Copyright (C) 2015 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author Discover and Change, Inc. <snielson@discoverandchange.com>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../../library/RsFilterIF.php");
require_once('AmcFilterIF.php');
require_once('IAmcItemizedReport.php');
require_once(dirname(__FILE__) . "/../../../../clinical_rules.php");
require_once(dirname(__FILE__) . "/../../../../amc.php");

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Reports\AMC\Trackers\AMCItemTracker;
use OpenEMR\Reports\AMC\Trackers\AMCItemSkipTracker;

abstract class AbstractAmcReport implements RsReportIF
{
    /**
     * @var AmcPopulation Patient Population
     */
    protected $_amcPopulation;

    protected $_resultsArray = array();

    protected $_rowRule;
    protected $_ruleId;
    protected $_beginMeasurement;
    protected $_endMeasurement;

    protected $_manualLabNumber;

    /**
     * @var AMCItemTracker
     */
    protected $_aggregator;

    /**
     * @var SystemLogger
     */
    private $logger;

    /*
     * @var int|null
     */
    protected $_billingFacilityId;

    /**
     * @var int|null
     */
    protected $_providerId;

    public function __construct(array $rowRule, array $patientIdArray, $dateTarget, $options)
    {
        // require all .php files in the report's sub-folder
        // TODO: This really needs to be moved to using our namespace autoloader... no point in doing a file stat check
        // for every single rule we have, over and over again every time the rule is instantiated.
        $className = get_class($this);
        foreach (glob(dirname(__FILE__) . "/../reports/" . $className . "/*.php") as $filename) {
            require_once($filename);
        }

        // require common .php files
        foreach (glob(dirname(__FILE__) . "/../reports/common/*.php") as $filename) {
            require_once($filename);
        }

        // require clinical types
        foreach (glob(dirname(__FILE__) . "/../../../ClinicalTypes/*.php") as $filename) {
            require_once($filename);
        }

        $this->_amcPopulation = new AmcPopulation($patientIdArray);
        $this->_rowRule = $rowRule;
        $this->_ruleId = isset($rowRule['id']) ? $rowRule['id'] : '';
        // Parse measurement period, which is stored as array in $dateTarget ('dateBegin' and 'dateTarget').
        $this->_beginMeasurement = $dateTarget['dateBegin'] ?? '';
        $this->_endMeasurement = $dateTarget['dateTarget'] ?? '';
        $this->_manualLabNumber = $options['labs_manual'] ?? 0;

        if (isset($GLOBALS['report_itemizing_temp_flag_and_id']) && $GLOBALS['report_itemizing_temp_flag_and_id']) {
            $this->_aggregator = $options['aggregator'] ?? new AMCItemTracker();
        } else {
            $this->_aggregator = new AMCItemSkipTracker();
        }
        $this->logger = new SystemLogger();
        $this->logger->debug(get_class($this) . "->__construct() finished", ['patients' => $patientIdArray]);

        $this->_billingFacilityId = $options['billing_facility_id'] ?? null;
        $this->_providerId = $options['provider_id'] ?? null;
    }

    public function getPatientPopulation(): AmcPopulation
    {
        return $this->_amcPopulation;
    }

    abstract public function createNumerator();
    abstract public function createDenominator();
    abstract public function getObjectToCount();

    public function getAggregator()
    {
        return $this->_aggregator;
    }

    public function getResults()
    {
        return $this->_resultsArray;
    }

    public function execute()
    {

        $this->logger->debug(get_class($this) . "->execute() starting function");

        // If itemization is turned on, then iterate the rule id iterator
        //
        // Note that when AMC rules supports different patient populations and
        // numerator calculation, then it will need to change placement of
        // this and mimic the CQM rules mechanism
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $GLOBALS['report_itemized_test_id_iterator']++;
        }

        $numerator = $this->createNumerator();
        if (!$numerator instanceof AmcFilterIF) {
            throw new Exception("Numerator must be an instance of AmcFilterIF");
        }

        $denominator = $this->createDenominator();
        if (!$denominator instanceof AmcFilterIF) {
            throw new Exception("Denominator must be an instance of AmcFilterIF");
        }

        $totalPatients = count($this->_amcPopulation);

        // Figure out object to be counted
        //   (patients, labs, transitions, visits, or prescriptions)
        $object_to_count = $this->getObjectToCount();
        if (empty($object_to_count)) {
            $object_to_count = "patients";
        }

        $this->logger->debug(get_class($this) . "->execute()", ['totalPatients' => $totalPatients, 'object_to_count' => $object_to_count]);

        $numeratorObjects = 0;
        $denominatorObjects = 0;

        // Deal with the manually added labs for the electronic labs AMC measure
        if ($object_to_count == "labs") {
            // not sure how we account for individual reporting here.
            $denominatorObjects = $this->_manualLabNumber;
            $this->logger->debug(
                get_class($this) . "->execute() manual labs processed",
                ['numeratorObjects' => $numeratorObjects, 'denominatorObjects' => $denominatorObjects]
            );
        }

        // we handle patients differently then patient referenced objects.
        if ($object_to_count == "patients") {
            $this->executeForPatients($numerator, $denominator, $numeratorObjects, $denominatorObjects);
        } else {
            $this->executeForPatientObjects($numerator, $denominator, $object_to_count, $numeratorObjects, $denominatorObjects);
        }


        $percentage = calculate_percentage($denominatorObjects, 0, $numeratorObjects);

        $result = new AmcResult($this->_rowRule, $totalPatients, $denominatorObjects, 0, $numeratorObjects, $percentage);
        $this->_resultsArray[] = &$result;
        $this->logger->debug(get_class($this) . "->execute() leaving rule");
    }

    private function executeForPatients($numerator, $denominator, &$numeratorObjects, &$denominatorObjects)
    {
        $numeratorObjects = 0;
        $denominatorObjects = 0;

        foreach ($this->_amcPopulation as $patient) {
            $pass = 0;
            $numeratorResultItemDetails = new AmcItemizedActionData();
            $denominatorResultItemDetails = new AmcItemizedActionData();

            // If begin measurement is empty, then make the begin
            //  measurement the patient dob.
            $tempBeginMeasurement = $this->getRuleBeginDateForPatient($patient);

            // Counting patients
            if (!$denominator->test($patient, $tempBeginMeasurement, $this->_endMeasurement)) {
                continue;
            }
            if ($denominator instanceof IAmcItemizedReport) {
                $denominatorResultItemDetails = $denominator->getItemizedDataForLastTest();
            }
            $denominatorObjects++;

            if ($numerator->test($patient, $tempBeginMeasurement, $this->_endMeasurement)) {
                $numeratorObjects++;
                $pass = 1;
            }
            if ($numerator instanceof IAmcItemizedReport) {
                $numeratorResultItemDetails = $numerator->getItemizedDataForLastTest();
            }
            // If itemization is turned on, then record the "passed" item
            $this->_aggregator->addItem(
                $GLOBALS['report_itemizing_temp_flag_and_id'],
                $GLOBALS['report_itemized_test_id_iterator'],
                $this->_ruleId,
                $tempBeginMeasurement,
                $this->_endMeasurement,
                $pass,
                $patient->id,
                'patients',
                $numeratorResultItemDetails,
                $denominatorResultItemDetails
            );
            $this->logger->debug(
                get_class($this) . "->execute() patient processed",
                ['pid' => $patient->id, 'numeratorObjects' => $numeratorObjects, 'denominatorObjects' => $denominatorObjects]
            );
        }
    }

    private function getRuleBeginDateForPatient(AmcPatient $patient)
    {
        if (empty($this->_beginMeasurement)) {
            $tempBeginMeasurement = $patient->dob;
        } else {
            $tempBeginMeasurement = $this->_beginMeasurement;
        }
        return $tempBeginMeasurement;
    }

    private function executeForPatientObjects($numerator, $denominator, $object_to_count, &$numeratorObjects, &$denominatorObjects)
    {
        foreach ($this->_amcPopulation as $patient) {
            // If begin measurement is empty, then make the begin
            //  measurement the patient dob.
            $tempBeginMeasurement = $this->getRuleBeginDateForPatient($patient);

            // Counting objects other than patients
            //   First, collect the pertinent objects
            $objects = $this->collectObjects($patient, $object_to_count, $tempBeginMeasurement, $this->_endMeasurement);
            //   Second, test each object
            foreach ($objects as $object) {
                $numeratorResultItemDetails = new AmcItemizedActionData();
                $denominatorResultItemDetails = new AmcItemizedActionData();

                $pass = 0;
                $patient->object = $object;
                if ($denominator->test($patient, $tempBeginMeasurement, $this->_endMeasurement)) {
                    $denominatorObjects++;

                    // now that the denominator passed, let's add in our object
                    if ($denominator instanceof IAmcItemizedReport) {
                        $denominatorResultItemDetails = $denominator->getItemizedDataForLastTest();
                    }

                    if ($numerator->test($patient, $tempBeginMeasurement, $this->_endMeasurement)) {
                        $numeratorObjects++;
                        $pass = 1;
                    }

                    if ($numerator instanceof IAmcItemizedReport) {
                        $numeratorResultItemDetails = $numerator->getItemizedDataForLastTest();
                    }
                    $this->_aggregator->addItem(
                        $GLOBALS['report_itemizing_temp_flag_and_id'],
                        $GLOBALS['report_itemized_test_id_iterator'],
                        $this->_ruleId,
                        $tempBeginMeasurement,
                        $this->_endMeasurement,
                        $pass,
                        $patient->id,
                        $object_to_count,
                        $numeratorResultItemDetails,
                        $denominatorResultItemDetails
                    );
                }
            }
            $this->logger->debug(
                get_class($this) . "->execute() patient processed",
                ['pid' => $patient->id, 'numeratorObjects' => $numeratorObjects, 'denominatorObjects' => $denominatorObjects]
            );
        }
    }

    private function collectObjects($patient, $object_label, $begin, $end)
    {

        $results = array();
        $sqlBindArray = array();

        switch ($object_label) {
            case "transitions-in":
                 $sql = "SELECT amc_misc_data.map_id as `encounter`, amc_misc_data.date_completed as `completed`, form_encounter.date as `date` " .
                        "FROM `amc_misc_data`, `form_encounter` " .
                        "INNER JOIN openemr_postcalendar_categories opc on opc.pc_catid = form_encounter.pc_catid " .
                        "WHERE amc_misc_data.map_id = form_encounter.encounter " .
                        "AND amc_misc_data.map_category = 'form_encounter' " .
                        "AND amc_misc_data.pid = ? AND form_encounter.pid = ? " .
                        "AND amc_misc_data.amc_id = 'med_reconc_amc' " .
                        "AND form_encounter.date >= ? AND form_encounter.date <= ? " .
                        "AND ((opc.pc_catname = 'New Patient') OR (opc.pc_catname = 'Established Patient' AND amc_misc_data.soc_provided is not null)) ";
                array_push($sqlBindArray, $patient->id, $patient->id, $begin, $end);
                break;
            case "transitions-out":
                return $this->collectTransitionOutObjects($patient, $begin, $end, $this->_billingFacilityId, $this->_providerId);
                break;
            case "encounters":
                $sql = "SELECT * " .
                       "FROM `form_encounter` " .
                       "WHERE `pid` = ? " .
                       "AND `date` >= ? AND `date` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "encounters_office_visit":
                $sql = "SELECT * " .
                       "FROM `form_encounter` LEFT JOIN `enc_category_map` ON (form_encounter.pc_catid = enc_category_map.main_cat_id) " .
                       "WHERE enc_category_map.rule_enc_id = 'enc_off_vis' " .
                       "AND `pid` = ? " .
                       "AND `date` >= ? AND `date` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "cpoe_medications":
                $sql = "SELECT  `drug` " .
                       "FROM `prescriptions` " .
                       "WHERE `patient_id` = ? " .
                       "AND `date_added` >= ? AND `date_added` <= ?  ";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "prescriptions":
                $sql = "SELECT * " .
                       "FROM `prescriptions` " .
                       "WHERE `patient_id` = ? " .
                       "AND `date_added` >= ? AND `date_added` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "labs":
                $sql = "SELECT procedure_result.result FROM " .
                       "procedure_order, " .
                       "procedure_report, " .
                       "procedure_result " .
                       "WHERE " .
                       "procedure_order.patient_id = ? AND " .
                       "procedure_order.procedure_order_id = procedure_report.procedure_order_id AND " .
                       "procedure_report.procedure_report_id = procedure_result.procedure_report_id AND " .
                       "procedure_report.date_collected >= ? AND procedure_report.date_collected <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "image_orders":
                $sql = "SELECT pr.* FROM procedure_order pr " .
                "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                "WHERE pr.patient_id = ? " .
                "AND prc.procedure_order_title LIKE '%imaging%' " .
                "AND (pr.date_ordered BETWEEN ? AND ?)";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;


            case "lab_radiology":
                $sql = "SELECT pr.* FROM procedure_order pr " .
                      "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                      "LEFT JOIN procedure_providers pp ON pr.lab_id = pp.ppid " .
                      "LEFT JOIN users u ON u.id = pp.lab_director " .
                      "WHERE pr.patient_id = ? " .
                      "AND prc.procedure_order_title LIKE '%imaging%' " .
                      "AND (pr.date_ordered BETWEEN ? AND ?)";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;

            case "cpoe_lab_orders":
                $sql = "SELECT pr.* FROM procedure_order pr " .
                      "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                      "LEFT JOIN procedure_providers pp ON pr.lab_id = pp.ppid " .
                      "LEFT JOIN users u ON u.id = pp.lab_director " .
                      "WHERE pr.patient_id = ? " .
                      "AND prc.procedure_order_title LIKE '%laboratory_test%' " .
                      "AND (pr.date_ordered BETWEEN ? AND ?)";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;

            case "med_orders":
                        // Still TODO
                        // AMC MU2 TODO :
                        //  Note the cpoe_flag and functionality does not exist in OpenEMR official codebase.
                        //
                $sql = "SELECT drug,erx_source as cpoe_stat " .
                       "FROM `prescriptions` " .
                       "WHERE `patient_id` = ? " .
                       "AND `date_added` BETWEEN ? AND ? ";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;

            case "lab_orders":
                $sql = "SELECT prc.* FROM procedure_order pr " .
                      "INNER JOIN procedure_order_code prc ON pr.procedure_order_id = prc.procedure_order_id " .
                      "WHERE pr.patient_id = ? " .
                      "AND (prc.procedure_order_title LIKE '%Laboratory%' or (prc.procedure_source = 2 and prc.procedure_order_title is NULL)) " .
                      "AND (pr.date_ordered BETWEEN ? AND ?)";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
        }

        $rez = sqlStatement($sql, $sqlBindArray);
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $results[$iter] = $row;
        }

        return $results;
    }

    private function collectTransitionOutObjects($patient, $begin, $end, $billing_facility, $provider_id)
    {

        $results = [];
        $sqlBindArray = [];
        $sqlSelect = "SELECT transactions.id as id,transactions.date ";
        $sqlFrom = "FROM transactions " .
            "INNER JOIN lbt_data on lbt_data.form_id = transactions.id ";
        $sqlWhere = "WHERE transactions.title = 'LBTref' " .
            "AND transactions.pid = ? " .
            "AND lbt_data.field_id = 'refer_date' " .
            "AND lbt_data.field_value >= ? AND lbt_data.field_value <= ?";

        array_push($sqlBindArray, $patient->id, $begin, $end);

        // for group calculation methods we need to restrict the data to the billing facility and the provider who issued the referral
        // this is because we may have patients that have been seen by the provider, but a DIFFERENT provider issued a referral for that patient
        // without this filtering that patient's referrals would be counted
        if (!empty($billing_facility) && intval($billing_facility) > 0) {
            $sqlFrom .= ' INNER JOIN lbt_data lbt_billing_fac ON (lbt_billing_fac.form_id = transactions.id AND lbt_billing_fac.field_id = "billing_facility_id")';
            $sqlWhere .= " AND lbt_billing_fac.field_value = ? ";
            $sqlBindArray[] = intval($billing_facility);
        }

        if (!empty($provider_id) && intval($provider_id) > 0) {
            $sqlFrom .= 'INNER JOIN lbt_data lbt_refer_from ON (lbt_refer_from.form_id = transactions.id AND lbt_refer_from.field_id = "refer_from") ';
            $sqlWhere .= " AND lbt_refer_from.field_value = ? ";
            $sqlBindArray[] = intval($provider_id);
        }

        $sql = $sqlSelect . $sqlFrom . $sqlWhere;
        $rez = sqlStatement($sql, $sqlBindArray);
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $fres = sqlStatement(
                "SELECT field_id, field_value FROM lbt_data WHERE form_id = ?",
                array($row['id'])
            );
            while ($frow = sqlFetchArray($fres)) {
                $row[$frow['field_id']] = $frow['field_value'];
            }
            $results[$iter] = $row;
        }
        return $results;
    }

    public function hydrateItemizedDataFromRecord($data): AmcItemizedActionData
    {


        $numerator = $this->createNumerator();
        if ($numerator instanceof IAmcItemizedReport) {
            $numeratorData = $numerator->hydrateItemizedDataFromRecord($data['numerator'] ?? []);
        } else {
            $numeratorData = new AmcItemizedActionData();
        }
        $denominator = $this->createDenominator();
        if ($denominator instanceof IAmcItemizedReport) {
            $denominatorData = $denominator->hydrateItemizedDataFromRecord($data['denominator'] ?? []);
        } else {
            $denominatorData = new AmcItemizedActionData();
        }
        $numeratorData->addActionObject($denominatorData);
        return $numeratorData;
    }
}
