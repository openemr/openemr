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
use OpenEMR\Setting\Driver\SettingDriverInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Note, array-key can be int or string, so
 * we convert possible option keys and $settingValue
 * to strings to prevent types mismatch on validation
 *
 * @extends AbstractSettingManager<string>
 */
class EnumSettingManager extends ScalarSettingManager
{
    public function __construct(
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_ENUM;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $possibleOptions = $this->globalsService->getSettingFieldOption($settingKey, GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES);

        Assert::oneOf(
            is_numeric($settingValue) ? (int) $settingValue : $settingValue,
            array_keys($possibleOptions),
            sprintf(
                'Setting "%s" can not accept value %s. Expected one of: %s',
                $settingKey,
                is_numeric($settingValue) ? $settingValue : sprintf('"%s"', $settingValue),
                implode(', ', array_map(
                    static fn($key, string $value): string => sprintf(
                        is_string($key) ? '"%s" (%s)' : '%s (%s)',
                        $key,
                        $value,
                    ),
                    array_keys($possibleOptions),
                    array_values($possibleOptions),
                ))
            )
        );
    }

    public function normalizeSetting(string $settingKey): iterable
    {
        yield from parent::normalizeSetting($settingKey);

        $possibleOptions = $this->globalsService->getSettingFieldOption($settingKey, GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES);
        yield 'setting_value_options' => array_map(
            static fn ($optionValue, $optionLabel): array => [
                SettingManagerInterface::NORMALIZE_OPTION_VALUE => is_numeric($optionValue) ? (int) $optionValue : $optionValue,
                SettingManagerInterface::NORMALIZE_OPTION_LABEL => $optionLabel,
            ],
            array_keys($possibleOptions),
            array_values($possibleOptions),
        );
    }
}
