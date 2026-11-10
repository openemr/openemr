<?php

/**
 * interface/eRxGlobals.php Functions for retrieving Ensora eRx global configurations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2015 Sam Likins <sam.likins@wsi-services.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Encryption\Plaintext;
use OpenEMR\Rx\Ensora\ErxGlobalsInterface;
use OpenEMR\Services\VersionService;

class eRxGlobals implements ErxGlobalsInterface
{
    private $configuration;

    /**
     * Construct eRxGlobals optionally providing an array of configurations to use
     * @param array|null &$configuration [optional] Array of configurations to use
     */
    public function __construct(&$configuration = null)
    {
        if (is_array($configuration)) {
            $this->setGlobals($configuration);
        }
    }

    /**
     * Set the configuration array for use in eRxGlobals
     * @param array &$configuration Array of configurations to use
     */
    public function setGlobals(&$configuration)
    {
        $this->configuration =& $configuration;

        return $this;
    }

    /**
     * Return the value stored in configurations specified by the key
     * @param  string $key Configuration array key
     * @return mixed       Configuration specified by the key
     */
    public function getGlobalValue($key)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return null;
    }

    /**
     * Return the OpenEMR site directory, or empty when unset
     */
    public function getOpenEMRSiteDirectory(): string
    {
        $value = $this->getGlobalValue('OE_SITE_DIR');

        return is_string($value) ? $value : '';
    }

    /**
     * Return enable state for Ensora eRx service
     * @return bool Ensora eRx service enabled state
     */
    public function getEnabled()
    {
        return $this->getGlobalValue('erx_enable');
    }

    /**
     * Return the Ensora eRx requests URL, or empty when unset
     */
    public function getPath(): string
    {
        $value = $this->getGlobalValue('erx_newcrop_path');

        return is_string($value) ? $value : '';
    }

    /**
     * Return the Ensora eRx service URLs
     * @return array URLs for Ensora eRx services: index [ 0 = Update, 1 = Patient ]
     */
    public function getSoapPaths()
    {
        return explode(';', (string) $this->getGlobalValue('erx_newcrop_path_soap'));
    }

    /**
     * Return the Ensora eRx allergies time-to-live
     * @return int Time-to-live in seconds for Ensora eRx allergies
     */
    public function getTTLSoapAllergies()
    {
        return $this->getGlobalValue('erx_soap_ttl_allergies');
    }

    /**
     * Return the Ensora eRx medications time-to-live
     * @return int Time-to-live in seconds for Ensora eRx medications
     */
    public function getTTLSoapMedications()
    {
        return $this->getGlobalValue('erx_soap_ttl_medications');
    }

    /**
     * Return the Ensora eRx partner name for credentials, or empty when unset
     */
    public function getPartnerName(): string
    {
        $value = $this->getGlobalValue('erx_account_partner_name');

        return is_string($value) ? $value : '';
    }

    /**
     * Return the Ensora eRx account name for credentials, or empty when unset
     */
    public function getAccountName(): string
    {
        $value = $this->getGlobalValue('erx_account_name');

        return is_string($value) ? $value : '';
    }

    /**
     * Return the decrypted Ensora eRx password for credentials, or empty when unset
     */
    public function getAccountPassword(): Plaintext
    {
        $value = $this->getGlobalValue('erx_account_password');

        return new Plaintext(ServiceContainer::getCrypto()->decryptFromDatabase(is_string($value) ? $value : null));
    }

    /**
     * Return the Ensora eRx account Id for credentials, or empty when unset
     */
    public function getAccountId(): string
    {
        $value = $this->getGlobalValue('erx_account_id');

        return is_string($value) ? $value : '';
    }

    /**
     * Return enable state for Ensora eRx only upload prescriptions
     */
    public function getUploadActive(): bool
    {
        return in_array($this->getGlobalValue('erx_upload_active'), [true, 1, '1'], true);
    }

    /**
     * Return enable state for Ensora eRx import status message
     * @return bool Ensora eRx import status message enabled state
     */
    public function getImportStatusMessage()
    {
        return $this->getGlobalValue('erx_import_status_message');
    }

    /**
     * Return enable state for Ensora eRx display medications uploaded
     * @return bool Ensora eRx display medications uploaded enabled state
     */
    public function getDisplayMedication()
    {
        return $this->getGlobalValue('erx_medication_display');
    }

    /**
     * Return enable state for Ensora eRx display allergies uploaded
     * @return bool Ensora eRx display allergies uploaded enabled state
     */
    public function getDisplayAllergy()
    {
        return $this->getGlobalValue('erx_allergy_display');
    }

    /**
     * Return Ensora eRx default patient country code, or empty when unset
     */
    public function getDefaultPatientCountry(): string
    {
        $value = $this->getGlobalValue('erx_default_patient_country');

        return is_string($value) ? $value : '';
    }

    /**
     * Return array containing Ensora eRx credentials
     * @return array Ensora eRx credentials: index [ 0 = Partner Name, 1 = Account Name, 2 = Password ]
     */
    public function getCredentials(): array
    {
        return [
            $this->getPartnerName(),
            $this->getAccountName(),
            $this->getAccountPassword()->bytes,
        ];
    }

    /**
     * Return the OpenEMR version the eRx request reports
     */
    public function getProductVersion(): string
    {
        return (new VersionService())->getSoftwareVersion()->full;
    }

    /**
     * Return Debug Ensora eRx settings
     * @return int Debug settings: flags [ 1 = XML, 2 = RESULT ]
     */
    public function getDebugSetting(): int
    {
        $value = $this->getGlobalValue('erx_debug_setting');

        return is_numeric($value) ? (int) $value : 0;
    }
}
