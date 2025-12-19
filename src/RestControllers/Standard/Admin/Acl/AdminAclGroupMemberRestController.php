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

namespace OpenEMR\RestControllers\Standard\Admin\Acl;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\Acl\AclGroupMemberValidator;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AdminAclGroupMemberRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        $userService = new UserService();
        return new self(
            $userService,
            AclGroupMemberService::getInstance(),
            AclGroupMemberValidator::getInstance(),
        );
    }

    public function __construct(
        private readonly UserService $userService,
        private readonly AclGroupMemberService $aclGroupMemberService,
        private readonly AclGroupMemberValidator $aclGroupMemberValidator,
    ) {
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
