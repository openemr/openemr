<?php

/**
 * UserRestController - REST API for user related operations
 *
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\Standard\User;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\UserService as LegacyUserService;
use OpenEMR\Services\User\UserService;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\UserValidator;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\InvalidArgumentException;

class UserRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        // @phpstan-ignore-next-line new.static
        return new static(
            new LegacyUserService(),
            UserService::getInstance(),
            UserValidator::getInstance(),
        );
    }

    public function __construct(
        private readonly LegacyUserService $legacyUserService,
        private readonly UserService $userService,
        private readonly UserValidator $userValidator,
    ) {
    }

    /**
     * Process a HTTP POST request used to create a User record.
     */
    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $result = $this->userValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        if ($result->isValid()) {
            try {
                $result->addData(
                    $this->userService->insert($data)
                );
            } catch (InvalidArgumentException $exception) {
                $result->addInternalError($exception->getMessage());
            }
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result,201);
    }

    /**
     * Fetches a single user resource by id.
     *
     * @param string $uuid - The user UUID identifier.
     */
    public function getOne(HttpRestRequest $request, string $uuid): ResponseInterface
    {
        try {
            $user = $this->userService->getOneByUuid($uuid);
            if (null === $user) {
                return RestControllerHelper::createProcessingResultResponse(
                    $request,
                    new ProcessingResult(),
                    404,
                );
            }
        } catch (InvalidArgumentException $exception) {
            return RestControllerHelper::createProcessingResultResponse(
                $request,
                ProcessingResult::createNewWithInternalError($exception->getMessage()),
            );
        }

        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData([
                $user,
            ]),
        );
    }

    /**
     * Returns user resources which match an optional search criteria.
     */
    public function getAll(HttpRestRequest $request, array $search = []): ResponseInterface
    {
        try {
            return RestControllerHelper::createProcessingResultResponse(
                $request,
                $this->legacyUserService->search($search),
                200,
                true,
            );
        } catch (InvalidArgumentException $exception) {
            return RestControllerHelper::createProcessingResultResponse(
                $request,
                ProcessingResult::createNewWithInternalError($exception->getMessage()),
            );
        }
    }

    public function delete(HttpRestRequest $request, string $uuid): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $this->userService->deleteOneByUuid($uuid);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
