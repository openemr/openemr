<?php

/*
 * FHIRDomainModelSerializationCleanupTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\DomainModels;

trait FHIRDomainModelSerializationCleanupTrait
{
    public function jsonSerialize(): array
    {
        $json = parent::jsonSerialize();
        if (isset($json['resourceType'])) {
            unset($json['resourceType']);
        }
        return $json;
    }
}
