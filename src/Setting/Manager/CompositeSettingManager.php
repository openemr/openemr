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
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @implements SettingManagerInterface<array|string|int|bool>
 */
class CompositeSettingManager implements SettingManagerInterface
{
    private GlobalsService $globalsService;

    public function __construct(
        /** @phpstan-var SettingManagerInterface[] */
        private readonly array $settingManagers,
        ?GlobalsService $globalsService = null,
    ) {
        $this->globalsService = $globalsService ?: GlobalsServiceFactory::getInstance();
    }

    public function addSettingManager(SettingManagerInterface $settingManager): void
    {
        $this->globalsService[] = $settingManager;
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return true;
    }

    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $this->getManagerBySettingKey($settingKey)->validateSettingValue($settingKey, $settingValue);
    }

    public function normalizeSetting(string $settingKey): iterable
    {
        yield from $this->getManagerBySettingKey($settingKey)->normalizeSetting($settingKey);
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        $this->validateSettingValue($settingKey, $settingValue);

        $this->getManagerBySettingKey($settingKey)->setSettingValue($settingKey, $settingValue);
    }

    public function getSettingDefaultValue(string $settingKey)
    {
        return $this->getManagerBySettingKey($settingKey)->getSettingDefaultValue($settingKey);
    }

    public function getSettingValue(string $settingKey)
    {
        return $this->getManagerBySettingKey($settingKey)->getSettingValue($settingKey);
    }

    public function resetSetting(string $settingKey): void
    {
        $this->getManagerBySettingKey($settingKey)->resetSetting($settingKey);
    }

    private function getManagerBySettingKey(string $settingKey): SettingManagerInterface
    {
        $dataType = $this->globalsService->getSettingDataType($settingKey);
        foreach ($this->settingManagers as $settingManager) {
            if ($settingManager->isDataTypeSupported($dataType)) {
                return $settingManager;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Unable to find manager for setting %s',
            $settingKey
        ));
    }
}
