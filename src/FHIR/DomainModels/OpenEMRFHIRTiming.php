<?php

/*
 * OpenEMRFHIRDosage.php  Extends FHIRDosage to cleanup the resource type on serialization
 * as it doesn't conform to the FHIR spec to have resourceType in in this object
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\DomainModels;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTiming;

class OpenEMRFHIRTiming extends FHIRTiming
{
    use FHIRDomainModelSerializationCleanupTrait;

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
