<?php

/*
 * VersionedProfileTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Traits;

use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;

trait VersionedProfileTrait
{
    const PROFILE_VERSION_NONE = '';

    const PROFILE_VERSION_3_1_1 = '3.1.1';
    const PROFILE_VERSION_7_0_0 = '7.0.0';
    const PROFILE_VERSION_8_0_0 = '8.0.0';
    const PROFILE_VERSIONS_ALL = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1,self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];
    const PROFILE_VERSIONS_V1 = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1];
    const PROFILE_VERSIONS_V2 = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];
    public function getSupportedVersions()
    {
        return self::PROFILE_VERSIONS_ALL;
    }
    public function getProfileForVersions(string $profile, array $versions)
    {
        return array_map(fn($version): string => $profile . (!empty($version) ? "|" . $version : ""), $versions);
    }
    public function addProfilesToMeta(array $profiles, FHIRMeta $meta): FHIRMeta
    {
        foreach ($profiles as $item) {
            foreach ($this->getProfileForVersions($item, $this->getSupportedVersions()) as $profile) {
                $meta->addProfile($profile);
            }
        }
        return $meta;
    }
}
