<?php

/**
 * FhirServiceBaseEmptyTrait is used to provide default empty service methods for when a FHIR service class is implementing
 * only a single or subset of service methods.  At some point we may want to consider refactoring the FHIRServiceBase
 * class to make these methods not required.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Validators\ProcessingResult;

trait FhirServiceBaseEmptyTrait
{
    protected function loadSearchParameters()
    {
        return [];
    }
    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $processingResult->setInternalErrors(['Search not implemented']);
        return $processingResult;
    }

    public function parseFhirResource($fhirResource = [])
    {
        return;
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        return;
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        return;
    }
    public function createProvenanceResource($dataRecord = [], $encode = false)
    {
        return;
    }

    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        return null;
    }
}
