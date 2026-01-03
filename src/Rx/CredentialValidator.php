<?php

/**
 * src/Rx/CredentialValidator.php
 *
 * Utility class for validating Ensora eRx credentials and detecting authentication failures.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx;

class CredentialValidator
{
    /**
     * Check if all required Ensora eRx credentials are configured
     *
     * @param array $globals Reference to globals configuration array
     * @return bool True if all required credentials are present and non-empty
     */
    public static function hasRequiredCredentials(array &$globals): bool
    {
        $requiredFields = [
            'erx_account_partner_name',
            'erx_account_name',
            'erx_account_password',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($globals[$field]) || empty($globals[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if XML response indicates an authentication failure
     *
     * @param string $xml The XML response from Ensora/NewCrop
     * @return bool True if the response indicates authentication failure
     */
    public static function isAuthenticationError(string $xml): bool
    {
        if (empty($xml)) {
            return false;
        }

        $authFailureIndicators = [
            'authentication',
            'unauthorized',
            'invalid credentials',
            'credential',
            'access denied',
        ];

        $lowerXml = strtolower($xml);

        foreach ($authFailureIndicators as $indicator) {
            if (str_contains($lowerXml, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get missing credential field names
     *
     * @param array $globals Reference to globals configuration array
     * @return array Array of missing field names
     */
    public static function getMissingCredentials(array &$globals): array
    {
        $requiredFields = [
            'erx_account_partner_name' => 'Partner Name',
            'erx_account_name' => 'Account Name',
            'erx_account_password' => 'Account Password',
        ];

        return array_filter(
            $requiredFields,
            fn($field) => !isset($globals[$field]) || empty($globals[$field]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
