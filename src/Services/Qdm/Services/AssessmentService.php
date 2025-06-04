<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\AssessmentPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class AssessmentService extends AbstractObservationService implements QdmServiceInterface
{
    public function getObservationType()
    {
        return parent::OB_TYPE_ASSESSMENT;
    }

    public function getModelClass()
    {
        return AssessmentPerformed::class;
    }
}
