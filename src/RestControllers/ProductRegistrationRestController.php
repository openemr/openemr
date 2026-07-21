<?php

/**
 * ProductRegistrationRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\ProductRegistrationService;

class ProductRegistrationRestController
{
    private $productRegistrationService;

    public function __construct()
    {
        $this->productRegistrationService = new ProductRegistrationService();
    }

    /**
     * Retrieves the OpenEMR product registration information.
     */
    #[OA\Get(
        path: '/api/product',
        description: 'Retrieves the OpenEMR product registration information',
        tags: ['standard'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne()
    {
        $serviceResult = $this->productRegistrationService->getRegistrationStatus();
        return RestControllerHelper::responseHandler($serviceResult, ["status" => $serviceResult], 200);
    }
}
