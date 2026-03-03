<?php

/**
 * ImmunizationRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\ImmunizationService;
use OpenEMR\RestControllers\RestControllerHelper;

class ImmunizationRestController
{
    private $immunizationService;

    /**
     * White list of immunization search fields
     */
    private const WHITELISTED_FIELDS = [];

    public function __construct()
    {
        $this->immunizationService = new ImmunizationService();
    }

    /**
     * Fetches a single immunization resource by id.
     * @param $uuid- The immunization uuid identifier in string format.
     */
    #[OA\Get(
        path: '/api/immunization/{uuid}',
        description: 'Retrieves a immunization',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the immunization.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne($uuid)
    {
        $processingResult = $this->immunizationService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns immunization resources which match an optional search criteria.
     */
    #[OA\Get(
        path: '/api/immunization',
        description: 'Retrieves a list of immunizations',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'patient_id',
                in: 'query',
                description: 'The pid for the patient.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'id',
                in: 'query',
                description: 'The id for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'uuid',
                in: 'query',
                description: 'The uuid for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'administered_date',
                in: 'query',
                description: 'The administered date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'immunization_id',
                in: 'query',
                description: 'The immunization list_id for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'cvx_code',
                in: 'query',
                description: 'The cvx code for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'manufacturer',
                in: 'query',
                description: 'The manufacturer for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lot_number',
                in: 'query',
                description: 'The lot number for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'administered_by_id',
                in: 'query',
                description: 'The administered by id for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'administered_by',
                in: 'query',
                description: 'The administered by for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'education_date',
                in: 'query',
                description: 'The education date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'vis_date',
                in: 'query',
                description: 'The vis date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'note',
                in: 'query',
                description: 'The note for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'create_date',
                in: 'query',
                description: 'The create date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'update_date',
                in: 'query',
                description: 'The update date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'created_by',
                in: 'query',
                description: 'The created_by for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'updated_by',
                in: 'query',
                description: 'The updated_by for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'amount_administered',
                in: 'query',
                description: 'The amount administered for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'amount_administered_unit',
                in: 'query',
                description: 'The amount administered unit for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'expiration_date',
                in: 'query',
                description: 'The expiration date for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'route',
                in: 'query',
                description: 'The route for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'administration_site',
                in: 'query',
                description: 'The administration site for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'added_erroneously',
                in: 'query',
                description: 'The added_erroneously for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'external_id',
                in: 'query',
                description: 'The external_id for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'completion_status',
                in: 'query',
                description: 'The completion status for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'information_source',
                in: 'query',
                description: 'The information source for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'refusal_reason',
                in: 'query',
                description: 'The refusal reason for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'ordering_provider',
                in: 'query',
                description: 'The ordering provider for the immunization.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll($search = [])
    {
        $validSearchFields = $this->immunizationService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->immunizationService->getAll($validSearchFields);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
