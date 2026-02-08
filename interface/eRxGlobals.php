<?php

/**
 * interface/eRxGlobals.php Functions for retrieving Ensora eRx global configurations.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @copyright Copyright (c) 2015 Sam Likins <sam.likins@wsi-services.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\VersionService;

class eRxGlobals
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
    }

    /**
     * Return the version of OpenEMR
     * @return string OpenEMR version
     */
    public function getOpenEMRVersion()
    {
        return (new VersionService())->asString();
    }

    /**
     * Return the OpenEMR site directory
     * @return string OpenEMR site directory
     */
    public function getOpenEMRSiteDirectory()
    {
        return $this->getGlobalValue('OE_SITE_DIR');
    }

    /**
     * Return enable state for Ensora eRx service
     * @return boolean Ensora eRx service enabled state
     */
    public function getEnabled()
    {
        return $this->getGlobalValue('erx_enable');
    }

    /**
     * Return the Ensora eRx requests URL
     * @return string URL for Ensora eRx requests
     */
    public function getPath()
    {
        return $this->getGlobalValue('erx_newcrop_path');
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
     * @return integer Time-to-live in seconds for Ensora eRx allergies
     */
    public function getTTLSoapAllergies()
    {
        return $this->getGlobalValue('erx_soap_ttl_allergies');
    }

    /**
     * Return the Ensora eRx medications time-to-live
     * @return integer Time-to-live in seconds for Ensora eRx medications
     */
    public function getTTLSoapMedications()
    {
        return $this->getGlobalValue('erx_soap_ttl_medications');
    }

    /**
     * Return the Ensora eRx partner name for credentials
     * @return string Partner name for credentials
     */
    public function getPartnerName()
    {
        return $this->getGlobalValue('erx_account_partner_name');
    }

    /**
     * Return the Ensora eRx account name for credentials
     * @return string Account name for credentials
     */
    public function getAccountName()
    {
        return $this->getGlobalValue('erx_account_name');
    }

    /**
     * Return the Ensora eRx password for credentials
     * @return string Password for credentials
     */
    public function getAccountPassword()
    {
        $cryptoGen = new CryptoGen();
        return $cryptoGen->decryptStandard($this->getGlobalValue('erx_account_password'));
    }

    /**
     * Return the Ensora eRx account Id for credentials
     * @return string Account Id for credentials
     */
    public function getAccountId()
    {
        return $this->getGlobalValue('erx_account_id');
    }

    /**
     * Return enable state for Ensora eRx only upload prescriptions
     * @return boolean Ensora eRx only upload prescriptions enabled state
     */
    public function getUploadActive()
    {
        return $this->getGlobalValue('erx_upload_active');
    }

    /**
     * Return enable state for Ensora eRx import status message
     * @return boolean Ensora eRx import status message enabled state
     */
    public function getImportStatusMessage()
    {
        return $this->getGlobalValue('erx_import_status_message');
    }

    /**
     * Return enable state for Ensora eRx display medications uploaded
     * @return boolean Ensora eRx display medications uploaded enabled state
     */
    public function getDisplayMedication()
    {
        return $this->getGlobalValue('erx_medication_display');
    }

    /**
     * Return enable state for Ensora eRx display allergies uploaded
     * @return boolean Ensora eRx display allergies uploaded enabled state
     */
    public function getDisplayAllergy()
    {
        return $this->getGlobalValue('erx_allergy_display');
    }

    /**
     * Return Ensora eRx default patient country code
     * @return string Ensora eRx default patient country code
     */
    public function getDefaultPatientCountry()
    {
        return $this->getGlobalValue('erx_default_patient_country');
    }

    /**
     * Return array containing Ensora eRx credentials
     * @return array Ensora eRx credentials: index [ 0 = Partner Name, 1 = Account Name, 2 = Password ]
     */
    public function getCredentials()
    {
        return [
            $this->getPartnerName(),
            $this->getAccountName(),
            $this->getAccountPassword(),
        ];
    }

    /**
     * Return Debug Ensora eRx settings
     * @return integer Debug settings: flags [ 1 = XML, 2 = RESULT ]
     */
    public function getDebugSetting()
    {
        return $this->getGlobalValue('erx_debug_setting');
    }
}
