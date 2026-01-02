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
use OpenEMR\Setting\Repository\ListOptionRepository;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @extends MultiSettingManager<string>
 */
class MultiListSettingManager extends MultiSettingManager
{
    public function __construct(
        protected readonly ListOptionRepository $listOptionRepository,
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $listId = $this->globalsService->getSettingFieldOption($settingKey, GlobalSetting::DATA_TYPE_OPTION_LIST_ID);
        $possibleOptions = $this->listOptionRepository->findByListId($listId);
        Assert::oneOf(
            $settingValue,
            array_map(
                static fn(array $option): string => $option['option_id'],
                $possibleOptions,
            ),
            sprintf(
                'Setting "%s" can not accept value "%s". Expected one of: %s',
                $settingKey,
                $settingValue,
                implode(', ', array_map(
                    static fn(array $option): string => sprintf(
                        '"%s" (%s)',
                        $option['option_id'],
                        $option['title'],
                    ),
                    $possibleOptions,
                ))
            )
        );
    }

    public function normalizeSetting(string $settingKey): iterable
    {
        yield from parent::normalizeSetting($settingKey);

        $listId = $this->globalsService->getSettingFieldOption($settingKey, GlobalSetting::DATA_TYPE_OPTION_LIST_ID);
        yield 'setting_value_options' => array_map(
            static fn(array $data): array => [
                SettingManagerInterface::NORMALIZE_OPTION_VALUE => $data['option_id'],
                SettingManagerInterface::NORMALIZE_OPTION_LABEL => $data['title'],
            ],
            $this->listOptionRepository->findByListId($listId),
        );
    }
}
