<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\Standard\Admin\Acl;

use OpenEMR\Common\Database\Repository\Acl\AclGroupSettingRepository;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Acl\AclSectionService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AdminAclGroupSettingRestController
{
    private readonly AclGroupSettingRepository $aclGroupSettingRepository;

    private readonly AclSectionService $aclSectionService;

    public function __construct()
    {
        $this->aclGroupSettingRepository = RepositoryFactory::createRepository(AclGroupSettingRepository::class);
        $this->aclSectionService = new AclSectionService();
    }

    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->aclGroupSettingRepository->findAll()
            ),
            200,
            true
        );
    }

    public function getBySection(HttpRestRequest $request, int $sectionId): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            Assert::true(
                $this->aclSectionService->isIdValid($sectionId),
                sprintf(
                    'Unknown Section ID %d',
                    $sectionId
                )
            );

            $result->setData([
                $this->aclGroupSettingRepository->findBySectionId($sectionId)
            ]);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
