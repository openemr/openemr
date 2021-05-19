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
}
