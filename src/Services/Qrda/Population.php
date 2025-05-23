<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

class Population
{
    public $type;
    public $value;
    public $id;
    public $stratifications = [];
    public $statifications_hash = [];
    public $supplemental_data;
    public $observation;

    public function add_stratification($id, $value, $observation)
    {
        if (!isset($this->statifications_hash[$id])) {
            $this->stratifications[] = new Stratification($id, $value, $observation);
            $this->statifications_hash[$id] = true;
        }
    }
}
