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

use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
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
}
