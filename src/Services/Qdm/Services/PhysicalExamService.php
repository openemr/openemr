<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
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

    /**
     * A number is a quantity, so a BMI of 18.7 is 18.7, not 18. A value that is
     * not a number is no measurement: the exam's coded result if it has one,
     * otherwise no result. A quantity with no value would stop the measure from
     * being calculated at all, and a 0 would count as a blood pressure reading.
     */
    public function makeResult(mixed $record): Quantity|Code|null
    {
        if (!is_array($record)) {
            return null;
        }
        $value = $record['ob_value'] ?? null;
        if (is_numeric($value)) {
            return new Quantity([
                'value' => (float)$value,
                'unit' => $record['ob_unit']
            ]);
        }
        return $this->makeQdmCode($record['ob_code'] ?? null);
    }
}
