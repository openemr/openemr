<?php

/**
 * FhirPractitionerSerializer.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Serialization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationRequestIntent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantityComparator;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRange;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUnitsOfTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;

class FhirMedicationRequestSerializer
{
    public static function serialize(FHIRMedicationRequest $object)
    {
        return $object->jsonSerialize();
    }

    // TODO: @adunsulag this is very painful to hydrate our objects.  It would be better if we used something like
    // the symfony serializer @see https://symfony.com/doc/current/components/serializer.html#deserializing-in-an-existing-object
    // If we want to start type safing things a LOT more and working with objects instead of arrays we could include that
    // library, but for this one off... perhaps we don't need it so much.  Once we start dealing with POST/PUT its going
    // to become a much bigger deal to go from JSON to type safed objects especially in terms of error elimination...
    /**
     * Takes a fhir json representing an organization and returns the populated the resource
     * @param $fhirJson
     * @return FHIRMedicationRequest
     */
    public static function deserialize($fhirJson)
    {
        $identifiers = $fhirJson['identifier'] ?? [];
        $categories = $fhirJson['category'] ?? [];
        $intent = $fhirJson['intent'];
        $medicationCodeableConcept = $fhirJson['medicationCodeableConcept'];
        $subject = $fhirJson['subject'];
        $encounter = $fhirJson['encounter'];
        $authoredOn = $fhirJson['authoredOn'];
        $requester = $fhirJson['requester'];
        unset($fhirJson['identifier']);
        unset($fhirJson['category']);
        unset($fhirJson['intent']);
        unset($fhirJson['medicationCodeableConcept']);
        unset($fhirJson['subject']);
        unset($fhirJson['encounter']);
        unset($fhirJson['authoredOn']);
        unset($fhirJson['requester']);
        $medicationRequest = new FHIRMedicationRequest($fhirJson);

        // note for the future reader
        // this is because the class constructor
        // didn't create the proper class/type it just assigns what get passed to it
        foreach ($identifiers as $item) {
            $medicationRequest->addIdentifier(self::parseJsonIdentifier($item));
        }

        // support only coding for now
        foreach ($categories as $category) {
            if ($category['coding']) {
                $medicationRequest->addCategory(self::parseJsonCodeableConcept($category));
            }
        }

        if (!empty($intent)) {
            if (is_string($intent)) {
                $intent = ['value' => $intent];
            }

            $medicationRequest->setIntent(new FHIRMedicationRequestIntent($intent));
        }

        // TODO maybe I should implement some kind of validation here
        if (!empty($medicationCodeableConcept)) {
            $medicationRequest->setMedicationCodeableConcept(self::parseJsonCodeableConcept($medicationCodeableConcept));
        }

        if (!empty($subject)) {
            $medicationRequest->setSubject(self::parseJsonReference($subject));
        }

        if (!empty($encounter)) {
            $medicationRequest->setEncounter(self::parseJsonReference($encounter));
        }

        if (!empty($authoredOn)) {
            /**
             * refer to the comment in [[UtilsService::getLocalDateAsUTC]]
             */
            $medicationRequest->setAuthoredOn(new FHIRDateTime(date('Y-m-d H:i:s', strtotime($authoredOn))));
        }

        if (!empty($requester)) {
            $medicationRequest->setRequester(self::parseJsonReference($requester));
        }

        return $medicationRequest;
    }

    /**
     * @param array{use: string, type: array, system: string, value: string, period: array, assigner: array} $jsonIdentifier
     * @return FHIRIdentifier
     */
    static function parseJsonIdentifier(array $jsonIdentifier = [])
    {
        $type = $jsonIdentifier['type'] ?? [];
        $codeableConcept = self::parseJsonCodeableConcept($type);
        $identifier = new FHIRIdentifier();
        $identifier->setType($codeableConcept);
        // according to the fhir docs use is enum/code "use": "<usual | official | temp | secondary | old (If known)>"
        // but here in the FHIRIdentifierUse class, the constructor expects an array
        $use = new FHIRIdentifierUse();
        $use->setValue($jsonIdentifier['use']);
        $identifier->setUse($use);
        $identifier->setSystem(new FHIRUri($jsonIdentifier['system']));
        $identifier->setValue(new FHIRString($jsonIdentifier['value'] ?? []));
        $identifier->setPeriod(self::parseJsonPeriod($jsonIdentifier['period'] ?? []));
        return $identifier;
    }

    /**
     * @param array $codeableConcept
     * @return FHIRCodeableConcept
     */
    static function parseJsonCodeableConcept(array $codeableConcept)
    {
        $fhirCodeableConcept = new FHIRCodeableConcept();
        foreach ($codeableConcept['coding'] as $coding) {
            $fhirCodeableConcept->addCoding(new FHIRCoding($coding));
        }

        if (!empty($codeableConcept['text'])) {
            $fhirCodeableConcept->setText($codeableConcept['text']);
        }

        return $fhirCodeableConcept;
    }

    /**
     * @param array{start: string, end: string} $period
     * @return FHIRPeriod
     */
    static function parseJsonPeriod(array $period)
    {
        $fhirPeriod = new FHIRPeriod();
        $fhirPeriod->setStart(new FHIRDateTime($period['start']));
        $fhirPeriod->setEnd(new FHIRDateTime($period['end']));
        return $fhirPeriod;
    }

    /**
     * @param array{reference: string,type: string, display: string, identifier: array} $jsonReference
     * @return FHIRReference
     */
    static function parseJsonReference(array $jsonReference)
    {
        $reference = new FHIRReference();
        $reference->setReference(new FHIRString($jsonReference['reference']));
        $reference->setType(new FHIRUri($jsonReference['type']));
        $reference->setIdentifier(self::parseJsonIdentifier($jsonReference['identifier'] ?? []));
        $reference->setDisplay(new FHIRString($jsonReference['display']));
        return $reference;
    }
}
