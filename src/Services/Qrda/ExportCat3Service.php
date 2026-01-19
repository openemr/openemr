<?php

/**
 * @package    OpenEMR
 * Claude A.I contributed to this file for the QRDA III consolidated report xml generation.
 *
 * @link       http://www.open-emr.org
 * @author     Ken Chapple <ken@mi-squared.com>
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright  Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\IndividualResult;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\ResultsCalculator;

class ExportCat3Service
{
    protected $builder;
    protected $calculator;
    protected $request;
    protected $measures = [];
    protected $results = [];
    protected $effectiveDate;
    protected $effectiveDateEnd;

    const DEBUG = false;

    /**
     * ExportCat3Service constructor.
     *
     * @param CqmCalculator       $calculator
     * @param QdmRequestInterface $request
     */
    public function __construct(QdmBuilder $builder, CqmCalculator $calculator, QdmRequestInterface $request)
    {
        $this->builder = $builder;
        $this->calculator = $calculator;
        $this->request = $request;
        $this->effectiveDate = trim($GLOBALS['cqm_performance_period'] ?? '2022') . '-01-01 00:00:00';
        $this->effectiveDateEnd = trim($GLOBALS['cqm_performance_period'] ?? '2022') . '-12-31 23:59:59';
    }

    public function export($measures, $resultOnly = false)
    {
        // let's build our measures from our json
        $measureObjs = [];
        foreach ($measures as $measurePath) {
            $measure_arr = MeasureService::fetchMeasureJson($measurePath);
            if (!empty($measure_arr)) {
                $measure = new Measure($measure_arr);
                $measure->measure_path = $measurePath;
                $measureObjs[] = $measure;
            } else {
                (new SystemLogger())->error("Measure JSON not found. Verify measures are installed correctly", ['path' => $measurePath]);
            }
        }
        // note that much of this function is following the logic in the cypress test suite
        // @see projectcypress/cypress.git lib/cypress/api_measure_evaluator.rb
        $patients = $this->builder->build($this->request);
        $calculationResults = $this->do_calculation($patients, $measureObjs);

        if (self::DEBUG) {
            $this->logCalculationResults($patients, $calculationResults);
        }

        if ($resultOnly) {
            return $calculationResults;
        }
        // TODO need to get correlation ID from calculator? Maybe bundleId
        $correlation_id = ''; // not sure if we need the correlation id at all

        // now we have a hashmap of measure ids(hqmf_id) => IndividualResult[]
        // ResultsCalculator is going to take all of those results and turn them into aggregated population results
        $resultCalculator = new ResultsCalculator($patients, $correlation_id, $this->effectiveDate);
        $results = $resultCalculator->aggregate_results_for_measures($measureObjs, $calculationResults);
        $options = [
            /*
             * These are options: TODO what is required?
            @see https://ecqi.healthit.gov/sites/default/files/2022-CMS-QRDA-III-Eligible-Clinicians-and-EP-IG-V1.1-508.pdf Section 5.1.4
            for provider information.  provider is based upon group calculation vs individual calculation
            Group calc is the TIN of the billing facility that the measure is run against
            individual calc is the individual provider
            $options['provider']; // @see
            $options['start_time'];
            $options['end_time'];
            $options['submission_program'];
            $options['ry2022_submission'];
            */
            'submission_program' => 'MIPS_INDIV', // This is the value Cypress test doc had.
            'start_time' => $this->effectiveDate,
            'end_time' => $this->effectiveDateEnd,
            'ry2022_submission' => true
        ];

        // uses the measures and aggregated result objects (it will do some additional formatting on those objects
        // inside the view.  We could skip some of the double formatting to consolidate all of this but until we've
        // verified we match cypress validation we will try to match the ruby code flow as much as we possibly can.
        $cat3 = new Cat3($results, $measureObjs, $options);
        $string = $cat3->renderXml();

        return $string;
    }

    /**
     * Generate consolidated QRDA III for all measures
     * This leverages your existing calculation logic
     *
     * @param array $measures Array of measure paths
     * @return string Consolidated QRDA III XML
     */
    public function exportConsolidated($measures)
    {
        // Use your existing measure building logic
        $measureObjs = [];
        foreach ($measures as $measurePath) {
            $measure_arr = MeasureService::fetchMeasureJson($measurePath);
            $measure = new Measure($measure_arr);
            $measure->measure_path = $measurePath;
            $measureObjs[] = $measure;
        }

        // Use your existing patient building logic
        $patients = $this->builder->build($this->request);

        // Use your existing calculation logic
        $calculationResults = $this->do_calculation($patients, $measureObjs);

        if (self::DEBUG) {
            $this->logCalculationResults($patients, $calculationResults);
        }

        // Use your existing aggregation logic
        $correlation_id = 'consolidated_' . uniqid();
        $resultCalculator = new ResultsCalculator($patients, $correlation_id, $this->effectiveDate);
        $results = $resultCalculator->aggregate_results_for_measures($measureObjs, $calculationResults);

        // NEW: Generate consolidated XML instead of individual measure XML
        $consolidatedXml = $this->generateConsolidatedXml($measureObjs, $results, $patients);

        return $consolidatedXml;
    }

    /**
     * Generate consolidated QRDA III XML containing all measures
     *
     * @param array $measureObjs Array of Measure objects
     * @param array $results     Aggregated results from ResultsCalculator
     * @param array $patients    Array of Patient objects
     * @return string Complete QRDA III XML
     */
    private function generateConsolidatedXml($measureObjs, $results, $patients)
    {
        $organizationInfo = $this->getOrganizationInfo();
        $documentId = $this->generateUuid();
        $currentDateTime = date('YmdHis');
        $reportingPeriod = trim($GLOBALS['cqm_performance_period'] ?? '2023');

        // XML Header
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:hl7-org:v3" xmlns:voc="urn:hl7-org:v3/voc">

<!-- QRDA Header -->
<realmCode code="US"/>
<typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
<templateId root="2.16.840.1.113883.10.20.27.1.1" extension="2017-06-01"/>
<templateId root="2.16.840.1.113883.10.20.27.1.2" extension="2021-07-01"/>
<id root="{$documentId}"/>
<code code="55184-6" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Quality Reporting Document Architecture Calculated Summary Report"/>
<title>QRDA Calculated Summary Report - Consolidated {$reportingPeriod}</title>
<effectiveTime value="{$currentDateTime}"/>
<confidentialityCode code="N" codeSystem="2.16.840.1.113883.5.25"/>
<languageCode code="en"/>
<versionNumber value="1"/>
<recordTarget>
  <patientRole>
    <id nullFlavor="NA"/>
  </patientRole>
</recordTarget>
<author>
  <time value="{$currentDateTime}"/>
  <assignedAuthor>
    <id extension="{$organizationInfo['npi']}" root="2.16.840.1.113883.4.6"/>
    <addr>
      <streetAddressLine>{$this->escapeXml($organizationInfo['address']['street'])}</streetAddressLine>
      <city>{$this->escapeXml($organizationInfo['address']['city'])}</city>
      <state>{$this->escapeXml($organizationInfo['address']['state'])}</state>
      <postalCode>{$organizationInfo['address']['zip']}</postalCode>
      <country>{$organizationInfo['address']['country']}</country>
    </addr>
    <telecom use="WP" value="tel:(555)555-5555"/>
    <assignedAuthoringDevice>
      <manufacturerModelName>OpenEMR</manufacturerModelName>
      <softwareName>OpenEMR CQM</softwareName>
    </assignedAuthoringDevice>
    <representedOrganization>
      <id root="2.16.840.1.113883.19.5" extension="{$organizationInfo['tin']}"/>
      <n>{$this->escapeXml($organizationInfo['name'])}</n>
    </representedOrganization>
  </assignedAuthor>
</author>
<custodian>
  <assignedCustodian>
    <representedCustodianOrganization>
      <id extension="{$organizationInfo['tin']}" root="2.16.840.1.113883.4.336"/>
      <n>{$this->escapeXml($organizationInfo['name'])}</n>
      <telecom use="WP" value="tel:(555)555-5555"/>
      <addr>
        <streetAddressLine>{$this->escapeXml($organizationInfo['address']['street'])}</streetAddressLine>
        <city>{$this->escapeXml($organizationInfo['address']['city'])}</city>
        <state>{$this->escapeXml($organizationInfo['address']['state'])}</state>
        <postalCode>{$organizationInfo['address']['zip']}</postalCode>
        <country>{$organizationInfo['address']['country']}</country>
      </addr>
    </representedCustodianOrganization>
  </assignedCustodian>
</custodian>

XML;

        // Measure Section with ALL measures
        $xml .= $this->generateConsolidatedMeasureSection($measureObjs, $results, $patients);

        // XML Footer
        $xml .= <<<XML
</ClinicalDocument>
XML;

        return $xml;
    }

    /**
     * Generate the measure section containing all measures
     */
    private function generateConsolidatedMeasureSection($measureObjs, $results, $patients)
    {
        $reportingPeriod = trim($GLOBALS['cqm_performance_period'] ?? '2023');

        $xml = <<<XML
  <component>
    <structuredBody>
      <component>
        <section>
          <templateId root="2.16.840.1.113883.10.20.24.2.2"/>
          <templateId extension="2017-06-01" root="2.16.840.1.113883.10.20.27.2.1"/>
          <templateId extension="2019-05-01" root="2.16.840.1.113883.10.20.27.2.3"/>
          <code code="55186-1" codeSystem="2.16.840.1.113883.6.1"/>
          <title>Measure Section - Consolidated Report {$reportingPeriod}</title>
          <text>
            <table border="1" width="100%">
              <thead>
                <tr>
                  <th>eMeasure Title</th>
                  <th>CMS ID</th>
                  <th>Version specific identifier</th>
                  <th>Total Patients</th>
                </tr>
              </thead>
              <tbody>

XML;

        // Table rows for all measures
        foreach ($measureObjs as $measure) {
            $xml .= <<<XML
                <tr>
                  <td>{$this->escapeXml($measure->title)}</td>
                  <td>{$measure->cms_id}</td>
                  <td>{$measure->hqmf_id}</td>
                  <td>{count($patients)}</td>
                </tr>

XML;
        }

        $xml .= <<<XML
              </tbody>
            </table>
          </text>

XML;

        // Individual measure entries - this is the key part!
        foreach ($measureObjs as $measure) {
            $measureResults = $results[$measure->hqmf_id] ?? [];
            $xml .= $this->generateConsolidatedMeasureEntry($measure, $measureResults);
        }

        // Reporting Parameters
        $xml .= $this->generateReportingParameters();

        $xml .= <<<XML
        </section>
      </component>
    </structuredBody>
  </component>

XML;

        return $xml;
    }

    /**
     * Generate individual measure entry for consolidated report
     */
    private function generateConsolidatedMeasureEntry($measure, $measureResults)
    {
        $entryId = $this->generateUuid();

        $xml = <<<XML
          <entry>
            <organizer classCode="CLUSTER" moodCode="EVN">
              <templateId root="2.16.840.1.113883.10.20.24.3.98"/>
              <templateId extension="2016-09-01" root="2.16.840.1.113883.10.20.27.3.1"/>
              <templateId extension="2019-05-01" root="2.16.840.1.113883.10.20.27.3.17"/>
              <id extension="{$entryId}" root="1.3.6.1.4.1.115"/>
              <statusCode code="completed"/>
              <reference typeCode="REFR">
                <externalDocument classCode="DOC" moodCode="EVN">
                  <id extension="{$measure->hqmf_id}" root="2.16.840.1.113883.4.738"/>
                  <text>{$this->escapeXml($measure->title)}</text>
                  <setId root="{$measure->hqmf_set_id}"/>
                </externalDocument>
              </reference>

XML;

        // Population data - iterate through population sets and stratifications
        foreach ($measure->population_sets_and_stratifications_for_measure() as $populationSetHash) {
            $setKey = $measure->key_for_population_set($populationSetHash);
            $setResults = $measureResults[$setKey] ?? [];

            $xml .= $this->generatePopulationComponents($measure, $setResults);
        }

        $xml .= <<<XML
            </organizer>
          </entry>

XML;

        return $xml;
    }

    /**
     * Generate population components for a measure
     */
    private function generatePopulationComponents($measure, $results)
    {
        $xml = '';
        $populationKeys = $measure->population_keys();

        foreach ($populationKeys as $popKey) {
            $count = $results[$popKey] ?? 0;
            $popCode = $this->getPopulationCode($popKey);
            $popId = $this->generateUuid();

            $xml .= <<<XML
                <component>
                  <observation classCode="OBS" moodCode="EVN">
                    <templateId root="2.16.840.1.113883.10.20.27.3.5" extension="2016-09-01"/>
                    <templateId root="2.16.840.1.113883.10.20.27.3.16" extension="2019-05-01"/>
                    <code code="ASSERTION" codeSystem="2.16.840.1.113883.5.4" displayName="Assertion" codeSystemName="ActCode"/>
                    <statusCode code="completed"/>
                    <value xsi:type="CD" code="{$popCode}" codeSystem="2.16.840.1.113883.5.4" codeSystemName="ActCode"/>
                    <entryRelationship typeCode="SUBJ" inversionInd="true">
                      <observation classCode="OBS" moodCode="EVN">
                        <templateId root="2.16.840.1.113883.10.20.27.3.3"/>
                        <code code="MSRAGG" displayName="rate aggregation" codeSystem="2.16.840.1.113883.5.4" codeSystemName="ActCode"/>
                        <value xsi:type="INT" value="{$count}"/>
                        <methodCode code="COUNT" displayName="Count" codeSystem="2.16.840.1.113883.5.84" codeSystemName="ObservationMethod"/>
                      </observation>
                    </entryRelationship>
                    <reference typeCode="REFR">
                       <externalObservation classCode="OBS" moodCode="EVN">
                          <id root="{$popId}"/>
                       </externalObservation>
                    </reference>
                  </observation>
                </component>

XML;
        }

        return $xml;
    }

    /**
     * Generate reporting parameters
     */
    private function generateReportingParameters()
    {
        $parametersId = $this->generateUuid();
        $reportingPeriod = trim($GLOBALS['cqm_performance_period'] ?? '2023');

        return <<<XML
          <entry>
            <act classCode="ACT" moodCode="EVN">
              <templateId root="2.16.840.1.113883.10.20.17.3.8"/>
              <id extension="{$parametersId}" root="1.3.6.1.4.1.115"/>
              <code code="252116004" codeSystem="2.16.840.1.113883.6.96" displayName="Observation Parameters"/>
              <effectiveTime>
                <low value="{$reportingPeriod}0101000000"/>
                <high value="{$reportingPeriod}1231235959"/>
              </effectiveTime>
            </act>
          </entry>

XML;
    }

    /**
     * Helper methods
     */
    private function getOrganizationInfo()
    {
        return [
            'name' => $GLOBALS['openemr_name'] ?? 'OpenEMR Practice',
            'npi' => $GLOBALS['practice_npi'] ?? '1234567890',
            'tin' => $GLOBALS['practice_tin'] ?? '123456789',
            'address' => [
                'street' => $GLOBALS['practice_street'] ?? '123 Medical Way',
                'city' => $GLOBALS['practice_city'] ?? 'Medical City',
                'state' => $GLOBALS['practice_state'] ?? 'NY',
                'zip' => $GLOBALS['practice_zip'] ?? '12345',
                'country' => 'US'
            ]
        ];
    }

    private function getPopulationCode($popKey)
    {
        $codes = [
            'IPP' => 'IPOP',
            'DENOM' => 'DENOM',
            'NUMER' => 'NUMER',
            'NUMEX' => 'NUMEX',
            'DENEX' => 'DENEX',
            'DENEXCEP' => 'DENEXCEP',
            'MSRPOPL' => 'MSRPOPL',
            'MSRPOPLEX' => 'MSRPOPLEX'
        ];

        return $codes[$popKey] ?? $popKey;
    }

    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    private function escapeXml($content)
    {
        return htmlspecialchars((string) $content, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function do_calculation($patients, $measures)
    {
        return $this->CqmExecutionCalcExecute($patients, $measures);
        /**
         * measures = product_test.measures
         * calc_job = Cypress::CqmExecutionCalc.new(patients.map(&:qdmPatient), measures, correlation_id,
         * effectiveDate: Time.at(product_test.measure_period_start).in_time_zone.to_formatted_s(:number))
         * calc_job.execute
         */
    }

    private function CqmExecutionCalcExecute($patients, $measures)
    {
        $finalResults = [];
        foreach ($measures as $measure) {
            $results = $this->request_for($patients, $measure);
            // we deviate from the ruby code so we can group these by measure id since we aren't using a database
            $finalResults[$measure->hqmf_id] = $results;
        }
        return $finalResults;
        /**
         * def initialize(patients, measures, correlation_id, options)
         *
         * @patients            = patients
         * # This is a key -> value pair of patients mapped in the form "qdm-patient-id" => BSON::ObjectId("cqm-patient-id")
         * @cqm_patient_mapping = patients.map { |patient| [patient.id.to_s, patient.cqmPatient] }.to_h
         * @measures            = measures
         * @correlation_id      = correlation_id
         * @options             = options
         * end
         *
         * def execute(save: true)
         * @measures            .map        do |measure|
         * request_for(measure, save: save)
         * end.flatten
         * end
         */
    }

    private function request_for($patients, Measure $measure)
    {

        $results = $this->calculator->calculateMeasure($patients, $measure, $this->effectiveDate, $this->effectiveDateEnd);
        $final_results = [];
        foreach ($results as $patient_id => $result) {
            // we will deviate here as we don't need the patient as we aren't saving any data for cypress with the patient
            // need to unconvert from our hex format here
            $aggregated_results = $this->aggregate_population_results_from_individual_results($result, $patient_id, $measure);
            $final_results = array_merge($final_results, $aggregated_results);
        }

        // note we aren't saving data to the database and so we are foregoing the hash return here.
        // Cypress runs a query on all IndividualResults conected to the measure_id && correlation_id which we are
        // going to skip over and just return an array of all of the results from our aggregation
        // which ends up being our individual results list per measure.
        return $final_results;

        /**
         * def request_for(measure, save: true)
         * ir_list = []
         *
         * @options['requestDocument'] = true
         * post_data = { patients: @patients, measure: measure, valueSets: measure.value_sets, options: @options }
         * # cqm-execution-service expects a field called value_set_oids which is really just our
         * # oids field. There is a value_set_oids on the measure for this explicit purpose.
         * post_data = post_data.to_json(methods: %i[_type])
         * begin
         * response = RestClient::Request.execute(method: :post, url: self.class.create_connection_string, timeout: 120,
         * payload: post_data, headers: { content_type: 'application/json' })
         * rescue StandardError => e
         * raise e.to_s || 'Calculation failed without an error message'
         * end
         * results = JSON.parse(response)
         *
         * patient_result_hash = {}
         * results.each do |patient_id, result|
         * # Aggregate the results returned from the calculation engine for a specific patient.
         * # If saving the individual results, update identifiers (patient id, population_set_key) in the individual result.
         * aggregate_population_results_from_individual_results(result, @cqm_patient_mapping[patient_id], save, ir_list)
         * patient_result_hash[patient_id] = result.values
         * end
         * measure.calculation_results.create(ir_list) if save
         * patient_result_hash.values
         * end
         */
    }

    private function aggregate_population_results_from_individual_results($individual_results, $patient_id, Measure $measure)
    {
        $results = [];
        foreach ($individual_results as $population_set_key => $individual_result) {
            $individual_result['population_set_key'] = $population_set_key;
            $individual_result['patient_id'] = $patient_id;
            $results[] = new IndividualResult($individual_result, $measure);
        }
        return $results;
        /**
         * def aggregate_population_results_from_individual_results(individual_results, patient, save, ir_list)
         * individual_results.each_pair do |population_set_key, individual_result|
         * # store the population_set within the indivdual result
         * individual_result['population_set_key'] = population_set_key
         * # update the patient_id to match the cqm_patient id, not the qdm_patient id
         * individual_result['patient_id'] = patient.id.to_s
         * # save to database (if in the IPP)
         * ir_list << postprocess_individual_result(individual_result) if save && individual_result.IPP != 0
         * # update the patients, measure_relevance_hash
         * patient.update_measure_relevance_hash(individual_result) if individual_result.IPP != 0
         * end
         * patient.save if save
         * end
         */
    }

    /**
     * Used for logging out the IPP, DENOM, NUMER, DENEXCEP commands to the error log if debug logging is turned on
     * This can be quickly seen in a grid format by running the following command from inside a docker container
     * tail -f /var/log/apache2/error.log | cut -c 100-
     *
     * Future debugging could store these in a database file or something else for easier debugging.
     *
     * @param $patients
     * @param $results
     * @throws \Exception
     */
    private function logCalculationResults($patients, $results)
    {
        $logger = new SystemLogger();
        $patientsById = [];
        foreach ($patients as $patient) {
            $patientsById[$patient->id->value] = $patient;
        }
        foreach ($results as $result) {
            $resultPatient = $patientsById[$result[0]->patient_id->value] ?? new Patient();
            $innerResult = $result[0]->getInnerResult();

            $resultString = [str_pad("Patient: " . implode(" ", $resultPatient->patientName), 30)];
            $resultString[] = str_pad("IPP: " . ($innerResult['IPP'] ?? 0), 10);
            $resultString[] = str_pad("DENOM: " . ($innerResult['DENOM'] ?? 0), 10);
            $resultString[] = str_pad("NUMER: " . ($innerResult['NUMER'] ?? 0), 10);
            $resultString[] = str_pad("DENEXCEP: " . ($innerResult['DENEXCEP'] ?? 0), 10);
            $logger->debug(implode(" ", $resultString));
        }
    }
}
