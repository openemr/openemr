<?php

/**
 * This file was adapted from the following file: https://github.com/projectcypress/cypress/blob/v6.2.2.1/lib/ext/individual_result.rb
 * But I believe it should rather be adapted from this project: https://github.com/projecttacoma/cqm-models/blob/master/app/models/cqm/individual_result.rb
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Cqm\Qdm\Identifier;
use OpenEMR\Services\Qdm\Services\PatientService;

class IndividualResult extends AbstractType
{
    /**
     * @var Identifier
     */
    public $patient_id;

    public $episode_results;

    public $population_set_key;

    /**
     * @var Measure
     */
    public $measure;

    protected $_result;

    /**
     * IndividualResult constructor.
     *
     * @param $_result
     */
    public function __construct($_result, $measure)
    {
        parent::__construct($_result);
        $this->patient_id = PatientService::makeQdmIdentifier('System', PatientService::convertIdFromBSONObjectIdFormat($_result['patient_id'] ?? null));
        $this->_result = $_result;
        $this->measure = $measure;
    }

    public function getInnerResult()
    {
        return $this->_result;
    }

    public function observed_values()
    {
        /*
         *     def observed_values
        return nil unless episode_results&.values&.any? { |er| er.key?('observation_values') }

        episode_results.values.map(&:observation_values)
        end
         */
    }

    public function compare_results($calculated, $options, $previously_passed)
    {
        /*
         *     def compare_results(calculated, options, previously_passed)
        issues = []
        if calculated.nil?
        [true && previously_passed, issues]
        else
        comp = true
        %w[IPP DENOM NUMER DENEX DENEXCEP MSRPOPL MSRPOPLEXCEP].each do |pop|
          original_value, calculated_value, pop = extract_calcuated_and_original_results(calculated, pop)
          next unless original_value != calculated_value

          pop_statement = options[:population_set].populations[pop].hqmf_id
          pop_statement << " Stratification #{options[:stratification_id]}" if options[:stratification_id]
          issues << "Calculated value (#{calculated_value}) for #{pop} (#{pop_statement}) does not match expected value (#{original_value})"
          comp = false
        end
        APP_CONSTANTS['result_measures'].each do |result_measure|
          compare_statement_results(calculated, result_measure['statement_name'], issues) if measure.hqmf_id == result_measure['hqmf_id']
        end
        compare_observations(calculated, issues) if observed_values
        [previously_passed && comp, issues]
        end
        end
         */
    }

    public function extract_calcuated_and_original_results($calculated, $pop)
    {
        /*
         *     def extract_calcuated_and_original_results(calculated, pop)
        # set original value to 0 if it wasn't calculated
        original_value = self[pop].nil? ? 0.0 : self[pop]
        # set calculated value to 0 if there is no calculation for the measure or population
        calculated_value = calculated.nil? || calculated[pop].nil? ? 0.0 : calculated[pop]
        if pop == 'values'
        pop = 'OBSERV'
        # the orginal and calculated values should be an array make empty if it doesn't exist
        original_value = [] unless original_value.is_a?(Array)
        calculated_value = [] if calculated_value.nil? || !calculated_value.is_a?(Array)
        end
        [original_value, calculated_value, pop]
        end
         */
    }

    public function compare_observations($calculated, $issues = [])
    {
        /*
         *     def compare_observations(calculated, issues = [])
        # If there aren't any calculated episode_results, use an empty array for comparison
        calculated_er = calculated['episode_results'] ? calculated['episode_results'].values.map(&:observation_values).sort : []
        expected_er = episode_results.values.map(&:observation_values).sort

        return unless calculated_er != expected_er

        issues << "Calculated observations (#{calculated_er}) do not match "\
                "expected observations (#{expected_er})"
        end
         */
    }

    /**
     * For the given population set that this individual result represents we add all of our observations for each individual
     * population in the set to the observation_hash array.
     *
     * @param  array $observation_hash The observations array we are adding addiitonal information to.
     * @param  bool  $agg_results
     * @return array|void
     */
    public function collect_observations(&$observation_hash = [], $agg_results = false)
    {
        if (empty($this->episode_results)) {
            return;
        }
        $population_keys = $this->measure->population_keys();
        $key = $agg_results ? $this->population_set_key : $this->patient_id->value;
        $this->setup_observation_hash($observation_hash, $key);
        // reset grabs first item.
        $pop_sets = $this->measure->population_set_for_key($this->population_set_key);
        $population_set = reset($pop_sets);
        // collect the observation_statements for the population_set. There may be more than one. episode_results are recorded in the same order
        $observation_statements = array_map(
            function ($obs) {
                return $obs['observation_parameter']['statement_name'];
            },
            $population_set->observations // was this->population_set
        );
        // collect the observation_values from and individual_result
        // a scenario with multiple episodes and multiple observations would look like this [[2], [9, 1]]
        // remove any empty values
        $obs_values_array = $this->get_observ_values($this->episode_results) ?? [];
        $observation_values = array_filter($obs_values_array, function ($a) {
            return isset($a);
        });
        foreach ($observation_values as $episode_index => $observation_value) {
            foreach ($observation_value as $observation => $index) {
                $obs_pop = null;
                foreach ($population_keys as $pop) {
                    if ($population_set->populations[$pop]['statement_name'] == $observation_statements[$index]) {
                        $obs_pop = $pop;
                        break;
                    }
                }
                $observation_hash[$key][$obs_pop]['values'][] = ['episode_index' => $episode_index, 'value' => $observation];
                $observation_hash[$key][$obs_pop]['statement_name'][] = $observation_statements[$index];
            }
        }
        return $observation_hash;
        /*
         *     # adds the observation values found in an individual_result to the observation_hash
        # Set agg_results to true if you are collecting aggregate results for multiple patients
        #
        # Below is an example hash for an individual (the hash key is the patient id)
        # {BSON::ObjectId('60806298c1c388315523be47')=>{"IPP"=>{:values=>[]},
        # "MSRPOPL"=>{:values=>[{:episode_index=>0, :value=>75}, {:episode_index=>1, :value=>50}], :statement_name=>"Measure Population"},
        # "MSRPOPLEX"=>{:values=>[]}}}

        # Below is an example hash for aggregate results (the hash keys are the population set keys)
        # {"PopulationSet_1"=>{"IPP"=>{:values=>[]},
        # "DENOM"=>{:values=>[{:episode_index=>0, :value=>9}, {:episode_index=>0, :value=>2}, :statement_name=>"Denominator"},
        # "NUMER"=>{:values=>[]}}}
        def collect_observations(observation_hash = {}, agg_results: false)
        return unless episode_results

        key = agg_results ? population_set_key : patient_id
        setup_observation_hash(observation_hash, key)
        population_set = measure.population_set_for_key(population_set_key).first
        # collect the observation_statements for the population_set. There may be more than one. episode_results are recorded in the same order
        observation_statements = population_set.observations.map { |obs| obs.observation_parameter.statement_name }
        # collect the observation_values from and individual_result
        # a scenario with multiple episodes and multiple observations would look like this [[2], [9, 1]]
        observation_values = get_observ_values(episode_results).compact
        observation_values.each_with_index do |observation_value, episode_index|
        observation_value.each_with_index do |observation, index|
          # lookup the population code (e.g., DENOM is the population code for the statement named 'Denominator')
          obs_pop = measure.population_keys.find { |pop| population_set.populations[pop]['statement_name'] == observation_statements[index] }
          # add the observation to the hash
          observation_hash[key][obs_pop][:values] << { episode_index: episode_index, value: observation }
          observation_hash[key][obs_pop][:statement_name] = observation_statements[index]
        end
        end
        observation_hash
        end
         */
    }

    public function setup_observation_hash($observation_hash, $key)
    {
        /*
         *     def setup_observation_hash(observation_hash, key)
        observation_hash[key] = {} unless observation_hash[key]
        measure.population_keys.each do |pop|
        observation_hash[key][pop] = { values: [] } unless observation_hash[key][pop]
        end
        end
         */
    }

    public function get_observ_values($episode_results)
    {
        /*
         *     def get_observ_values(episode_results)
        episode_results.collect do |_id, episode_result|
        # Only use observed values when a patient is in the MSRPOPL and not in the MSRPOPLEX
        next unless (episode_result['MSRPOPL']&.positive? && !episode_result['MSRPOPLEX']&.positive?) || episode_result['MSRPOPL'].nil?

        episode_result['observation_values']
        end
        end

         */
    }

    public function compare_statement_results($calculated, $statement_name, $issues = [])
    {
        /*
         *     def compare_statement_results(calculated, statement_name, issues = [])
        combined_statement_results = gather_statement_results(calculated, statement_name)
        combined_statement_results.each do |csr|
        # if original and reported match, move on
        next unless csr[:original] != csr[:reported]

        # if the original value is nil, and a value is reported, return error message
        if csr[:original].nil? || csr[:original].empty?
          issues << "#{csr[:name]} not expected"
          next
        end
        issues << if csr[:reported].nil? || csr[:reported].empty?
                    original_vals = csr[:original].map { |o| "#{o['value']} #{o['unit']}" }.join(', ')
                    "#{csr[:name]} of [#{original_vals}] is missing"
                  else
                    reported_vals = csr[:reported].map { |r| "#{r['value']} #{r['unit']}" }.join(', ')
                    original_vals = csr[:original].map { |o| "#{o['value']} #{o['unit']}" }.join(', ')
                    "#{csr[:name]} of [#{original_vals}] does not match [#{reported_vals}]"
                  end
        end
        end
         */
    }

    public function gather_statement_results($calculated, $statement_name)
    {
        /*
         *     # Helper method that compiles an array with the orignial and reported value for each result type.
        def gather_statement_results(calculated, statement_name)
        return [] if statement_results.blank?

        original_statement_results = statement_results.select { |sr| sr['statement_name'] == statement_name }.first['raw']
        calculated_statement_results = calculated['statement_results'].select { |sr| sr['statement_name'] == statement_name }.first['raw']
        combined_statement_results = []
        original_statement_results.each do |result_name, value|
        next if calculated_statement_results[result_name].empty?

        original_values = value.map(&:FirstResult).compact.empty? ? [] : value.map(&:FirstResult).compact&.sort_by! { |fr| fr['value'] }
        statement_result_hash = { name: result_name,
                                  original: original_values,
                                  reported: calculated_statement_results[result_name].map(&:FirstResult).compact&.sort_by! { |fr| fr['value'] } }
        combined_statement_results << statement_result_hash
        end
        combined_statement_results
        end
         */
    }
}
