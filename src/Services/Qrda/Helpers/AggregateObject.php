<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

trait AggregateObject
{
}

/*
 *
 * module Qrda
  module Export
    module Helper




      class Stratification
        attr_accessor :id, :value, :observation
        def initialize(id,value, observation)
          @id = id
          @value = value
          @observation = observation
        end

      end

      class PopulationGroup
        include PopulationSelectors
        attr_accessor :populations
        def performance_rate
          numerator_count.to_f / (performance_rate_denominator)
        end

        def performance_rate_denominator
          denominator_count - denominator_exclusions_count - denominator_exceptions_count
        end

        def is_cv?
          populations.any? {|pop| pop.type == 'MSRPOPL'}
        end

      end

      class AggregateCount
        attr_accessor :measure_id,  :populations, :population_groups

        def initialize(measure_id)
          @populations = []
          @measure_id = measure_id
          @population_groups = []
        end

        def add_entry(cache_entry, population_sets)
          population_set = population_sets.where(population_set_id: cache_entry.pop_set_hash[:population_set_id]).first
          entry_populations = []
          %w[IPP DENOM NUMER NUMEX DENEX DENEXCEP MSRPOPL MSRPOPLEX].each do |pop_code|
            next unless population_set.populations[pop_code]

            population = create_population_from_population_set(pop_code, population_set, cache_entry)
            if cache_entry.pop_set_hash[:stratification_id]
              strat_id = population_set.stratifications.where(stratification_id: cache_entry.pop_set_hash[:stratification_id]).first&.hqmf_id
              observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
              population.add_stratification(strat_id,cache_entry[pop_code], observation)
            else
              population.value = cache_entry[pop_code]
              population.observation = cache_entry['observations'][pop_code] if cache_entry['observations'] && cache_entry['observations'][pop_code]
              population.supplemental_data = cache_entry.supplemental_data[pop_code]
            end
            entry_populations << population if population
          end
          return if population_groups.find {|pg| pg.populations.collect(&:id).compact.sort == entry_populations.collect(&:id).compact.sort }

          pg = PopulationGroup.new
          pg.populations = entry_populations
          population_groups << pg
        end

        def create_population_from_population_set(pop_code, population_set, cache_entry)
          population = populations.find { |pop| pop.id == population_set.populations[pop_code]&.hqmf_id } if pop_code != 'STRAT'
          return population unless population.nil? && !cache_entry.pop_set_hash[:stratification_id]

          population = Population.new
          population.type = pop_code
          population.id = population_set.populations[pop_code]&.hqmf_id
          populations << population
          population
        end

        def is_cv?
          populations.any? {|pop| pop.type == 'MSRPOPL'}
        end
      end
    end
  end
end

 */
