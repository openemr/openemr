<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\PhysicalExamPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class PhysicalExamService extends AbstractObservationService implements QdmServiceInterface
{
    public function getObservationType(): string
    {
        return parent::OB_TYPE_PHYSICAL_EXAM;
    }

    public function getModelClass(): string
    {
        return PhysicalExamPerformed::class;
    }

    public function makeResult($record): Quantity
    {
        // A BMI of 18.7 is 18.7, not 18; a value that isn't a number is no result, not 0
        // (a blood pressure of 0 would count as controlled).
        $value = $record['ob_value'] ?? null;
        return new Quantity([
            'value' => is_numeric($value) ? (float)$value : null,
            'unit' => $record['ob_unit']
        ]);
    }
}
