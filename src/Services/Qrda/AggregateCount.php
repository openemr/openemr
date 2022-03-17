<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;


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
            if ($ps['population_set_id'] == $cache_entry->pop_set_hash['population_set_id']) {
                $population_set = $ps;
                break;
            }
        }
        $entry_populations = [];
        foreach(['IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX'] as $pop_code) {
            if (!isset($population_set->populations[$pop_code])) {
                continue;
            }

            $population = $this->create_population_from_population_set($pop_code, $population_set, $cache_entry);
            if ($cache_entry->pop_set_hash['stratification_id']) {
                // TODO
                 // strat_id = population_set.stratifications.where(stratification_id: cache_entry.pop_set_hash[:stratification_id]).first&.hqmf_id
                 // observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
                 // population.add_stratification(strat_id,cache_entry[pop_code], observation)

            } else {
                // TODO
                // population.value = cache_entry[pop_code]
                // population.observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
                // population.supplemental_data = cache_entry.supplemental_data[pop_code]
            }

            $entry_populations[] = $population;
        }

        // TODO
        // return if population_groups.find {|pg| pg.populations.collect(&:id).compact.sort == entry_populations.collect(&:id).compact.sort }

        $population_group = new PopulationGroup();
        $population_group->populations = $entry_populations;
        $this->population_groups[] = $population_group;
    }

    public function create_population_from_population_set($pop_code, $population_set, $cache_entry)
    {
        // TODO Do we need the caching stuff?

        $population = new Population();
        $population->type = $pop_code;
        $population->id = $population_set->populations[$pop_code]->hqmf_id;
        $this->populations[] = $population;
        return $population;
    }
}
