<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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
    public function getObservationType()
    {
        return parent::OB_TYPE_PHYSICAL_EXAM;
    }

    public function getModelClass()
    {
        return PhysicalExamPerformed::class;
    }

    public function makeResult($record)
    {
        return new Quantity([
            'value' => (int)$record['ob_value'],
            'unit' => $record['ob_unit']
        ]);
    }
}
