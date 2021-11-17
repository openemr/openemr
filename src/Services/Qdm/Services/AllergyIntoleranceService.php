<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\AllergyIntolerance;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Services\Search\TokenSearchField;

class AllergyIntoleranceService extends AbstractQdmService
{
    public function fetch()
    {

    }

    public function makeQdmModel(array $record)
    {
        $qdmRecord = null;
        if ($record['type'] === 'allergy') {
            $qdmRecord = new AllergyIntolerance([
                'prevalencePeriod' => new Interval([
                    'low' => $record['begdate'],
                    'high' => $record['enddate'],
                    'lowClosed' => $record['begdate'] ? true : false,
                    'highClosed' => $record['enddate'] ? true : false
                ])
            ]);
        }

        return $qdmRecord;
    }
}
