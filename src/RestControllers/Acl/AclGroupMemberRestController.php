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

namespace OpenEMR\RestControllers\Acl;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\Acl\AclGroupMemberValidator;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AclGroupMemberRestController
{
    private UserService $userService;

    private AclGroupMemberService $aclGroupMemberService;

    private AclGroupMemberValidator $aclGroupMemberValidator;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->aclGroupMemberService = new AclGroupMemberService($this->userService);
        $this->aclGroupMemberValidator = new AclGroupMemberValidator();
    }

    public function post(int $groupId, int $userId, array $data, HttpRestRequest $request): ResponseInterface
    {
        $data['group_id'] = $groupId;
        $data['user_id'] = $userId;

        $result = $this->aclGroupMemberValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        if ($result->isValid()) {
            $user = $this->userService->getUser($data['user_id']);

            try {
                $this->aclGroupMemberService->addUserToGroup($user, $groupId, $data);
            } catch (InvalidArgumentException $exception) {
                $result->addInternalError($exception->getMessage());
            }
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result,201);
    }

    public function getAll(int $groupId, HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->aclGroupMemberService->getAll($groupId)
            ),
            200,
            true
        );
    }

    public function delete(HttpRestRequest $request, string $groupId, string $userId): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            Assert::integerish($groupId, sprintf('Group ID %s is invalid. Integer expected', $groupId));
            Assert::integerish($userId, sprintf('User ID %s is invalid. Integer expected', $userId));

            $user = $this->userService->getUser($userId);
            Assert::notNull(
                $user,
                sprintf('User with ID %s was not found', $userId)
            );

            $this->aclGroupMemberService->deleteUserFromGroup($user, $groupId);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
