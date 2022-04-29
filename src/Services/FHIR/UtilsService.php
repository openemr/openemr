<?php

/**
 * UtilsService holds helper methods for dealing with fhir objects in the services  layer.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class UtilsService
{
    const UNKNOWNABLE_CODE_NULL_FLAVOR = "UNK";
    const UNKNOWNABLE_CODE_DATA_ABSENT = "unknown";

    public static function createRelativeReference($type, $uuid, $displayName = null)
    {
        $reference = new FHIRReference();
        $reference->setType($type);
        $reference->setReference($type . "/" . $uuid);
        if (!empty($displayName) && is_string($displayName)) {
            $reference->setDisplay($displayName);
        }
        return $reference;
    }

    public static function getUuidFromReference(FHIRReference $reference)
    {
        $uuid = null;
        if (!empty($reference->getReference())) {
            $parts = explode("/", $reference->getReference());
            $uuid = $parts[1] ?? null;
        }
        return $uuid;
    }

    public static function createQuantity($value, $unit, $code)
    {
        $quantity = new FHIRQuantity();
        $quantity->setCode($code);
        $quantity->setValue($value);
        $quantity->setUnit($unit);
        $quantity->setSystem(FhirCodeSystemConstants::UNITS_OF_MEASURE);
    }

    public static function createCoding($code, $display, $system): FHIRCoding
    {
        if (!is_string($code)) {
            $code = trim("$code"); // FHIR expects a string
        }
        // make sure there are no whitespaces.
        $coding = new FHIRCoding();
        $coding->setCode($code);
        $coding->setDisplay(trim($display ?? ""));
        $coding->setSystem(trim($system ?? ""));
        return $coding;
    }

    public static function createCodeableConcept(array $diagnosisCodes, $defaultCodeSystem = "", $defaultDisplay = ""): FHIRCodeableConcept
    {
        $diagnosisCode = new FHIRCodeableConcept();
        foreach ($diagnosisCodes as $code => $codeValues) {
            $codeSystem = $codeValues['system'] ?? $defaultCodeSystem;
            if (!empty($codeValues['description'])) {
                $diagnosisCode->addCoding(self::createCoding($code, $codeValues['description'], $codeSystem));
            } else {
                $diagnosisCode->addCoding(self::createCoding($code, $defaultDisplay, $codeSystem));
            }
        }
        return $diagnosisCode;
    }

    public static function createDataMissingExtension()
    {
        // @see http://hl7.org/fhir/us/core/general-guidance.html#missing-data
        // for some reason in order to get this to work we have to wrap our inner exception
        // into an outer exception.  This might be just a PHPism with the way JSON encodes things
        $extension = new FHIRExtension();
        $extension->setUrl(FhirCodeSystemConstants::DATA_ABSENT_REASON_EXTENSION);
        $extension->setValueCode(new FHIRCode("unknown"));
        $outerExtension = new FHIRExtension();
        $outerExtension->addExtension($extension);
        return $outerExtension;
    }

    public static function createContactPoint($value, $system, $use): FHIRContactPoint
    {
        $fhirContactPoint = new FHIRContactPoint();
        $fhirContactPoint->setSystem($system);
        $fhirContactPoint->setValue($value);
        $fhirContactPoint->setUse($use);
        return $fhirContactPoint;
    }

    public static function createAddressFromRecord($dataRecord): ?FHIRAddress
    {
        $address = new FHIRAddress();
        // TODO: we don't track start and end periods for dates so what value should go here...?
        $addressPeriod = new FHIRPeriod();
        $start = new \DateTime();
        $start->sub(new \DateInterval('P1Y')); // subtract one year
        $end = new \DateTime();
        $addressPeriod->setStart(new FHIRDateTime($start->format(\DateTime::RFC3339_EXTENDED)));
        // if there's an end date we provide one here, but for now we just go back one year
//        $addressPeriod->setEnd(new FHIRDateTime($end->format(\DateTime::RFC3339_EXTENDED)));
        $address->setPeriod($addressPeriod);
        $hasAddress = false;
        if (!empty($dataRecord['line1'])) {
            $address->addLine($dataRecord['line1']);
            $hasAddress = true;
        } else if (!empty($dataRecord['street'])) {
            $address->addLine($dataRecord['street']);
            $hasAddress = true;
        }

        if (!empty($dataRecord['line2'])) {
            $address->addLine($dataRecord['line2']);
        }

        if (!empty($dataRecord['city'])) {
            $address->setCity($dataRecord['city']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['state'])) {
            $address->setState($dataRecord['state']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['postal_code'])) {
            $address->setPostalCode($dataRecord['postal_code']);
            $hasAddress = true;
        }
        if (!empty($dataRecord['country_code'])) {
            $address->setCountry($dataRecord['country_code']);
            $hasAddress = true;
        }

        if ($hasAddress) {
            return $address;
        }
        return null;
    }

    public static function createFhirMeta($version, $date): FHIRMeta
    {
        $meta = new FHIRMeta();
        $meta->setVersionId($version);
        $meta->setLastUpdated($date);
        return $meta;
    }

    public static function createHumanNameFromRecord($dataRecord): FHIRHumanName
    {
        $name = new FHIRHumanName();
        $name->setUse('official');

        if (!empty($dataRecord['title'])) {
            $name->addPrefix($dataRecord['title']);
        }
        if (!empty($dataRecord['lname'])) {
            $name->setFamily($dataRecord['lname']);
        }

        if (!empty($dataRecord['fname'])) {
            $name->addGiven($dataRecord['fname']);
        }

        if (!empty($dataRecord['mname'])) {
            $name->addGiven($dataRecord['mname']);
        }
        return $name;
    }

    public static function createNullFlavorUnknownCodeableConcept()
    {
        return self::createCodeableConcept([
            self::UNKNOWNABLE_CODE_NULL_FLAVOR => [
                'code' => self::UNKNOWNABLE_CODE_NULL_FLAVOR
                ,'description' => 'unknown'
                ,'system' => FhirCodeSystemConstants::HL7_NULL_FLAVOR
            ]]);
    }

    public static function createDataAbsentUnknownCodeableConcept()
    {
        return self::createCodeableConcept(
            [self::UNKNOWNABLE_CODE_DATA_ABSENT => [
                'code' => self::UNKNOWNABLE_CODE_DATA_ABSENT
                , 'description' => 'Unknown'
                , 'system' => FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM
            ]]
        );
    }

    /**
     * Given a FHIRPeriod object return an array containing the timestamp in milliseconds of the start and end points
     * of the period.  If the passed in object is null it will return null values for the 'start' and 'end' properties.
     * If the start has no value or if the end period has no value it will return null values for the properties.
     * @param FHIRPeriod $period  The object representing the period interval.
     * @return array Containing two keys of 'start' and 'end' representing the period.
     */
    public static function getPeriodTimestamps(?FHIRPeriod $period)
    {
        $end = null;
        $start = null;
        if ($period !== null) {
            if (!empty($period->getEnd())) {
                $end = strtotime($period->getEnd()->getValue());
            }
            if (!empty($period->getStart())) {
                $start = strtotime($period->getStart()->getValue());
            }
        }
        return [
            'start' => $start,
            'end' => $end
        ];
    }

    public static function createNarrative($message, $status = "generated"): FHIRNarrative
    {
        $div = "<div xmlns='http://www.w3.org/1999/xhtml'>" . $message . "</div>";
        $narrative = new FHIRNarrative();
        $code = new FHIRCode();
        $code->setValue($status);
        $narrative->setStatus($code);
        $narrative->setDiv($div);
        return $narrative;
    }
}
