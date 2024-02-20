<?php

/**
 * Handles the mapping and retrieving of telehealth providers in the OpenEMR system.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Repository;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthPersonSettings;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class TeleHealthProviderRepository
{
    public function __construct(SystemLogger $logger, TelehealthGlobalConfig $config)
    {
        $this->personSettings = new TeleHealthPersonSettingsRepository($logger);
        $this->config = $config;
    }

    public function isEnabledProvider($providerId)
    {
        if ($this->config->shouldAutoProvisionProviders()) {
            return true;
        }

        $setting = $this->personSettings->getSettingsForUser($providerId);
        if (!empty($setting)) {
            return $setting->getIsEnabled();
        }
        return false;
    }

    public function getEnabledProviders()
    {
        $providers =  [];
        // if we auto provision we need to grab our entire provider array
        if ($this->config->shouldAutoProvisionProviders()) {
            // grab all the providers and return them as enabled settings
            $service = new UserService();
            $facility = $_SESSION['pc_facility'] ?? "";
            $dataArray = $service->getUsersForCalendar($facility);
            if (empty($dataArray)) { // if our facility came back with nothing we will try to hit the current logged in user
                $service->getUsersForCalendar($_SESSION['authUserID']);
            }
            if (!empty($dataArray)) {
                $providers = array_map(function ($provider) {
                    return $this->mapProviderToPersonSetting($provider);
                }, $dataArray);
            }
        } else {
            // just grab all of our enabled users
            $providers = $this->personSettings->getEnabledUsers();
        }
        return $providers;
    }

    private function mapProviderToPersonSetting($provider)
    {
        $personSetting = new TeleHealthPersonSettings();
        $personSetting->setIsPatient(false);
        $personSetting->setDbRecordId($provider['id']);
        $personSetting->setIsEnabled(true);
        return $personSetting;
    }
}
