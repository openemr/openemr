<?php

/**
 * FhirServiceRequestSerializer.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Serialization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class FhirServiceRequestSerializer
{
    /**
     * Takes a FHIR JSON array representing a ServiceRequest and returns the populated FHIRServiceRequest resource.
     *
     * The FHIR R4 constructors/setters do not recursively hydrate nested elements
     * (e.g., CodeableConcept.coding stays as raw arrays). This method explicitly
     * constructs typed FHIR objects for all nested elements that parseFhirResource accesses.
     *
     * @param array<string, mixed> $fhirJson
     * @return FHIRServiceRequest
     */
    public static function deserialize(array $fhirJson): FHIRServiceRequest
    {
        // Extract fields that need manual hydration (constructors don't convert nested arrays to typed objects)
        /** @var array<int, array<string, mixed>> $category */
        $category = $fhirJson['category'] ?? [];
        /** @var array<int, array<string, mixed>> $reasonCode */
        $reasonCode = $fhirJson['reasonCode'] ?? [];
        /** @var array<int, array<string, mixed>> $note */
        $note = $fhirJson['note'] ?? [];
        /** @var array<int, array<string, mixed>> $performer */
        $performer = $fhirJson['performer'] ?? [];
        /** @var array<string, mixed>|null $code */
        $code = $fhirJson['code'] ?? null;
        /** @var array<string, mixed>|null $subject */
        $subject = $fhirJson['subject'] ?? null;
        /** @var array<string, mixed>|null $encounter */
        $encounter = $fhirJson['encounter'] ?? null;
        /** @var array<string, mixed>|null $requester */
        $requester = $fhirJson['requester'] ?? null;

        unset(
            $fhirJson['category'],
            $fhirJson['reasonCode'],
            $fhirJson['note'],
            $fhirJson['performer'],
            $fhirJson['code'],
            $fhirJson['subject'],
            $fhirJson['encounter'],
            $fhirJson['requester']
        );

        $serviceRequest = new FHIRServiceRequest($fhirJson);

        // Hydrate CodeableConcept arrays with proper FHIRCoding objects
        foreach ($category as $item) {
            $serviceRequest->addCategory(self::hydrateCodeableConcept($item));
        }
        foreach ($reasonCode as $item) {
            $serviceRequest->addReasonCode(self::hydrateCodeableConcept($item));
        }
        foreach ($note as $item) {
            $serviceRequest->addNote(new FHIRAnnotation($item));
        }
        foreach ($performer as $item) {
            $serviceRequest->addPerformer(new FHIRReference($item));
        }

        // Hydrate single-value fields
        if (is_array($code)) {
            $serviceRequest->setCode(self::hydrateCodeableConcept($code));
        }
        if (is_array($subject)) {
            $serviceRequest->setSubject(new FHIRReference($subject));
        }
        if (is_array($encounter)) {
            $serviceRequest->setEncounter(new FHIRReference($encounter));
        }
        if (is_array($requester)) {
            $serviceRequest->setRequester(new FHIRReference($requester));
        }

        return $serviceRequest;
    }

    /**
     * Creates a FHIRCodeableConcept with properly typed FHIRCoding elements.
     *
     * @param array<string, mixed> $data
     * @return FHIRCodeableConcept
     */
    private static function hydrateCodeableConcept(array $data): FHIRCodeableConcept
    {
        /** @var array<int, array<string, mixed>> $codings */
        $codings = $data['coding'] ?? [];
        unset($data['coding']);

        $concept = new FHIRCodeableConcept($data);
        foreach ($codings as $codingData) {
            $concept->addCoding(new FHIRCoding($codingData));
        }
        return $concept;
    }
}
