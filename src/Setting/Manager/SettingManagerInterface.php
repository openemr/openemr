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

use Webmozart\Assert\InvalidArgumentException;

/**
 * @template TSettingValue
 *
 * @phpstan-type TSetting = array{
 *     setting_name: string,
 *     setting_description: string,
 *     setting_section: string,
 *     setting_key: string,
 *     setting_value: string|null,
 *     setting_default_value: string,
 *     setting_is_default_value: bool,
 *     setting_value_options?: array<{
 *         option_value: string,
 *         option_label: string,
 *     }>,
 *     setting_value_options_html?: string,
 * }
 */
interface SettingManagerInterface
{
    public const NORMALIZE_OPTION_VALUE = 'option_value';

    public const NORMALIZE_OPTION_LABEL = 'option_label';

    public function isDataTypeSupported(string $dataType): bool;

    /**
     * Validate setting value before storing
     *
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void;

    /**
     * @phpstan-param TSettingValue $settingValue
     */
    public function setSettingValue(string $settingKey, $settingValue): void;

    /**
     * @phpstan-return TSettingValue
     */
    public function getSettingValue(string $settingKey);

    /**
     * @phpstan-return TSettingValue
     */
    public function getSettingDefaultValue(string $settingKey);

    public function resetSetting(string $settingKey): void;

//    public function removeSetting(string $settingKey): void;

    /**
     * @return TSetting
     */
    public function normalizeSetting(string $settingKey): iterable;
}
