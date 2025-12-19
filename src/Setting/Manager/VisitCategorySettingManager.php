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

use OpenEMR\Common\Database\Repository\Settings\PostCalendarCategoryRepository;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @extends AbstractSettingManager<string>
 */
class VisitCategorySettingManager extends ScalarSettingManager
{
    public function __construct(
        protected readonly PostCalendarCategoryRepository $visitCategoryRepository,
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null
    ) {
        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY;
    }

    /**
     * @todo Decide if we want to use findActive (not findAll)
     *       E.g. throw exception / fail validation if not active option is about to be set
     *
     * @throws InvalidArgumentException
     */
    public function validateSettingValue(string $settingKey, $settingValue): void
    {
        $possibleOptions = $this->visitCategoryRepository->findAll();

        Assert::oneOf(
            $settingValue,
            array_map(
                static fn(array $option): string => $option['pc_catid'],
                $possibleOptions,
            ),
            sprintf(
                'Setting "%s" can not accept value "%s". Expected one of: %s',
                $settingKey,
                $settingValue,
                implode(', ', array_map(
                    static fn(array $option): string => sprintf(
                        '"%s" (%s)',
                        $option['pc_catid'],
                        $option['pc_catname'],
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
                SettingManagerInterface::NORMALIZE_OPTION_VALUE => $data['pc_catid'],
                SettingManagerInterface::NORMALIZE_OPTION_LABEL => $data['pc_catname'],
            ],
            $this->visitCategoryRepository->findAll(),
        );
    }
}
