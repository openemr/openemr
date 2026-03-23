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

use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Acl\AclGroupMemberService;
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
        return new self(
            UserRepository::getInstance(),
            AclGroupMemberService::getInstance(),
            AclGroupMemberValidator::getInstance(),
        );
    }

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AclGroupMemberService $aclGroupMemberService,
        private readonly AclGroupMemberValidator $aclGroupMemberValidator,
    ) {
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

    public function post(int $groupId, string $uuid, array $data, HttpRestRequest $request): ResponseInterface
    {
        $data['group_id'] = $groupId;
        $data['uuid'] = $uuid;

        $result = $this->aclGroupMemberValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        if ($result->isValid()) {
            $user = $this->userRepository->findOneByUuid($data['uuid']);

            try {
                $this->aclGroupMemberService->addUserToGroup($user, $groupId, $data);
            } catch (InvalidArgumentException $exception) {
                $result->addInternalError($exception->getMessage());
            }
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result,201);
    }

    public function delete(HttpRestRequest $request, string $groupId, string $uuid): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            Assert::integerish($groupId, sprintf('Group ID %s is invalid. Integer expected', $groupId));

            $user = $this->userRepository->findOneByUuid($uuid);
            Assert::notNull(
                $user,
                sprintf('User with UUID %s was not found', $uuid)
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
