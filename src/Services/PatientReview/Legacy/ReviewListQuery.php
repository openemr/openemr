<?php

/**
 * The staff review list (Portal > Review Audits): requests with their patient and the
 * patient's provider, filtered, sorted and paged the way the list page asks.
 *
 * The page is JavaScript written against onsite_portal_activity joined to patient_data and
 * users; each row is returned in that shape (LegacyActivityRow::toModel() plus the patient and
 * provider fields), so the page works unchanged.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Legacy;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class ReviewListQuery
{
    /** Patient and provider columns, keyed by the field name the page reads. */
    private const PERSON_FIELDS = [
        'title' => 'p.`title`',
        'fname' => 'p.`fname`',
        'lname' => 'p.`lname`',
        'mname' => 'p.`mname`',
        'dob' => 'p.`DOB`',
        'ss' => 'p.`ss`',
        'street' => 'p.`street`',
        'postalCode' => 'p.`postal_code`',
        'city' => 'p.`city`',
        'state' => 'p.`state`',
        'referrerid' => 'p.`referrerID`',
        'providerid' => 'p.`providerID`',
        'refProviderid' => 'p.`ref_providerID`',
        'pubpid' => 'p.`pubpid`',
        'careTeam' => 'p.`care_team_provider`',
        'username' => 'u.`username`',
        'authorized' => 'u.`authorized`',
        'ufname' => 'u.`fname`',
        'umname' => 'u.`mname`',
        'ulname' => 'u.`lname`',
        'facility' => 'u.`facility`',
        'active' => 'u.`active`',
        'utitle' => 'u.`title`',
        'physicianType' => 'u.`physician_type`',
    ];

    /** Sortable columns, keyed by the (case-insensitive) property the page sends. */
    private const ORDER_COLUMNS = [
        'id' => 'r.`id`',
        'date' => 'r.`created_at`',
        'patientid' => 'r.`pid`',
        'activity' => 'r.`type`',
        'status' => 'r.`status`',
        'narrative' => 'r.`summary`',
        'actiontakentime' => 'r.`reviewed_at`',
        'actionuser' => 'r.`reviewed_by`',
        'fname' => 'p.`fname`',
        'lname' => 'p.`lname`',
        'mname' => 'p.`mname`',
        'dob' => 'p.`DOB`',
        'pubpid' => 'p.`pubpid`',
        'ulname' => 'u.`lname`',
    ];

    private const FROM = ' FROM `patient_review_request` AS r'
        . ' LEFT JOIN `patient_data` AS p ON p.`pid` = r.`pid`'
        . ' LEFT JOIN `users` AS u ON u.`id` = p.`providerID`';

    public function __construct(private ReviewRequestMapper $mapper)
    {
    }

    /**
     * @param ?string $filter matched against the summary, patient names, pubpid and type
     * @param ?string $orderBy a property name from the page; unknown names sort by date
     * @param int $page 1-based
     * @return array{rows: list<array<string, mixed>>, totalResults: int, totalPages: int, pageSize: int, currentPage: int}
     */
    public function page(
        ReviewStatus $status,
        ?ReviewType $type,
        ?string $filter,
        ?string $orderBy,
        bool $orderDesc,
        int $page,
        int $pageSize,
    ): array {
        $where = ' WHERE r.`status` = ?';
        $binds = [$status->value];
        // With no type, the portal's own types only: other requests (FHIR writes) have no
        // action on this list and are not in its alert count.
        $types = $type !== null ? [$type] : PortalReviewWorkflows::LIST_TYPES;
        $where .= ' AND r.`type` IN (' . implode(', ', array_fill(0, count($types), '?')) . ')';
        foreach ($types as $listed) {
            $binds[] = $listed->value;
        }
        if ($filter !== null && trim($filter) !== '') {
            $like = '%' . trim($filter) . '%';
            $where .= ' AND (r.`summary` LIKE ? OR r.`type` LIKE ? OR p.`fname` LIKE ? OR p.`lname` LIKE ? OR p.`pubpid` LIKE ?)';
            array_push($binds, $like, $like, $like, $like, $like);
        }

        $countRow = QueryUtils::querySingleRow('SELECT COUNT(*) AS `total`' . self::FROM . $where, $binds);
        $totalValue = is_array($countRow) ? ($countRow['total'] ?? 0) : 0;
        $total = is_int($totalValue) || (is_string($totalValue) && ctype_digit($totalValue)) ? intval($totalValue) : 0;

        $pageSize = max(1, $pageSize);
        $totalPages = max(1, intdiv($total + $pageSize - 1, $pageSize));
        $page = min(max(1, $page), $totalPages);
        $column = self::ORDER_COLUMNS[strtolower($orderBy ?? '')] ?? self::ORDER_COLUMNS['date'];

        $select = 'SELECT r.*';
        foreach (self::PERSON_FIELDS as $field => $expression) {
            $select .= ', ' . $expression . ' AS `person_' . $field . '`';
        }
        // ORDER BY and LIMIT come from the whitelist and integers above, never from request text.
        $sql = $select . self::FROM . $where
            . ' ORDER BY ' . $column . ($orderDesc ? ' DESC' : ' ASC') . ', r.`id` DESC'
            . ' LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize);

        $rows = [];
        foreach (QueryUtils::fetchRecords($sql, $binds) as $record) {
            $row = LegacyActivityRow::fromRequest($this->mapper->toRequest($record))->toModel();
            foreach (array_keys(self::PERSON_FIELDS) as $field) {
                $row[$field] = $record['person_' . $field] ?? null;
            }
            $rows[] = $row;
        }

        return [
            'rows' => $rows,
            'totalResults' => $total,
            'totalPages' => $totalPages,
            'pageSize' => $pageSize,
            'currentPage' => $page,
        ];
    }

    /**
     * The review status a legacy status string the page sends stands for.
     */
    public static function statusFromLegacy(?string $legacy): ReviewStatus
    {
        foreach (ReviewStatus::cases() as $status) {
            if (LegacyActivityRow::status($status) === $legacy) {
                return $status;
            }
        }

        return ReviewStatus::Pending;
    }
}
