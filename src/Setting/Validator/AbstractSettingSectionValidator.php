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

namespace OpenEMR\Setting\Validator;

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Manager\SettingManagerInterface;
use OpenEMR\Setting\Service\SettingSectionServiceInterface;
use OpenEMR\Validators\BaseValidator;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Rule\Callback;
use Particle\Validator\Validator;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

abstract class AbstractSettingSectionValidator extends BaseValidator
{
    public function __construct(
        protected readonly string $sectionSlug,
        protected readonly SettingSectionServiceInterface $settingSectionService,
        protected readonly SettingManagerInterface $settingManager,
        protected readonly GlobalsService $globalsService,
    ) {
        parent::__construct();
    }

    abstract protected function getSettingKeysBySectionName(string $sectionName): array;

    public function assertNoExtraFields(array $data, string $context): void
    {
        $sectionName = $this->settingSectionService->deslugify($this->sectionSlug);
        $settingKeys = $this->getSettingKeysBySectionName($sectionName);

//        $missingSettingKeys = array_diff(
//            $settingKeys,
//            array_keys($data)
//        );
//
//        Assert::isEmpty(
//            $missingSettingKeys,
//            sprintf(
//                'Missing settings: %s',
//                implode(', ', $missingSettingKeys),
//            ),
//        );

        $extraSettingKeys = array_diff(
            array_keys($data),
            $settingKeys,
        );

        Assert::isEmpty(
            $extraSettingKeys,
            sprintf(
                'Unexpected settings: %s',
                implode(', ', $extraSettingKeys),
            ),
        );
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $sectionName = $this->settingSectionService->deslugify($this->sectionSlug);
        $settingKeys = $this->getSettingKeysBySectionName($sectionName);

        foreach ($settingKeys as $settingKey) {
            $chain = $validator->required(
                $settingKey,
                $this->globalsService->getSettingName($settingKey)
            );

            if (in_array(
                $this->globalsService->getSettingDefaultValue($settingKey),
                ['', null],
                true
            )) {
                $chain->allowEmpty(true);
            }

            switch ($this->globalsService->getSettingDataType($settingKey)) {
                case GlobalSetting::DATA_TYPE_NUMBER:
                    $chain->numeric();
                    break;

                case GlobalSetting::DATA_TYPE_BOOL:
                    $chain->bool();
                    break;

                case GlobalSetting::DATA_TYPE_TEXT:
                    $chain->string();
                    break;

                // @todo Other types
            }

            $chain->callback(function ($settingValue) use ($settingKey) {
                try {
                    $this->settingManager->validateSettingValue($settingKey, $settingValue);
                } catch (InvalidArgumentException $exception) {
                    throw new InvalidValueException(
                        $exception->getMessage(),
                        Callback::INVALID_VALUE,
                        $exception,
                    );
                }
                return true;
            });
        }
    }
}
