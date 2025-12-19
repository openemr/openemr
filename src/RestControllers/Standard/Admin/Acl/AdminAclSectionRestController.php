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
use OpenEMR\Services\Acl\AclSectionService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

class AdminAclSectionRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclSectionService::getInstance(),
        );
    }

    public function __construct(
        private readonly AclSectionService $sectionService
    ) {
    }

    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->sectionService->getAll()
            ),
            200,
            true
        );
    }
}
