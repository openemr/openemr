<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;


class Measure
{
    protected $_measure;

    /**
     * Measure constructor.
     * @param $measure
     *
     * wrap a CQM measure with some extra functionality for reports
     */
    public function __construct($measure)
    {
        $this->_measure = $measure;
    }

    public function population_sets_and_stratifications_for_measure()
    {
        /*
         *     # A measure may have 1 or more population sets that may have 1 or more stratifications
    # This method returns an array of hashes with the population_set and stratification_id for every combindation
    def population_sets_and_stratifications_for_measure
      population_set_array = []
      population_sets.each do |population_set|
        population_set_hash = { population_set_id: population_set.population_set_id }
        next if population_set_array.include? population_set_hash

        population_set_array << population_set_hash
        population_set.stratifications.each do |stratification|
          population_set_stratification_hash = { population_set_id: population_set.population_set_id,
                                                 stratification_id: stratification.stratification_id }
          population_set_array << population_set_stratification_hash
        end
      end
      population_set_array
    end
         */
    }

    public function population_set_for_key($population_set_key)
    {
        /*
         *     # This method returns the population_set for a given 'population_set_key.'  The popluation_set_key is the key used
    # by the cqm-execution-service to reference the population set for a specific set of calculation results
    def population_set_for_key(population_set_key)
      ps_hash = population_sets_and_stratifications_for_measure
      ps_hash.keep_if { |ps| [ps[:population_set_id], ps[:stratification_id]].include? population_set_key }
      return nil if ps_hash.blank?

      [population_sets.where(population_set_id: ps_hash[0][:population_set_id]).first, ps_hash[0][:stratification_id]]
    end
         */
    }

    public function population_set_hash_for_key($population_set_key)
    {
        /*
         *     # This method returns an population_set_hash (from the population_sets_and_stratifications_for_measure)
    # for a given 'population_set_key.' The popluation_set_key is the key used by the cqm-execution-service
    # to reference the population set for a specific set of calculation results
    def population_set_hash_for_key(population_set_key)
      population_set_hash = population_sets_and_stratifications_for_measure
      population_set_hash.keep_if { |ps| [ps[:population_set_id], ps[:stratification_id]].include? population_set_key }.first
    end
         */
    }

    public function key_for_population_set($population_set_hash)
    {
        /*
         *     # This method returns a popluation_set_key for.a given population_set_hash
    def key_for_population_set(population_set_hash)
      population_set_hash[:stratification_id] || population_set_hash[:population_set_id]
    end
         */
    }

    public function population_keys()
    {
        /*
         *     # This method returns the subset of population keys used in a specific measure
    def population_keys
      %w[IPP DENOM NUMER NUMEX DENEX DENEXCEP MSRPOPL MSRPOPLEX].keep_if { |pop| population_sets.any? { |ps| ps.populations[pop]&.hqmf_id } }
    end
         */
    }

}
