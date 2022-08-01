<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Cqm\Qdm\Identifier;

class Measure extends AbstractType
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $cms_id;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $hqmf_id;

    /**
     * @var string
     */
    public $hqmf_set_id;

    /**
     * @var string
     */
    public $hqmf_version_number;

    /**
     * @var string
     */
    public $main_cql_library;

    /**
     * @var string
     */
    public $title;

    // TODO: are these composite used anywhere?
    public $composite;
    public $component;
    public $component_hqmf_set_ids;
    public $composite_hqmf_set_id;

    /**
     * @var PopulationSet[]
     */
    public $population_sets;

    /**
     * @var string
     */
    public $measure_path;

    public $calculation_method;

    /**
     * Measure constructor.
     *
     * @param $measure
     *
     * wrap a CQM measure with some extra functionality for reports
     */
    public function __construct($measure)
    {
        parent::__construct($measure);
        //$this->id = ($measure['_id'] ?? [])['oid'] ?? '';

        $this->calculation_method = 'EPISODE_OF_CARE';
        // CMS22v10 is EPISODE_OF_CARE, which seems to be default in measure file, but these measures
        // require PATIENT
        if (
            $measure['cms_id'] == 'CMS122v10' ||
            $measure['cms_id'] == 'CMS69v10' ||
            $measure['cms_id'] == 'CMS124v10' ||
            $measure['cms_id'] == 'CMS125v10' ||
            $measure['cms_id'] == 'CMS127v10' ||
            $measure['cms_id'] == 'CMS130v10' ||
            $measure['cms_id'] == 'CMS138v10' ||
            $measure['cms_id'] == 'CMS147v11' ||
            $measure['cms_id'] == 'CMS165v10'
        ) {
            $this->calculation_method = 'PATIENT';
        }

        //$this->_measure = $measure;
        $this->population_sets  = [];
        if ($measure['population_sets']) {
            foreach ($measure['population_sets'] as $population_set) {
                $this->population_sets[] = new PopulationSet($population_set);
            }
        }
    }

    public function population_sets_and_stratifications_for_measure()
    {
        $hashIdsSeen = [];
        $population_set_array = [];
        foreach ($this->population_sets as $population_set) {
            // got a duplicate population set so we skip
            // we do it this way since we can't compare object references like ruby include? can do.
            if (!empty($hashIdsSeen[$population_set->population_set_id])) {
                continue;
            }

            // TODO: do we need to convert population_set
            $population_set_hash = ["population_set_id" => $population_set->population_set_id];
            $hashIdsSeen[$population_set->population_set_id] = $population_set->population_set_id;

            $population_set_array[] = $population_set_hash;
            foreach ($population_set->stratifications as $stratification) {
                $population_set_stratification_hash = [
                    'population_set_id' => $population_set->population_set_id
                    // TODO: change this if we move stratification to an object
                    ,'stratification_id' => $stratification['stratification_id']
                ];
                $population_set[] = $population_set_stratification_hash;
            }
        }
        return $population_set_array;

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

    /**
     * @param  string $population_set_key
     * @return PopulationSet[]
     */
    public function population_set_for_key(string $population_set_key): ?array
    {
        $ps_hash = $this->population_sets_and_stratifications_for_measure();
        $ps_hash_keep = [];
        foreach ($ps_hash as $ps) {
            if ($ps['population_set_id'] == $population_set_key || $ps['stratification_id'] == $population_set_key) {
                $ps_hash_keep[] = $ps;
            }
        }
        if (empty($ps_hash_keep)) {
            return null;
        }
        $found_population_sets = [];
        foreach ($this->population_sets as $ps) {
            if ($ps->population_set_id == $ps_hash_keep[0]['population_set_id']) {
                $found_population_sets[] = $ps;
            }
        }

        return [$ps_hash[0]['stratification_id'] => $found_population_sets[0]];
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
        $population_set_hash = $this->population_sets_and_stratifications_for_measure();
        $filtered_set_hash = array_filter(
            $population_set_hash,
            function ($ps) use ($population_set_key) {
                $set_id = $ps['population_set_id'] ?? null;
                $strat_id = $ps['stratification_id'] ?? null;
                return $set_id == $population_set_key || $strat_id == $population_set_key;
            }
        );
        return reset($filtered_set_hash); // grab the first one
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
        return $population_set_hash['stratification_id'] ?? $population_set_hash['population_set_id'];
        /*
         *     # This method returns a popluation_set_key for.a given population_set_hash
        def key_for_population_set(population_set_hash)
        population_set_hash[:stratification_id] || population_set_hash[:population_set_id]
        end
         */
    }

    public function population_keys()
    {
        // we could probably cache this call if we needed to optimize...
        $popKeys = [];
        $keys = ["IPP", "DENOM", "NUMER", "NUMEX", "DENEX", "DENEXCEP", "MSRPOPL", "MSRPOPLEX"];
        foreach ($keys as $pop) {
            foreach ($this->population_sets as $ps) {
                if (!empty($ps->populations[$pop]['hqmf_id'])) {
                    $popKeys[] = $pop;
                    break;
                }
            }
        }
        return $popKeys;
        /*
         *     # This method returns the subset of population keys used in a specific measure
        def population_keys
        %w[IPP DENOM NUMER NUMEX DENEX DENEXCEP MSRPOPL MSRPOPLEX].keep_if { |pop| population_sets.any? { |ps| ps.populations[pop]&.hqmf_id } }
        end
         */
    }

    public function getJsonArrayDefinition()
    {
        // get our populated measure if we have one or return the json.
        return $this->_measure || $this->jsonSerialize();
    }
}
