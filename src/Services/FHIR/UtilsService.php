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

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddressType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddressUse;
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
use OpenEMR\Services\Utils\DateFormatterUtils;

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
        $addressPeriod = new FHIRPeriod();

        if (!empty($dataRecord['type'])) {
            // TODO: do we want to do any validation on this?  If people add 'types' we will have issues, downside is a code change if we need to support newer standards
            $address->setType(new FHIRAddressType(['value' => $dataRecord['type']]));
        }
        if (!empty($dataRecord['use'])) {
            // TODO: do we want to do any validation on this?  If people add 'uses' we will have issues, downside is a code change if we need to support newer standards
            $address->setUse(new FHIRAddressUse(['value' => $dataRecord['use']]));
        }

        if (!empty($dataRecord['period_start'])) {
            $date = DateFormatterUtils::dateStringToDateTime($dataRecord['period_start']);
            if ($date === false) {
                (new SystemLogger())->errorLogCaller(
                    "Failed to format date record with date format ",
                    ['start' => $dataRecord['period_start'], 'contact_address_id' => ($dataRecord['contact_address_id'] ?? null)]
                );
                $date = new \DateTime('now', new \DateTimeZone(date('P')));
            }
            $addressPeriod->setStart($date->format(\DateTime::RFC3339_EXTENDED));
        } else {
            // we should always have a start period, but if we don't, we will go one year before
            $start = new \DateTime();
            $start->sub(new \DateInterval('P1Y')); // subtract one year
            $addressPeriod->setStart(new FHIRDateTime($start->format(\DateTime::RFC3339_EXTENDED)));
        }

        if (!empty($dataRecord['period_end'])) {
            $date = DateFormatterUtils::dateStringToDateTime($dataRecord['period_end']);
            if ($date === false) {
                (new SystemLogger())->errorLogCaller(
                    "Failed to format date record with date format ",
                    ['date' => $dataRecord['period_end'], 'contact_address_id' => ($dataRecord['contact_address_id'] ?? null)]
                );
                $date = new \DateTime();
            }
            // if we have an end date we need to set our use to be old
            // TODO: when FHIR R4 5.0.1 is the standard, it has proposed to go off the 'end' date instead of the use column for an old address
            // for ONC R4 3.1.1 we have to populate the use column as old (which removes the fact that the address was a 'home' or a 'work' address)
            $addressPeriod->setEnd($date->format(\DateTime::RFC3339_EXTENDED));
            $address->setUse(new FHIRAddressUse(['value' => ContactAddress::USE_OLD]));
        }

        $address->setPeriod($addressPeriod);
        $hasAddress = false;
        $line1 = $dataRecord['line1'] ?? $dataRecord['street'] ?? null;
        if (!empty($line1)) {
            $address->addLine($line1);
            $hasAddress = true;
        }

        $line2 = $dataRecord['line2'] ?? $dataRecord['street_line_2'] ?? null;
        if (!empty($line2)) {
            $address->addLine($line2);
        }

        if (!empty($dataRecord['city'])) {
            $address->setCity($dataRecord['city']);
            $hasAddress = true;
        }
        $district = $dataRecord['county'] ?? $dataRecord['district'] ?? null;
        if (!empty($district)) {
            $address->setDistrict($district);
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

    public static function getDateFormattedAsUTC(): string
    {
        return (new \DateTime())->format(DATE_ATOM);
    }

    public static function getLocalDateAsUTC($date)
    {
        // make this assumption explicit that we are using the current timezone specified in PHP
        // when we use strtotime or gmdate we get bad behavior when dealing with DST
        // we really should be storing dates internally as UTC instead of local time... but until that happens we have
        // to do this.
        // note this is what we were using before
        // $date = gmdate('c', strtotime($dataRecord['date']));
        // w/ DST the date 2015-06-22 00:00:00 server time becomes 2015-06-22T04:00:00+00:00 w/o DST the server time becomes 2015-06-22T00:00:00-04:00
        $date = new \DateTime($date, new \DateTimeZone(date('P')));
        $utcDate = $date->format(DATE_ATOM);
        return $utcDate;
    }
}
