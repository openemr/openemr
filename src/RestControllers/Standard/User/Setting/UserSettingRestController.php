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

namespace OpenEMR\RestControllers\Standard\User\Setting;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Validator\Factory\SettingSectionValidatorFactory;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class UserSettingRestController
{
    use SingletonTrait;

    public function getAll(HttpRestRequest $request, string $userId): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                SettingServiceFactory::createUserSpecificByUserId($userId)->getAll()
            ),
            200,
            true
        );
    }

    public function getBySectionSlug(HttpRestRequest $request, string $userId, string $sectionSlug): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData(
                SettingServiceFactory::createUserSpecificByUserId($userId)->getBySectionSlug($sectionSlug)
            );
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200, true);
    }

    public function getOneBySettingKey(HttpRestRequest $request, string $userId, string $sectionSlug, string $settingKey): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                SettingServiceFactory::createUserSpecificByUserId($userId)->getOneBySettingKey($sectionSlug, $settingKey)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

//    public function patchBySectionSlug(HttpRestRequest $request, string $userId, string $sectionSlug, string $data): ResponseInterface
//    {
//        $result = new ProcessingResult();
//        try {
//            $data = json_decode($data, true);
//            Assert::isArray($data, 'Malformed data');
//            Assert::notEmpty($data, 'Empty data');
//
//            $settingService = SettingServiceFactory::createUserSpecificByUserId($userId);
//
//            $result->setData([
//                array_map(
//                    fn ($settingData): array => $settingService->setOneBySettingKey(
//                        $sectionSlug,
//                        $settingData['setting_key'],
//                        $settingData['setting_value'],
//                    ),
//                    $data
//                )
//            ]);
//        } catch (InvalidArgumentException $e) {
//            $result->setValidationMessages([
//                $e->getMessage()
//            ]);
//        }
//
//        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
//    }

    public function putBySectionSlug(HttpRestRequest $request, string $userId, string $sectionSlug, string $data): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $data = json_decode($data, true);
            Assert::isArray($data, 'Malformed data');
            Assert::notEmpty($data, 'Empty data');

            $validator = SettingSectionValidatorFactory::createUserSpecific($userId, $sectionSlug);
            $result = $validator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT); // @todo Correct context?
            if (!$result->isValid()) {
                return RestControllerHelper::createProcessingResultResponse($request, $result, 400);
            }

            $settingService = SettingServiceFactory::createUserSpecificByUserId($userId);
            $result->setData([
                array_map(
                    fn ($settingKey, $settingValue): array => $settingService->setOneBySettingKey(
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

    public function resetBySectionSlug(HttpRestRequest $request, string $userId, string $sectionSlug): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                SettingServiceFactory::createUserSpecificByUserId($userId)->resetBySectionSlug($sectionSlug)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    public function resetOneBySettingKey(HttpRestRequest $request, string $userId, string $sectionSlug, string $settingKey): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $result->setData([
                SettingServiceFactory::createUserSpecificByUserId($userId)->resetOneBySettingKey($sectionSlug, $settingKey)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
