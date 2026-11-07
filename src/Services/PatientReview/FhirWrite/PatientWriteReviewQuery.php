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
        foreach ($records as $record) {
            $request = $this->mapper->toRequest($record);
            $json = json_encode($request->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $json = $json === false ? '' : $json;
            $contentKey = $request->pid . '|' . ($request->resourceType->value ?? '') . '|' . hash('sha256', $json);
            $duplicateOf = $firstWithContent[$contentKey] ?? null;
            $firstWithContent[$contentKey] ??= $request->id;

            $isResponse = $request->resourceType?->value === 'QuestionnaireResponse';
            $items[] = new PatientWriteReviewItem(
                $request,
                trim(self::text($record['patient_fname'] ?? null) . ' ' . self::text($record['patient_lname'] ?? null)),
                self::text($record['patient_pubpid'] ?? null),
                ($record['app_name'] ?? null) === null ? null : self::text($record['app_name']),
                self::clientType($record['app_is_confidential'] ?? null),
                $duplicateOf,
                $isResponse ? QuestionnaireResponseAnswers::of($request->payload) : [],
                $json,
                $this->handler->canApply($request),
                $isResponse ? self::questionnaireName($request->payload['questionnaire'] ?? null) : null,
            );
        }

        return ['items' => $items, 'total' => $this->countPending()];
    }

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
     * to, or null when it points elsewhere.
     */
    private static function questionnaireName(mixed $canonical): ?string
    {
        $pattern = '#(?:^|/)Questionnaire/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})(?:\|.*)?$#';
        if (!is_string($canonical) || preg_match($pattern, $canonical, $match) !== 1) {
            return null;
        }
        $name = QueryUtils::fetchSingleValue(
            'SELECT `name` FROM `questionnaire_repository` WHERE `uuid` = ?',
            'name',
            [UuidRegistry::uuidToBytes($match[1])]
        );

        return is_string($name) && $name !== '' ? $name : null;
    }

    private static function clientType(mixed $isConfidential): ?PatientWriteClientType
    {
        return match (true) {
            $isConfidential === null => null,
            $isConfidential === 1, $isConfidential === '1' => PatientWriteClientType::Confidential,
            default => PatientWriteClientType::Public,
        };
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) ? (string) $value : '');
    }
}
