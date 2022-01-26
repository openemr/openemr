<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Search\TokenSearchField;

class ConditionService extends AbstractQdmService implements QdmServiceInterface
{
    public function executeQuery()
    {
    }

    /**
     * Map an OpenEMR record into a QDM model
     *
     * This
     *
     * @param array $record
     * @return Diagnosis|null
     * @throws \Exception
     */
    public function makeQdmModel(array $record)
    {
        $qdmRecord = null;
        if ($record['type'] === 'medical_problem') {
            if ($record['diagnosis']) {
                // Get diagnosis
                $qdmRecord = new Diagnosis([
                    'prevalencePeriod' => new Interval([
                        'low' => $record['begdate'],
                        'high' => $record['enddate'],
                        'lowClosed' => $record['begdate'] ? true : false,
                        'highClosed' => $record['enddate'] ? true : false]),
                    'dataElementCodes' => $record['diagnosis'] // diagnosis contains code and system
                ]);
            }
        }

        return $qdmRecord;
    }
}
