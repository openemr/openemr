<?php

/**
 * BundleTrait.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, M.D. <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <contact@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;

trait BundleTrait
{
    private function insert(FHIRBundle $object): ProcessingResult {
        foreach ($object->entry as $item) {
            $resource = $item->getResource();
            $service = match ($resource->_fhirElementName) {
                'Patient' => new FhirPatientService(),
                'Practitioner' => new PractitionerService(),
                'Organization' => new FhirOrganizationService()
            };

            $service->insert($resource);
        }
    }
}
