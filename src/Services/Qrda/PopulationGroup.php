<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qrda\Helpers\PopulationSelectors;

class PopulationGroup
{
    use PopulationSelectors;

    public $populations = [];

    public function performance_rate()
    {
        // TODO no idea where the numerator_count comes from
        // numerator_count.to_f / (performance_rate_denominator)
    }

    public function performance_rate_denominator()
    {
        // TODO no idea where the denominator_count comes from
        // denominator_count - denominator_exclusions_count - denominator_exceptions_count
    }

    public function is_cv()
    {
        foreach ($this->populations as $population) {
            if ($population->type == 'MSRPOPL') {
                return true;
            }
        }
        return false;
        // populations.any? {|pop| pop.type == 'MSRPOPL'}
    }
}
