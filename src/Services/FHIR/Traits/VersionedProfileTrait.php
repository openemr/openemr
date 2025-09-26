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

trait VersionedProfileTrait {
    public function getSupportedVersions() {
        return ['', '3.1.1','7.0.0', '8.0.0'];
    }
    public function getProfileForVersions(string $profile, array $versions) {
        return array_map(fn($version) => $profile . (!empty($version) ? "|" . $version : ""), $versions);
    }
}
