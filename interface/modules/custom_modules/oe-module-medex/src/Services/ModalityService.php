<?php

/**
 * MedEx Module - Modality Service
 * Determines which communication methods (SMS/EMAIL/AVM) are available for a patient
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

namespace OpenEMR\Modules\MedEx\Services;

class ModalityService
{
    private $icons = [];

    public function __construct()
    {
        // Load icons from medex_icons table
        $this->loadIcons();
    }

    /**
     * Load communication icons from medex_icons table
     * Gracefully handle missing table - not needed for basic modality detection
     */
    private function loadIcons()
    {
        try {
            $sql = "SELECT * FROM medex_icons";
            $result = \sqlStatement($sql);

            while ($row = \sqlFetchArray($result)) {
                $this->icons[$row['msg_type']][$row['msg_status']] = $row['i_html'];
            }
            error_log('[MedEx ModalityService] Loaded ' . count($this->icons) . ' icon types from medex_icons table');
        } catch (\Exception $e) {
            // medex_icons table doesn't exist - that's OK
            // Icons are not needed for modality detection (just ALLOWED array)
            error_log('[MedEx ModalityService] medex_icons table not found: ' . $e->getMessage());
            $this->icons = [];
        }
    }

    /**
     * Get possible communication modalities for a patient
     * Returns HTML icons showing what methods are ALLOWED or NotAllowed
     *
     * This duplicates the logic from library/MedEx/API.php::possibleModalities()
     *
     * @param array $patient Patient data (phone_cell, phone_home, email, hipaa_allowsms, hipaa_allowemail, hipaa_voice)
     * @return array Array with SMS, EMAIL, AVM keys containing icon HTML
     */
    public function getPossibleModalities($patient)
    {
        $modalities = [];

        // SMS - requires cell phone and HIPAA SMS permission
        if (empty($patient['phone_cell']) || ($patient["hipaa_allowsms"] ?? 'NO') == "NO") {
            $modalities['SMS'] = $this->icons['SMS']['NotAllowed'] ?? '';
            $modalities['ALLOWED']['SMS'] = 'NO';
        } else {
            $modalities['SMS'] = $this->icons['SMS']['ALLOWED'] ?? '';
            $modalities['ALLOWED']['SMS'] = 'YES';
        }
        error_log('[MedEx ModalityService] SMS icon HTML length: ' . strlen($modalities['SMS']));

        // AVM (Automated Voice Message) - requires any phone and HIPAA voice permission
        if ((empty($patient["phone_home"]) && empty($patient["phone_cell"])) || ($patient["hipaa_voice"] ?? 'NO') == "NO") {
            $modalities['AVM'] = $this->icons['AVM']['NotAllowed'] ?? '';
            $modalities['ALLOWED']['AVM'] = 'NO';
        } else {
            $modalities['AVM'] = $this->icons['AVM']['ALLOWED'] ?? '';
            $modalities['ALLOWED']['AVM'] = 'YES';
        }

        // EMAIL - requires email and HIPAA email permission
        if (empty($patient["email"]) || ($patient["hipaa_allowemail"] ?? 'NO') == "NO") {
            $modalities['EMAIL'] = $this->icons['EMAIL']['NotAllowed'] ?? '';
            $modalities['ALLOWED']['EMAIL'] = 'NO';
        } else {
            $modalities['EMAIL'] = $this->icons['EMAIL']['ALLOWED'] ?? '';
            $modalities['ALLOWED']['EMAIL'] = 'YES';
        }

        // Check if patient's facility and provider are enabled for MedEx
        if ($GLOBALS['medex_enable'] == '1') {
            $sql = "SELECT * FROM medex_prefs";
            $prefs = \sqlFetchArray(\sqlStatement($sql));

            if ($prefs) {
                $facs = explode('|', (string) $prefs['ME_facilities']);
                foreach ($facs as $place) {
                    if (isset($patient['r_facility']) && ($patient['r_facility'] == $place)) {
                        $modalities['facility']['status'] = 'ok';
                    }
                }

                $providers = explode('|', (string) $prefs['ME_providers']);
                foreach ($providers as $provider) {
                    if (isset($patient['r_provider']) && ($patient['r_provider'] == $provider)) {
                        $modalities['provider']['status'] = 'ok';
                    }
                }
            }
        }

        return $modalities;
    }

    /**
     * Get icon HTML for a specific message type and status
     *
     * @param string $msgType SMS, EMAIL, AVM, POSTCARD
     * @param string $msgStatus ALLOWED, NotAllowed, SCHEDULED, SENT, READ, FAILED, CONFIRMED, CALL, STOP, etc.
     * @return string|false Icon HTML or false if not found
     */
    public function getIcon($msgType, $msgStatus = 'SCHEDULED')
    {
        return $this->icons[$msgType][$msgStatus] ?? false;
    }

    /**
     * Get all icons for a message type
     *
     * @param string $msgType SMS, EMAIL, AVM, POSTCARD
     * @return array Array of status => icon HTML
     */
    public function getIconsForType($msgType)
    {
        return $this->icons[$msgType] ?? [];
    }

    /**
     * Get all icons
     *
     * @return array Array of msgType => [status => icon HTML]
     */
    public function getAllIcons()
    {
        return $this->icons;
    }
}
