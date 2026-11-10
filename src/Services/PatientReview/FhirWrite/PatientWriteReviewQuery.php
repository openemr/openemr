<?php

/**
 * The pending patient app writes staff review, oldest first, with the patient and the app.
 *
 * A request is marked a possible duplicate when an earlier pending request for the same
 * patient and resource type holds the same resource, e.g. an app that sent one reading twice.
 * Staff decide; nothing is merged or dropped.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class PatientWriteReviewQuery
{
    public const PAGE_SIZE = 100;

    public function __construct(
        private ReviewRequestMapper $mapper,
        private FhirWriteReviewHandler $handler,
    ) {
    }

    /**
     * @return array{items: list<PatientWriteReviewItem>, total: int}
     */
    public function pending(): array
    {
        $records = QueryUtils::fetchRecords(
            'SELECT r.*, p.`fname` AS `patient_fname`, p.`lname` AS `patient_lname`, p.`pubpid` AS `patient_pubpid`,'
            . ' c.`client_name` AS `app_name`, c.`is_confidential` AS `app_is_confidential`'
            . ' FROM `patient_review_request` AS r'
            . ' LEFT JOIN `patient_data` AS p ON p.`pid` = r.`pid`'
            . ' LEFT JOIN `oauth_clients` AS c ON c.`client_id` = r.`client_id`'
            . ' WHERE r.`type` = ? AND r.`status` = ?'
            // An integer constant, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY r.`created_at` ASC, r.`id` ASC LIMIT ' . self::PAGE_SIZE,
            [ReviewType::FhirWrite->value, ReviewStatus::Pending->value]
        );

        $items = [];
        /** @var array<string, int> $firstWithContent request id keyed by patient, type and content */
        $firstWithContent = [];
        /** @var array<string, ?string> $names questionnaire name keyed by uuid, so each is looked up once */
        $names = [];
        foreach ($records as $record) {
            $request = $this->mapper->toRequest($record);
            $json = json_encode($request->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $json = $json === false ? '' : $json;
            $contentKey = $request->pid . '|' . ($request->resourceType->value ?? '') . '|' . hash('sha256', $json);
            $duplicateOf = $firstWithContent[$contentKey] ?? null;
            $firstWithContent[$contentKey] ??= $request->id;

            $isResponse = $request->resourceType?->value === 'QuestionnaireResponse';
            $answers = $isResponse ? QuestionnaireResponseAnswers::of($request->payload) : ['rows' => [], 'tooDeep' => 0];
            $items[] = new PatientWriteReviewItem(
                $request,
                trim(self::text($record['patient_fname'] ?? null) . ' ' . self::text($record['patient_lname'] ?? null)),
                self::text($record['patient_pubpid'] ?? null),
                ($record['app_name'] ?? null) === null ? null : self::text($record['app_name']),
                self::clientType($record['app_is_confidential'] ?? null),
                $duplicateOf,
                $answers['rows'],
                $json,
                $this->handler->canApply($request),
                $isResponse ? self::questionnaireName($request->payload['questionnaire'] ?? null, $names) : null,
                $answers['tooDeep'],
            );
        }

        return ['items' => $items, 'total' => $this->countPending()];
    }

    /**
     * How many fhir-write requests are waiting, including those past the first page.
     */
    private function countPending(): int
    {
        $total = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `total` FROM `patient_review_request` WHERE `type` = ? AND `status` = ?',
            'total',
            [ReviewType::FhirWrite->value, ReviewStatus::Pending->value]
        );

        return is_int($total) || (is_string($total) && ctype_digit($total)) ? intval($total) : 0;
    }

    /**
     * The name of the local Questionnaire a canonical such as "Questionnaire/<uuid>|1" points
     * to, or null when it points elsewhere. The reference is resolved as approval resolves it
     * (UtilsService::parseReferenceString()), so an absolute URL to another server is not local.
     * Whether the pinned version still matches is for canApprove to say.
     *
     * @param array<string, ?string> $names names already looked up, by uuid
     */
    private static function questionnaireName(mixed $canonical, array &$names): ?string
    {
        if (!is_string($canonical) || $canonical === '') {
            return null;
        }
        $separator = strpos($canonical, '|');
        $reference = $separator === false ? $canonical : substr($canonical, 0, $separator);
        $uuid = UtilsService::parseReferenceString($reference, 'Questionnaire')['uuid'];
        if (!is_string($uuid) || !UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        $key = strtolower($uuid);
        if (!array_key_exists($key, $names)) {
            $name = QueryUtils::fetchSingleValue(
                'SELECT `name` FROM `questionnaire_repository` WHERE `uuid` = ?',
                'name',
                [UuidRegistry::uuidToBytes($uuid)]
            );
            $names[$key] = is_string($name) && $name !== '' ? $name : null;
        }

        return $names[$key];
    }

    /**
     * The client type from oauth_clients.is_confidential; null when the client is gone.
     */
    private static function clientType(mixed $isConfidential): ?PatientWriteClientType
    {
        return match (true) {
            $isConfidential === null => null,
            $isConfidential === 1, $isConfidential === '1' => PatientWriteClientType::Confidential,
            default => PatientWriteClientType::Public,
        };
    }

    /**
     * A database value as text; anything that isn't a string or integer is empty.
     */
    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) ? (string) $value : '');
    }
}
