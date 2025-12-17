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

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Acl\AclGroupService;
use OpenEMR\Validators\Acl\AclGroupValidator;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AdminAclGroupRestController
{
    private readonly AclGroupService $groupService;

    private readonly AclGroupValidator $groupValidator;

    public function __construct()
    {
        $this->groupService = new AclGroupService();
        $this->groupValidator = new AclGroupValidator();
    }

    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $result = $this->groupValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        if ($result->isValid()) {
            try {
                $result->addData(
                    $this->groupService->insert($data)
                );
            } catch (InvalidArgumentException $exception) {
                $result->addInternalError($exception->getMessage());
            }
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result,201);
    }

    public function getOne(HttpRestRequest $request, string $id): ResponseInterface
    {
        $result = new ProcessingResult();

        try {
            Assert::integerish($id, sprintf('ID %s is invalid. Integer expected', $id));
            $group = $this->groupService->getOneById((int) $id);
            if (null === $group) {
                return RestControllerHelper::createProcessingResultResponse($request, $result, 404);
            }

            $result->setData([$group]);
        } catch (InvalidArgumentException $exception) {
            $result->setValidationMessages([
                $exception->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->groupService->getAll()
            ),
            200,
            true
        );
    }

    public function delete(HttpRestRequest $request, string $id): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            Assert::integerish($id, sprintf('ID %s is invalid. Integer expected', $id));
            Assert::notNull(
                $this->groupService->getOneById($id),
                sprintf('Group %s was not found', $id)
            );

            $this->groupService->deleteById($id);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
