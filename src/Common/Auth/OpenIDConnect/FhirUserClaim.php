<?php
/*
 * FhirUserClaim.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Services\PractitionerService;

class FhirUserClaim {
    use SystemLoggerAwareTrait;

    private string $fhirBaseUrl = '';

    public function getFhirUser($fhirUserId) : string {
        $uuidToUser = new UuidUserAccount($fhirUserId);
        $userRole = $uuidToUser->getUserRole();
        $fhirUserResource = "Person";
        if ($userRole == UuidUserAccount::USER_ROLE_USERS) {
            // need to find out if its a practitioner or not
            $practitionerService = new PractitionerService();
            // ONC validation does not accept Person as a valid test case so we have to differentiate practitioners
            // from the more generic Person resource.
            if ($practitionerService->isValidPractitionerUuid($fhirUserId)) {
                $fhirUserResource = "Practitioner";
            }
        } else if ($userRole == UuidUserAccount::USER_ROLE_PATIENT) {
            $fhirUserResource = "Patient";
        } else {
            (new SystemLogger())->error("user role not supported for fhirUser claim ", ['role' => $userRole]);
        }
        $fhirUser = $this->getFhirBaseUrl() . "/" . $fhirUserResource . "/" . $fhirUserId;
        return $fhirUser;
    }


    public function getFhirBaseUrl(): string
    {
        return $this->fhirBaseUrl;
    }

    public function setFhirBaseUrl(string $fhirBaseUrl): void
    {
        if (ValidationUtils::isValidUrl($fhirBaseUrl)) {
            $this->fhirBaseUrl = $fhirBaseUrl;
        } else {
            throw OAuthServerException::invalidRequest('fhirBaseUrl', 'Invalid FHIR base URL provided.');
        }
    }
}
