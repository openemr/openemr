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

namespace OpenEMR\RestControllers\Standard\Admin\GlobalSetting;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingSectionService;
use OpenEMR\Setting\Service\Global\GlobalSettingService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

class AdminGlobalSettingSectionRestController
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            GlobalSettingSectionService::getInstance(),
        );
    }

    public function __construct(
        private readonly GlobalSettingSectionService $settingSectionService,
    ) {
    }

    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        return RestControllerHelper::createProcessingResultResponse(
            $request,
            ProcessingResult::createNewWithData(
                $this->settingSectionService->getSectionSlugs()
            ),
            200,
            true
        );
    }
}
