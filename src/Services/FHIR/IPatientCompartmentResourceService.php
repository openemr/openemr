<?php

/**
 * IFhirExportableResourceService defines the methods a Fhir Resource Service must implement if the resource service
 * interacts with patient data.  It prevents patient data from leaking by making sure that when we are inside a patient
 * context we return the search field that we can bind a search to and make sure ONLY that patient is returned.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\Search\FhirSearchParameterDefinition;

interface IPatientCompartmentResourceService
{
    public function getPatientContextSearchField(): FhirSearchParameterDefinition;
}
