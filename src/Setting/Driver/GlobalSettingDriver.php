<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Setting\Driver;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;

/**
 * Usage:
 *   $userSettingManager = new GlobalSettingManager();
 *   $userSettings->setSettingValue('allow_pat_delete', 1);
 *   $allowPatientDelete = $userSettingManager->getSettingValue('allow_pat_delete');
 *   $userSettings->removeSetting('allow_pat_delete');
 *
 * @todo support of multiple options like hide_dashboard_cards (Hide selected cards on patient dashboard)
 * @see GlobalSetting::ALL_MULTI_DATA_TYPES
 *
 * @todo arrays support!
 */
class GlobalSettingDriver implements SettingDriverInterface
{
    private readonly DatabaseManager $database;

    private readonly GlobalsService $globalsService;

    public function __construct(?GlobalsService $globalsService = null)
    {
        $this->database = DatabaseManager::getInstance();
        $this->globalsService = $globalsService ?: GlobalsServiceFactory::getInstance();
    }

    public function getSettingDefaultValue(string $settingKey)
    {
        $settingMetadata = $this->globalsService->getMetadataBySettingKey($settingKey);

        return $settingMetadata[GlobalSetting::INDEX_DEFAULT];
    }

    public function getSettingValue(string $settingKey): string|null
    {
        return $this->database->getSingleScalarResultBy('globals', 'gl_value', [
            'gl_name' => $settingKey,
        ]);
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        $existingSettingValue = $this->getSettingValue($settingKey);

        if (null === $existingSettingValue) {
            $this->database->insert('globals', [
                'gl_name' => $settingKey,
                'gl_value' => $settingValue,
            ]);
        } elseif ($existingSettingValue !== $settingValue) {
            $this->updateSettingValue($settingKey, $settingValue);
        }
    }

    public function setMultiSettingValues(string $settingKey, array $settingValues): void
    {
        $this->database->removeBy('globals', ['gl_name' => $settingKey]);
        foreach ($settingValues as $index => $settingValue) {
            $this->database->insert('globals', [
                'gl_name' => $settingKey,
                'gl_index' => $index,
                'gl_value' => $settingValue,
            ]);
        }
    }

    public function getMultiSettingValue(string $settingKey): array
    {
        return $this->database->getSingleColumnResultBy('globals', 'gl_value', [
            'gl_name' => $settingKey,
        ], [
            'gl_index' => 'ASC',
            'gl_value' => 'ASC',
        ]);
    }

    public function resetSetting(string $settingKey): void
    {
        $this->setSettingValue(
            $settingKey,
            $this->getSettingDefaultValue($settingKey),
        );
    }

    private function updateSettingValue(string $settingKey, string $settingValue): void
    {
        $this->database->update('globals', [
            'gl_value' => $settingValue,
        ], [
            'gl_name' => $settingKey,
        ]);
    }

//    public function removeSetting(string $settingKey): void
//    {
//        $this->database->removeBy('globals', [
//            'gl_name' => $settingKey,
//        ]);
//    }
}
