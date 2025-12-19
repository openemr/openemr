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

namespace OpenEMR\Setting\Manager;

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\SettingDriverInterface;

/**
 * @template TSettingValue
 * @implements SettingManagerInterface<TSettingValue>
 */
abstract class AbstractSettingManager implements SettingManagerInterface
{
    protected GlobalsService $globalsService;

    public function __construct(
        protected readonly SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        $this->globalsService = $globalsService ?: GlobalsServiceFactory::getInstance();
    }

    abstract public function isDataTypeSupported(string $dataType): bool;


    public function resetSetting(string $settingKey): void
    {
        $this->driver->resetSetting($settingKey);
    }

    public function getSettingDefaultValue(string $settingKey)
    {
        return $this->driver->getSettingDefaultValue($settingKey);
    }

    public function normalizeSetting(string $settingKey): iterable
    {
        $settingMetadata = $this->globalsService->getMetadataBySettingKey($settingKey);

        $settingValue = $this->getSettingValue($settingKey);
        $settingValue = is_numeric($settingValue) ? (int) $settingValue : $settingValue;

        $settingDefaultValue = $this->getSettingDefaultValue($settingKey);
        $settingDefaultValue = is_numeric($settingDefaultValue) ? (int) $settingDefaultValue : $settingDefaultValue;

        // @todo Do we need section here?
        // $sectionName = $this->globalsService->getSectionNameBySettingKey($settingKey);
        // yield 'setting_section' => $this->sectionService->slugify($sectionName);

        yield 'setting_key' => $settingKey;
        yield 'setting_name' => $settingMetadata[GlobalSetting::INDEX_NAME];
        yield 'setting_description' => $settingMetadata[GlobalSetting::INDEX_DESCRIPTION];
        yield 'setting_default_value' => $settingDefaultValue;
        yield 'setting_is_default_value' => null === $settingValue
            || $settingDefaultValue === $settingValue
        ;
        yield 'setting_value' => $settingValue;
    }
}
