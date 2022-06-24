<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

trait PopulationSelectors
{
    public function numerator()
    {
        // populations.find {|pop| pop.type == 'NUMER'}
        foreach ($this->populations as $population) {
            if ($population->type == 'NUMER') {
                return true;
            }
        }
        return false;
    }

    public function denominator()
    {
        // populations.find {|pop| pop.type == 'DENOM'}
        foreach ($this->populations as $population) {
            if ($population->type == 'DENOM') {
                return true;
            }
        }
        return false;
    }

    public function denominator_exceptions()
    {
        // populations.find {|pop| pop.type == 'DENEXCEP'}
        foreach ($this->populations as $population) {
            if ($population->type == 'DENEXCEP') {
                return true;
            }
        }
        return false;
    }

    public function denominator_exclusions()
    {
        // populations.find {|pop| pop.type == 'DENEX'}
        foreach ($this->populations as $population) {
            if ($population->type == 'DENEX') {
                return true;
            }
        }
        return false;
    }

    public function population_count($population_type, $population_id)
    {
        //           population = populations.find {|pop| pop.type == population_type && pop.id == population_id}
        //          if population
        //            population.value
        //          else
        //            0
        //          end
        $found = 0;
        foreach ($this->populations as $population) {
            if (
                $population->type == $population_type
                && $population->id == $population_id
            ) {
                $found = $population->value;
                break;
            }
        }
        return $found;
    }

    public function population_id($population_type)
    {
        // populations.find {|pop| pop.type == population_type}.id
        foreach ($this->populations as $population) {
            if ($population->type == $population_type) {
                return $population->id;
            }
        }
        return false;
    }

    // TODO how are these implemented?

    //        def method_missing(method, *args, &block)
    //          match_data = method.to_s.match(/^(.+)_count$/)
    //          if match_data
    //            population = send(match_data[1])
    //            if population
    //              population.value
    //            else
    //              0
    //            end
    //          else
    //            super
    //          end
    //        end
    //
    //        def respond_to_missing?(method, *args)
    //          match_data = method.to_s.match(/^(.+)_count$/)
    //          !match_data.nil? or super
    //        end
    //
    //        # Returns true if there is more than one IPP or DENOM, etc.
    //        def multiple_population_types?
    //          population_groups = populations.group_by(&:type)
    //          population_groups.values.any? { |pops| pops.size > 1 }
    //        end
}
