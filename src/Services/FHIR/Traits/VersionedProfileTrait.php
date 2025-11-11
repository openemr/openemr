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

use InvalidArgumentException;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;

trait VersionedProfileTrait
{
    // TODO: @adunsulag consider making this an enum
    protected string $highestUSCoreProfileVersion = self::PROFILE_VERSION_8_0_0;
    const PROFILE_VERSION_NONE = '';

    const PROFILE_VERSION_3_1_1 = '3.1.1';
    const PROFILE_VERSION_7_0_0 = '7.0.0';
    const PROFILE_VERSION_8_0_0 = '8.0.0';
    const PROFILE_VERSIONS_ALL = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1,self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];
    const PROFILE_VERSIONS_V1 = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1];
    const PROFILE_VERSIONS_V2 = [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_7_0_0, self::PROFILE_VERSION_8_0_0];

    /**
     * Get the list of supported US Core profile versions based on the highest compatible version as set in our Globals
     * if the highest compatible version is not set, assume the latest version, currently 8.0.0 as of 2025-10
     * Individual FHIR resource services may override this method to define their own supported versions in case of breaking changes
     * most resources support all versions and will use the default implementation
     * @return array<int, string> List of supported US Core profile versions
     */
    public function getSupportedVersions(): array
    {
        $highestVersion = $this->getHighestCompatibleUSCoreProfileVersion();
        // most resources support all versions, but some have breaking changes and will define their own supported versions, see for example FhirPatientService
        return match ($highestVersion) {
            self::PROFILE_VERSION_3_1_1 => self::PROFILE_VERSIONS_V1,
            self::PROFILE_VERSION_7_0_0 => [self::PROFILE_VERSION_NONE, self::PROFILE_VERSION_3_1_1, self::PROFILE_VERSION_7_0_0],
            // self::PROFILE_VERSION_8_0_0 and any other future versions
            default => self::PROFILE_VERSIONS_ALL
        };
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


    public function getHighestCompatibleUSCoreProfileVersion(): string
    {
        // return the highest supported US Core profile version as set in our Globals
        return $this->highestUSCoreProfileVersion ?? self::PROFILE_VERSION_8_0_0;
    }

    public function setHighestCompatibleUSCoreProfileVersion(string $version): void
    {
        // set the highest supported US Core profile version in our Globals
        if (in_array($version, self::PROFILE_VERSIONS_ALL)) {
            $this->highestUSCoreProfileVersion = $version;
        } else {
            $this->getSystemLogger()->errorLogCaller("Attempt to set unsupported US Core profile version", ['version' => $version]);
            throw new InvalidArgumentException("Unsupported US Core profile version " . $version);
        }
    }
}
