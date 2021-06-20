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
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class UtilsService
{
    public static function createRelativeReference($type, $uuid)
    {
        $reference = new FHIRReference();
        $reference->setType($type);
        $reference->setReference($type . "/" . $uuid);
        return $reference;
    }

    public static function createCodeableConcept(array $diagnosisCodes, $codeSystem): FHIRCodeableConcept
    {
        $diagnosisCoding = new FHIRCoding();
        $diagnosisCode = new FHIRCodeableConcept();
        foreach ($diagnosisCodes as $code => $display) {
            if (!is_string($code)) {
                $code = "$code"; // FHIR expects a string
            }
            $diagnosisCoding->setCode($code);
            $diagnosisCoding->setDisplay($display);
            $diagnosisCoding->setSystem($codeSystem);
            $diagnosisCode->addCoding($diagnosisCoding);
        }
        return $diagnosisCode;
    }

    public static function createDataMissingExtension()
    {
        // @see http://hl7.org/fhir/us/core/general-guidance.html#missing-data
        // for some reason in order to get this to work we have to wrap our inner exception
        // into an outer exception.  This might be just a PHPism with the way JSON encodes things
        $extension = new FHIRExtension();
        $extension->setUrl(FhirCodeSystemUris::DATA_ABSENT_REASON);
        $extension->setValueCode(new FHIRCode("unknown"));
        $outerExtension = new FHIRExtension();
        $outerExtension->addExtension($extension);
        return $outerExtension;
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
}
