<?php

/**
 * The $reopen-charges operation on a patient's insurance policy: lists the
 * encounters billed before the policy was on file, and reopens the ones chosen
 * so they can be billed to the policy's payer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Billing\Reopen\PolicyReopenService;
use OpenEMR\Billing\Reopen\ReopenCandidate;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\PatientService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

final readonly class InsuranceReopenChargesRestController
{
    public function __construct(
        private PolicyReopenService $reopenService = new PolicyReopenService(),
        private PatientService $patientService = new PatientService(),
    ) {
    }

    /**
     * Lists the encounters to offer for reopening.
     */
    #[OA\Get(
        path: '/api/patient/{puuid}/insurance/{insuranceUuid}/$reopen-charges',
        description: 'Lists the encounters whose charges were billed before this insurance policy was on file: the policy is the one in effect at its level (primary, secondary, tertiary) on the date of service, the charges are marked billed, no claim for the encounter went to the policy\'s insurance company, every level before the policy\'s is closed, and the encounter has a balance. Requires the acct/bill/write ACL.',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'puuid',
                in: 'path',
                description: 'The uuid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'insuranceUuid',
                in: 'path',
                description: 'The insurance policy uuid for the patient.',
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
    public function getCandidates(HttpRestRequest $request, string $puuid, string $insuranceUuid): ResponseInterface
    {
        $result = new ProcessingResult();
        $pid = $this->pidFor($puuid);
        if ($pid === null) {
            $result->setValidationMessages(['puuid::INVALID_PUUID' => 'Patient uuid invalid']);
            return RestControllerHelper::createProcessingResultResponse($request, $result);
        }
        $result->addData(['encounters' => array_map(
            static fn(ReopenCandidate $candidate): array => $candidate->toArray(),
            $this->reopenService->candidates($pid, $insuranceUuid)
        )]);
        return RestControllerHelper::createProcessingResultResponse($request, $result);
    }

    /**
     * Reopens the chosen encounters.
     */
    #[OA\Post(
        path: '/api/patient/{puuid}/insurance/{insuranceUuid}/$reopen-charges',
        description: 'Reopens encounters for billing to this insurance policy\'s payer. Only encounters the GET of this operation lists are reopened; any other encounter in the request is left as it is. The charges go back to unbilled and the encounter to the policy\'s level, so the Billing Manager lists it with this policy\'s payer selected. Requires the acct/bill/write ACL.',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'puuid',
                in: 'path',
                description: 'The uuid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'insuranceUuid',
                in: 'path',
                description: 'The insurance policy uuid for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['encounters'],
                    properties: [
                        new OA\Property(
                            property: 'encounters',
                            description: 'The encounter numbers to reopen.',
                            type: 'array',
                            items: new OA\Items(type: 'integer')
                        ),
                    ],
                    example: ['encounters' => [1234, 1250]]
                )
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function reopen(HttpRestRequest $request, string $puuid, string $insuranceUuid): ResponseInterface
    {
        $result = new ProcessingResult();
        $pid = $this->pidFor($puuid);
        if ($pid === null) {
            $result->setValidationMessages(['puuid::INVALID_PUUID' => 'Patient uuid invalid']);
            return RestControllerHelper::createProcessingResultResponse($request, $result);
        }
        $encounters = self::encountersIn($request->getContent());
        if ($encounters === null) {
            $result->setValidationMessages(['encounters' => 'A list of encounter numbers is required']);
            return RestControllerHelper::createProcessingResultResponse($request, $result);
        }
        $result->addData(['reopened' => $this->reopenService->reopen($pid, $insuranceUuid, $encounters)]);
        return RestControllerHelper::createProcessingResultResponse($request, $result);
    }

    private function pidFor(string $puuid): ?int
    {
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return null;
        }
        return EffectiveInsuranceRule::storedId($this->patientService->getPidByUuid(UuidRegistry::uuidToBytes($puuid)));
    }

    /**
     * The encounter numbers in a {"encounters": [...]} request body, or null
     * when the body isn't one.
     *
     * @return ?list<int>
     */
    private static function encountersIn(string $body): ?array
    {
        $decoded = json_decode($body, true);
        $listed = is_array($decoded) ? ($decoded['encounters'] ?? null) : null;
        if (!is_array($listed)) {
            return null;
        }
        $encounters = [];
        foreach ($listed as $value) {
            $encounter = EffectiveInsuranceRule::storedId($value);
            if ($encounter === null) {
                return null;
            }
            $encounters[] = $encounter;
        }
        return $encounters;
    }
}
