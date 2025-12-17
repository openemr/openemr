<?php

/**
 * UserRestController - REST API for user related operations
 *
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
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
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\UserValidator;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\InvalidArgumentException;

class UserRestController
{
    private const SEARCH_FIELDS = [
        'id',
        'title',
        'fname',
        'lname',
        'mname',
        'federaltaxid',
        'federaldrugid',
        'upin',
        'facility_id',
        'facility',
        'npi',
        'email',
        'specialty',
        'billname',
        'url',
        'assistant',
        'organization',
        'valedictory',
        'street',
        'streetb',
        'city',
        'state',
        'zip',
        'phone',
        'fax',
        'phonew1',
        'phonecell',
        'notes',
        'state_license_number',
        'username',
    ];

    private readonly UserService $userService;

    private readonly UserValidator $userValidator;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->userValidator = UserValidator::getInstance();
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
        $result = new ProcessingResult();

        $user = $this->userService->getUserByUUID($uuid);
        if ([] === $user) {
            return RestControllerHelper::createProcessingResultResponse($request, $result, 404);
        }

        $result->setData([$user]);

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }

    /**
     * Returns user resources which match an optional search criteria.
     */
    public function getAll(HttpRestRequest $request, array $search = []): ResponseInterface
    {
        $search = array_filter(
            $search,
            static fn (string $key): bool => in_array($key, self::SEARCH_FIELDS, true),
            \ARRAY_FILTER_USE_KEY
        );

        return RestControllerHelper::createProcessingResultResponse(
            $request,
            $this->userService->search($search),
            200,
            true
        );
    }

    public function delete(HttpRestRequest $request, string $uuid): ResponseInterface
    {
        $result = new ProcessingResult();
        try {
            $this->userService->deleteByUuid($uuid);
        } catch (InvalidArgumentException $e) {
            $result->setValidationMessages([
                $e->getMessage()
            ]);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $result, 200);
    }
}
