<?php
/**
 * FhirQuestionnaireResponseController class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\FhirQuestionnaireResponseService;
use OpenEMR\Services\FhirResourcesService;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirQuestionnaireResponseController
{
    private $questionnaireResponseService;
    private $fhirService;

    public function __construct()
    {
        $this->questionnaireResponseService = new FhirQuestionnaireResponseService();
        $this->fhirService = new FhirResourcesService();
    }
}
