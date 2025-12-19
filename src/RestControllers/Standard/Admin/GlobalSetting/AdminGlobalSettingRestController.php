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

namespace OpenEMR\RestControllers\Standard\Admin\GlobalSetting;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingService;
use OpenEMR\Setting\Validator\Factory\SettingSectionValidatorFactory;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AdminGlobalSettingRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            SettingServiceFactory::createGlobal()
        );
    }

    public function __construct(
        private readonly GlobalSettingService $settingService,
    ) {
    }

    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->settingService->getAll()
            ),
            200,
            true
        );
    }

    public function getBySectionSlug(HttpRestRequest $request, string $sectionSlug): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData(
                $this->settingService->getBySectionSlug($sectionSlug)
            );
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200, true);
    }

    public function getOneBySettingKey(HttpRestRequest $request, string $sectionSlug, string $settingKey): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                $this->settingService->getOneBySettingKey($sectionSlug, $settingKey)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    public function putBySectionSlug(HttpRestRequest $request, string $sectionSlug, string $data): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $data = json_decode($data, true);
            Assert::isArray($data, 'Malformed data');
            Assert::notEmpty($data, 'Empty data');

            $validator = SettingSectionValidatorFactory::createGlobal($sectionSlug);
            $result = $validator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT); // @todo Correct context?
            if (!$result->isValid()) {
                return RestControllerHelper::createProcessingResultResponse($request, $result, 400);
            }

            $result->setData([
                array_map(
                    fn ($settingKey, $settingValue): array => $this->settingService->setOneBySettingKey(
                        $sectionSlug,
                        $settingKey,
                        $settingValue,
                    ),
                    array_keys($data),
                    array_values($data),
                )
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    public function resetBySectionSlug(HttpRestRequest $request, string $sectionSlug): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                $this->settingService->resetBySectionSlug($sectionSlug)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    public function resetOneBySettingKey(HttpRestRequest $request, string $sectionSlug, string $settingKey): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                $this->settingService->resetOneBySettingKey($sectionSlug, $settingKey)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
