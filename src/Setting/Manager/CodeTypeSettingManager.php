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

use OpenEMR\Common\Database\Repository\Settings\CodeTypeRepository;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @extends AbstractSettingManager<string>
 */
class CodeTypeSettingManager extends ScalarSettingManager
{
    public function __construct(
        protected readonly CodeTypeRepository $codeTypeRepository,
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_CODE_TYPES;
    }

    /**
     * @todo Decide if we want to use findActive (not findAll)
     *       E.g. throw exception / fail validation if not active option is about to be set
     *
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $possibleOptions = $this->codeTypeRepository->findAll();

        Assert::oneOf(
            $settingValue,
            array_map(
                static fn(array $option): string => $option['ct_key'],
                $possibleOptions,
            ),
            sprintf(
                'Setting "%s" can not accept value "%s". Expected one of: %s',
                $settingKey,
                $settingValue,
                implode(', ', array_map(
                    static fn(array $option): string => sprintf(
                        '"%s" (%s)',
                        $option['ct_key'],
                        $option['ct_label'],
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
                SettingManagerInterface::NORMALIZE_OPTION_VALUE => $data['ct_key'],
                SettingManagerInterface::NORMALIZE_OPTION_LABEL => $data['ct_label'],
            ],
            $this->codeTypeRepository->findAll(),
        );
    }
}
