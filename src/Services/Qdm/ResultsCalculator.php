<?php

/**
 * // @see projectcypress/cypress lib/cypress/expected_results_calculator.rb
 *
 * @package   OpenEMR
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

    // @param [Array] patients the list of patients that are included in the aggregate results
    // @param [String] correlation_id the id used to associate a group of patients
    // @param [String] effective_date used when generating the query_cache_object for HDS QRDA Cat III export
    // @param [Hash] options :individual_results are the raw results from CqmExecutionCalc
    public function __construct(array $patients, $correlation_id, $effective_date)
    {
        $this->correlation_id = $correlation_id;
        // Hash of patient_id and their supplemental information
        $this->patient_sup_map = [];
        $this->measure_result_hash = [];
        $this->effective_date = $effective_date;
        foreach ($patients as $patient) {
            $this->add_patient_to_sup_map($patient);
        }
    }

    public function add_patient_to_sup_map(Patient $patient)
    {
        $patient_id = $patient->id->value;
        $this->patient_sup_map[$patient_id] = [];
        $this->patient_sup_map[$patient_id]['SEX'] = $patient->extract_first_code('patient_characteristic', 'gender');
        $this->patient_sup_map[$patient_id]['RACE'] = $patient->extract_first_code('patient_characteristic', 'race');
        $this->patient_sup_map[$patient_id]['ETHNICITY'] = $patient->extract_first_code('patient_characteristic', 'ethnicity');
        $this->patient_sup_map[$patient_id]['PAYER'] = $patient->extract_first_code('patient_characteristic', 'payer');
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
        $this->measure_result_hash[$measure->hqmf_id] = [];
        $population_set_keys = array_map(
            function ($ps) use ($measure) {
                return $measure->key_for_population_set($ps);
            },
            $measure->population_sets_and_stratifications_for_measure()
        );
        foreach ($population_set_keys as $psk) {
            $this->measure_result_hash[$measure->hqmf_id][$psk] = [];
            foreach ($measure->population_keys() as $pop_key) {
                $this->measure_result_hash[$measure->hqmf_id][$psk][$pop_key] = 0;
            }
            $this->measure_result_hash[$measure->hqmf_id][$psk]['supplemental_data'] = [];
            $this->measure_result_hash[$measure->hqmf_id][$psk]['observations'] = [];
        }
    }

    /**
     * @param  array $measures
     * @param  array $individual_results
     * @return mixed
     *
     * Ported from Ryby
     * https://github.com/projectcypress/cypress/blob/master/lib/cypress/expected_results_calculator.rb
     */
    public function aggregate_results_for_measures(array $measures, array $individual_results)
    {
        foreach ($measures as $measure) {
            if (!($measure instanceof Measure)) {
                // TODO: Log this if for some reason we don't have a measure here.
                continue;
            }
            $this->prepopulate_measure_result_hash($measure);
            $measure_individual_results = null;
            // If individual_results are provided, use the results for the measure being aggregated
            foreach ($individual_results as $measureId => $results) {
                if ($measureId == $measure->hqmf_id) {
                    $measure_individual_results = $results;
                    break;
                }
            }

            // If individual_results are provided, use them.  Otherwise, look them up in the database by measure id and correlation_id
            // TODO not storing results in DB
            // measure_individual_results ||= CQM::IndividualResult.where('measure_id' => measure._id, correlation_id: @correlation_id)

            $this->aggregate_results_for_measure($measure, $measure_individual_results);
        }
        return $this->measure_result_hash;
    }

    public function aggregate_results_for_measure(Measure $measure, $individual_results)
    {
        // TODO: not storing results in DB
        // # If individual_results are provided, use them.  Otherwise, look them up in the database by measure id and correlation_id
        //          individual_results ||= CQM::IndividualResult.where('measure_id' => measure._id, correlation_id: @correlation_id)
        $observation_hash = array();
        foreach ($individual_results as $individual_result) {
            // type safety and let's us get intellisense
            if (!($individual_result instanceof IndividualResult)) {
                continue;
            }
            $key = $individual_result->population_set_key;
            foreach ($measure->population_keys() as $pop) {
                if (empty($individual_result->{$pop})) {
                    continue;
                }
                $this->measure_result_hash[$measure->hqmf_id][$key][$pop] += $individual_result->{$pop};
                $this->increment_sup_info(
                    $this->patient_sup_map[$individual_result->patient_id->value],
                    $pop,
                    $this->measure_result_hash[$measure->hqmf_id][$key]
                );
            }

            if (empty($individual_result->episode_results)) {
                continue;
            }
            $agg_results = true;
            // note this is passed by reference...
            $individual_result->collect_observations($observation_hash, $agg_results);
        }

        foreach ($this->measure_result_hash[$measure->hqmf_id] as $key => $data) {
            $this->calculate_observation($observation_hash, $measure, $key);
            $this->measure_result_hash[$measure->hqmf_id][$key]['measure_id'] = $measure->hqmf_id;
            $this->measure_result_hash[$measure->hqmf_id][$key]['pop_set_hash'] = $measure->population_set_hash_for_key($key);
        }


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

    public function increment_sup_info($patient_sup, $pop, array &$single_measure_result_hash)
    {
        if (!is_array($single_measure_result_hash['supplemental_data'][$pop])) {
            $single_measure_result_hash['supplemental_data'][$pop] = ['RACE' => [], 'ETHNICITY' => [], 'SEX' => [], 'PAYER' => []];
        }
        foreach ($patient_sup as $sup_type => $code) {
            $this->add_or_increment_code($pop, $sup_type, $code, $single_measure_result_hash);
        }
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

    public function add_or_increment_code($pop, $sup_type, $code, array &$single_measure_result_hash)
    {
        if (!empty($single_measure_result_hash['supplemental_data'][$pop][$sup_type][$code])) {
            $single_measure_result_hash['supplemental_data'][$pop][$sup_type][$code] += 1;
        } else {
            $single_measure_result_hash['supplemental_data'][$pop][$sup_type][$code] = 1;
        }
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

    public function calculate_observation(array $observation_hash, Measure $measure, string $population_set_key)
    {
        $key = $population_set_key;
        // do nothing if we don't have the population_set_key in our hash
        if (empty($observation_hash[$key])) {
            return;
        }
        foreach ($observation_hash[$key] as $population => $observation_map) {
            // nothing to aggregate, so continue
            if (empty($observation_map['statement_name'])) {
                continue;
            }
            /**
             * @var PopulationSet
             */
            $pop_set = $measure->population_set_for_key($key);
            $pop_set = reset($pop_set); // grab the first item
            $observation = null;
            foreach ($pop_set->observations as $obs) {
                if ($obs['observation_parameter']['statement_name'] == $observation_map['statement_name']) {
                    $observation = $obs;
                    break;
                }
            }
            // algorithm assumes $observation was found
            $value = 0;
            $array_values = array_values($observation_map);
            switch ($observation['aggregation_type']) {
                case 'COUNT':
                    // original projectcypress algorithm still counted null values.  Is that correct?
                    $value = $this->count($array_values);
                    break;
                case 'MEDIAN':
                    // remove any values that are null
                    $value = $this->median($array_values);
                    break;
                case 'SUM':
                    // only sum up non-null values, then we reduce the array by summing up each value
                    $value = $this->sum($array_values);
                    break;
            }
            $this->measure_result_hash[$measure->hqmf_id][$key]['observations'][$population] = [
                'value' => $value
                , 'method' => $observation['aggregation_type']
                , 'hqmf_id' => $observation['hqmf_id']
            ];
        }

        /**
         * # Calculate the aggregate observation totals for the values in an observation_hash
         * # these aggregate totals will be added to the appropriate measure/popuation in the @measure_result_hash
         * def calculate_observation(observation_hash, measure, population_set_key)
         * key = population_set_key
         * return unless observation_hash[key]
         *
         * # calculate the aggregate observation based on the aggregation type
         * # aggregation type is looked up using the statement_name
         * observation_hash[key].each do |population, observation_map|
         * next unless observation_map[:statement_name]
         *
         * pop_set = measure.population_set_for_key(key).first
         * # find observation that matches the statement_name
         * observation = pop_set.observations.select { |obs| obs.observation_parameter.statement_name == observation_map[:statement_name] }.first
         * # Guidance for calculations can be found here
         * # https://www.hl7.org/documentcenter/public/standards/vocabulary/vocabulary_tables/infrastructure/vocabulary/ObservationMethod.html#_ObservationMethodAggregate
         * case observation.aggregation_type
         * when 'COUNT'
         *
         * @measure_result_hash[measure.hqmf_id][key]['observations'][population] = { value: count(observation_map[:values].map(&:value)),
         * method: 'COUNT', hqmf_id: observation.hqmf_id }
         * when 'MEDIAN'
         * median_value = median(observation_map[:values].map(&:value).reject(&:nil?))
         * @measure_result_hash[measure.hqmf_id][key]['observations'][population] = { method: 'MEDIAN', hqmf_id: observation.hqmf_id,
         * value: median_value }
         * when 'SUM'
         * @measure_result_hash[measure.hqmf_id][key]['observations'][population] = { value: sum(observation_map[:values].map(&:value)),
         * method: 'SUM', hqmf_id: observation.hqmf_id }
         * end
         * end
         * end
         */
    }

    private function count(array $arr)
    {
        return count($this->filter_null_values($arr));

        /*
         *
         *

        def count(array)
          array.compact.size
        end

         */
    }

    private function sum(array $arr)
    {
        return array_reduce(
            $this->filter_null_values($arr),
            function ($sum, $item) {
                return $sum + $item;
            },
            0
        );

        /*
         *     def sum(array)
                    array.inject(0.0) { |sum, elem| sum + elem }
                end
         */
    }

    private function median(array $arr)
    {
        $value = 0.0;

        $sortedValues = asort($this->filter_null_values($arr));
        $count = count($sortedValues);
        if ($count > 0) {
            $midpoint = (int)($count / 2);
            if ($count % 2 == 0) {
                $second_midpoint = $midpoint - 1;
                $value = ($sortedValues[$midpoint] + $sortedValues[$second_midpoint]) / 2;
            } else {
                $value = $sortedValues[$midpoint];
            }
        }
        return $value;
        /*
         *
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

    private function filter_null_values(array $arr)
    {
        return array_filter('isset', $arr);
    }
}
