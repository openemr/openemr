<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class ResultsCalculator
{
    protected $correlation_id;
    protected $patient_sup_map;
    protected $measure_result_hash;
    protected $effective_date;

    # @param [Array] patients the list of patients that are included in the aggregate results
    # @param [String] correlation_id the id used to associate a group of patients
    # @param [String] effective_date used when generating the query_cache_object for HDS QRDA Cat III export
    # @param [Hash] options :individual_results are the raw results from CqmExecutionCalc
    public function __construct(array $patients, $correlation_id, $effective_date)
    {
        $this->correlation_id = $correlation_id;
        # Hash of patient_id and their supplemental information
        $this->patient_sup_map = [];
        $this->measure_result_hash = [];
        $this->effective_date = $effective_date;
        foreach ($patients as $patient) {
            $this->add_patient_to_sup_map($patient);
        }
    }

    public function add_patient_to_sup_map(Patient $patient)
    {
        $patient_id = $patient['id'];
        $this->patient_sup_map[$patient_id] = [];
        $sex = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'gender')), true);
        $this->patient_sup_map[$patient_id]['SEX'] = $sex['code'];
        $race = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'race')), true);
        $this->patient_sup_map[$patient_id]['RACE'] = $race['code'];
        $ethnicity = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'ethnicity')), true);
        $this->patient_sup_map[$patient_id]['ETHNICITY'] = $ethnicity['code'];
        $payer = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'payer')), true);
        $this->patient_sup_map[$patient_id]['PAYER'] = $payer['code'];
    }

    //https://github.com/projectcypress/cypress/blob/ef09517b96b269c60671e3af651eb52df2ff22fa/lib/ext/measure.rb#L27

    public function prepopulate_measure_result_hash(Measure $measure)
    {
        /*
         *     def prepopulate_measure_result_hash(measure)
          measure_result_hash[measure.hqmf_id] = {}
          population_set_keys = measure.population_sets_and_stratifications_for_measure.map { |ps| measure.key_for_population_set(ps) }
          population_set_keys.each do |psk|
            @measure_result_hash[measure.hqmf_id][psk] = {}
            measure.population_keys.each do |pop_key|
              @measure_result_hash[measure.hqmf_id][psk][pop_key] = 0
            end
            @measure_result_hash[measure.hqmf_id][psk]['supplemental_data'] = {}
            @measure_result_hash[measure.hqmf_id][psk]['observations'] = {}
          end
        end
         */
        $this->measure_result_hash[$measure['hqmf_id']] = [];

    }

    /**
     * @param array $measures
     * @param array $individual_results
     * @return mixed
     *
     * Ported from Ryby
     * https://github.com/projectcypress/cypress/blob/master/lib/cypress/expected_results_calculator.rb
     */
    public function aggregate_results_for_measures(array $measures, array $individual_results)
    {
        foreach ($measures as $measure) {
            $this->prepopulate_measure_result_hash($measure);
            $measure_individual_results = null;
            # If individual_results are provided, use the results for the measure being aggregated
            foreach ($individual_results as $res) {
                if ($res['measure_id'] == $measure['id']) {
                    $measure_individual_results = $res;
                    break;
                }
            }

            # If individual_results are provided, use them.  Otherwise, look them up in the database by measure id and correlation_id
            // TODO not storing results in DB
            // measure_individual_results ||= CQM::IndividualResult.where('measure_id' => measure._id, correlation_id: @correlation_id)

            $this->aggregate_results_for_measure($measure, $measure_individual_results);
        }
        return $this->measure_result_hash;
    }

    public function aggregate_results_for_measure($measure, $individual_results)
    {
        /*
         * def aggregate_results_for_measure(measure, individual_results = nil)
          # If individual_results are provided, use them.  Otherwise, look them up in the database by measure id and correlation_id
          individual_results ||= CQM::IndividualResult.where('measure_id' => measure._id, correlation_id: @correlation_id)

          # The observation_hash is used to collect all of the observation values for each population_set and population.
          # Format is the following {"PopulationSet_1"=>{"MSRPOPL"=>{:values=>[75, 15, 50, 50], :statement_name=>"Measure Population"}}}
          observation_hash = {}
          # Increment counts for each measure_populations in each individual_result
          individual_results.each do |individual_result|
            key = individual_result['population_set_key']
            measure.population_keys.each do |pop|
              next if individual_result[pop].nil? || individual_result[pop].zero?

              @measure_result_hash[measure.hqmf_id][key][pop] += individual_result[pop]
              # For each population, increment supplemental information counts
              increment_sup_info(@patient_sup_map[individual_result.patient_id.to_s], pop, @measure_result_hash[measure.hqmf_id][key])
            end
            # extract the observed value from an individual results.  Observed values are in the 'episode result'.
            # Each episode will have its own observation
            next unless individual_result['episode_results']

            individual_result.collect_observations(observation_hash, agg_results: true)
          end
          @measure_result_hash[measure.hqmf_id].each_key do |key|
            calculate_observation(observation_hash, measure, key)
            @measure_result_hash[measure.hqmf_id][key]['measure_id'] = measure.hqmf_id
            @measure_result_hash[measure.hqmf_id][key]['pop_set_hash'] = measure.population_set_hash_for_key(key)
          end
        end
         */
    }

    public function increment_sup_info($patient_sup, $pop, $single_measure_result_hash)
    {
        /*
         *     def increment_sup_info(patient_sup, pop, single_measure_result_hash)
          # If supplemental_data for a population does not already exist, create a new hash
          unless single_measure_result_hash['supplemental_data'][pop]
            single_measure_result_hash['supplemental_data'][pop] = { 'RACE' => {}, 'ETHNICITY' => {}, 'SEX' => {}, 'PAYER' => {} }
          end
          patient_sup.each_key do |sup_type|
            # For each type of supplemental data (e.g., RACE, SEX), increment code values
            add_or_increment_code(pop, sup_type, patient_sup[sup_type], single_measure_result_hash)
          end
        end
        */
    }

    public function add_or_increment_code($pop, s$up_type, $code, $single_measure_result_hash)
    {
        /*
        def add_or_increment_code(pop, sup_type, code, single_measure_result_hash)
          # If the code already exists for the meausure_population, increment.  Otherwise create a hash for the code, start at 1
          if single_measure_result_hash['supplemental_data'][pop][sup_type][code]
            single_measure_result_hash['supplemental_data'][pop][sup_type][code] += 1
          else
            single_measure_result_hash['supplemental_data'][pop][sup_type][code] = 1
          end
        end
         */
    }

    /*
     *
     *
    def sum(array)
      array.inject(0.0) { |sum, elem| sum + elem }
    end

    def count(array)
      array.compact.size
    end

    def mean(array)
      return 0.0 if array.empty?

      array.inject(0.0) { |sum, elem| sum + elem } / array.size
    end

    def median(array, already_sorted: false)
      return 0.0 if array.empty?

      array = array.sort unless already_sorted
      m_pos = array.size / 2
      array.size.odd? ? array[m_pos] : mean(array[m_pos - 1..m_pos])
    end
     */
}
