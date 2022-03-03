<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;


class Context
{
    public function patient_characteristic_birthdate()
    {
        $elements = $this->patient->get_data_elements('patient_characteristic', 'birthdate');
        if (count($elements) === 1) {
            $birthDate = $elements[0];
        } else {
            throw new \Exception("ERROR: There can only be one birthdate element");
        }

        return $birthDate;
    }
}
