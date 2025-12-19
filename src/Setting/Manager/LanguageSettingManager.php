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

use OpenEMR\Common\Database\Repository\Settings\LanguageRepository;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @extends AbstractSettingManager<string>
 */
class LanguageSettingManager extends ScalarSettingManager
{
    public function __construct(
        protected readonly LanguageRepository $languageRepository,
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_LANGUAGE;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $possibleOptions = $this->languageRepository->findAll();

        Assert::oneOf(
            $settingValue,
            array_map(
                static fn(array $option): string => $option['lang_description'],
                $possibleOptions,
            ),
            sprintf(
                'Setting "%s" can not accept value "%s". Expected one of: %s',
                $settingKey,
                $settingValue,
                implode(', ', array_map(
                    static fn(array $option): string => sprintf(
                        '"%s"',
                        $option['lang_description'],
                    ),
                    $possibleOptions,
                ))
            )
        );
    }

    public function normalizeSetting(string $settingKey): iterable
    {
        yield from parent::normalizeSetting($settingKey);
        yield 'setting_value_options' => array_map(
            static fn(array $data): array => [
                SettingManagerInterface::NORMALIZE_OPTION_VALUE => $data['lang_description'],
                SettingManagerInterface::NORMALIZE_OPTION_LABEL => $data['lang_description'],
            ],
            $this->languageRepository->findAll(),
        );
    }
}
