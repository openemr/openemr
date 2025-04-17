<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

class Stratification
{
    public $id;
    public $value;
    public $observation;

    /**
     * Stratification constructor.
     *
     * @param $id
     * @param $value
     * @param $observation
     */
    public function __construct($id, $value, $observation)
    {
        $this->id = $id;
        $this->value = $value;
        $this->observation = $observation;
    }
}
