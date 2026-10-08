<?php

/**
 * FhirServiceBaseEmptyTrait provides default service methods for when a FHIR service class is implementing
 * only a single or subset of service methods. Search reports an internal error, provenance is unavailable, and the write
 * methods throw.  At some point we may want to consider refactoring the FHIRServiceBase
 * class to make these methods not required.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Validators\ProcessingResult;

trait FhirServiceBaseEmptyTrait
{
    protected function loadSearchParameters(): array
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

    public function parseFhirResource($fhirResource = []): array
    {
        throw $this->writeNotSupported();
    }

    public function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        throw $this->writeNotSupported();
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        throw $this->writeNotSupported();
    }
    public function createProvenanceResource($dataRecord = []): ?FHIRProvenance
    {
        return null;
    }

    /**
     * This is typed mixed because the FhirServiceBase has a docbock type
     * that's incompatible with a couple of the implementations, and Rector L10
     * types require _something_ to be present.
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): mixed
    {
        return null;
    }

    private function writeNotSupported(): \BadMethodCallException
    {
        return new \BadMethodCallException(static::class . ' does not support writes');
    }
}
