<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qdm\IndividualResult;
use OpenEMR\Services\Qdm\PopulationSet;

class AggregateCount
{
    public $measure_id;
    public $populations = [];
    public $population_groups = [];

    public function __construct($measure_id)
    {
        $this->measure_id = $measure_id;
    }

    public function add_entry($cache_entry, array $population_sets)
    {
        $population_set = null;
        foreach ($population_sets as $ps) {
            if ($ps->population_set_id == $cache_entry['pop_set_hash']['population_set_id']) {
                $population_set = $ps;
                break;
            }
        }
        $entry_populations = [];
        foreach (['IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX'] as $pop_code) {
            if (!isset($population_set->populations[$pop_code])) {
                continue;
            }

            $population = $this->create_population_from_population_set($pop_code, $population_set, $cache_entry);
            if ($cache_entry['pop_set_hash']['stratification_id']) {
                 // strat_id = population_set.stratifications.where(stratification_id: cache_entry.pop_set_hash[:stratification_id]).first&.hqmf_id
                 // observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
                 // population.add_stratification(strat_id,cache_entry[pop_code], observation)
                $strat_id = null;
                foreach ($population_set->stratifications as $stratification) {
                    if ($cache_entry['pop_set_hash']['stratification_id']) {
                        $strat_id = $stratification['hqmf_id'];
                    }
                }
                $observation = null;
                if ($cache_entry['observations'] && $cache_entry['observations'][$pop_code]) {
                    $observation = $cache_entry['observations'][$pop_code];
                }
                $population->add_stratification($strat_id, $cache_entry[$pop_code], $observation);
            } else {
                // population.value = cache_entry[pop_code]
                // population.observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
                // population.supplemental_data = cache_entry.supplemental_data[pop_code]
                $population->value = $cache_entry[$pop_code];
                if ($cache_entry['observations'] && $cache_entry['observations'][$pop_code]) {
                    $population->observation = $cache_entry['observations'][$pop_code];
                }
                $population->supplemental_data = $cache_entry['supplemental_data'][$pop_code];
            }

            $entry_populations[] = $population;
        }

        // See if we already have this population by checking to see that we have an existing population with all the same IDs
        // Ruby:
        // return if population_groups.find {|pg| pg.populations.collect(&:id).compact.sort == entry_populations.collect(&:id).compact.sort }
        $population_group = null;
        $idMapper = function ($item) {
            return $item->id;
        };
        foreach ($this->population_groups as $pg) {
            $diff = array_diff(array_map($idMapper, $pg->populations), array_map($idMapper, $entry_populations));
            if (count($diff) === 0) {
                $population_group = $pg;
                break;
            }
        }

        if ($population_group === null) {
            $population_group = new PopulationGroup();
            $population_group->populations = $entry_populations;
            $this->population_groups[] = $population_group;
        } else {
            return $population_group;
        }
    }

    public function create_population_from_population_set($pop_code, PopulationSet $population_set, $cache_entry)
    {
        // population = populations.find { |pop| pop.id == population_set.populations[pop_code]&.hqmf_id } if pop_code != 'STRAT'
        // return population unless population.nil? && !cache_entry.pop_set_hash[:stratification_id]
        $population = null;
        foreach ($this->populations as $pop) {
            if (
                $pop_code != 'STRAT'
                && $pop->id == $population_set->populations[$pop_code]['hqmf_id']
            ) {
                $population = $pop;
                break;
            }
        }

        if (
            $population !== null
            || !empty($cache_entry['pop_set_hash']['stratification_id'])
        ) {
            return $population;
        }

        $population = new Population();
        $population->type = $pop_code;
        $population->id = $population_set->populations[$pop_code]['hqmf_id'];
        $this->populations[] = $population;
        return $population;
    }

    public function is_cv()
    {
        foreach ($this->populations as $population) {
            if ($population->type == 'MSRPOPL') {
                return true;
            }
        }
        return false;
    }
}
