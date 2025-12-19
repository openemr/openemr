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
use OpenEMR\Setting\Service\User\UserSpecificSettingSectionService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

class UserSettingSectionRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            UserSpecificSettingSectionService::getInstance(),
        );
    }

    public function __construct(
        private readonly UserSpecificSettingSectionService $userSettingSectionService,
    ) {
    }

    public function getUserSpecificSections(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->userSettingSectionService->getSectionSlugs()
            ),
            200,
            true
        );
    }
}
