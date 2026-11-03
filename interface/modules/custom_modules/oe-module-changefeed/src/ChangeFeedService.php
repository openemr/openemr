<?php

/**
 * Turns raw changefeed_log rows into a page of FHIR resource references.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

use DomainException;
use OpenEMR\Common\Database\QueryUtils;

final class ChangeFeedService
{
    private const WATERMARK_LAG_SECONDS = 2;
    private const DEFAULT_LIMIT = 100;
    private const MAX_LIMIT = 1000;

    /** @var array<string, WatchedResource> keyed by source table name */
    private array $watchedByTable;

    public function __construct(
        private readonly ChangeFeedRepository $repository = new ChangeFeedRepository(),
        ?TriggerManager $triggerManager = null,
    ) {
        $manager = $triggerManager ?? new TriggerManager();
        $this->watchedByTable = [];
        foreach ($manager->watchedResources() as $resource) {
            $this->watchedByTable[$resource->table] = $resource;
        }
    }

    /**
     * @return array{
     *     since: int,
     *     cursor: int,
     *     watermark: int,
     *     count: int,
     *     changes: list<array{resourceType: string, id: string, operation: string, cursor: int, changedAt: string}>
     * }
     */
    public function getChanges(int $since, ?int $limit = null): array
    {
        if ($since < 0) {
            throw new DomainException('since cursor must be >= 0');
        }

        $limit = $this->clampLimit($limit);
        $watermark = $this->repository->watermark(self::WATERMARK_LAG_SECONDS);

        if ($watermark <= $since) {
            return $this->page($since, $since, $watermark, []);
        }

        $rows = $this->repository->readSince($since, $watermark, $limit);

        $changes = [];
        $cursor = $since;
        foreach ($rows as $row) {
            // Advance the cursor for every row read, even ones we cannot map to
            // a FHIR reference - otherwise an unmappable row would be re-served
            // forever and the feed would never progress.
            $cursor = (int) ($row['id'] ?? $cursor);
            $entry = $this->mapRow($row);
            if ($entry !== null) {
                $changes[] = $entry;
            }
        }

        return $this->page($since, $cursor, $watermark, $changes);
    }

    /**
     * @param array<string, ?string> $row
     * @return array{resourceType: string, id: string, operation: string, cursor: int, changedAt: string}|null
     */
    private function mapRow(array $row): ?array
    {
        $uuid = $this->resolveUuid($row);
        if ($uuid === null) {
            return null;
        }

        return [
            'resourceType' => (string) $row['resource_type'],
            'id' => $uuid,
            'operation' => ChangeOperation::from((string) $row['op'])->value,
            'cursor' => (int) $row['id'],
            'changedAt' => (string) $row['changed_at'],
        ];
    }

    /**
     * The FHIR logical id (canonical uuid) for a change row. Prefer the uuid the
     * trigger captured; if it was null at capture time (a row created before its
     * uuid was assigned), resolve it from the still-present source row. Deletes
     * cannot be resolved this way, so a delete with no captured uuid is skipped.
     *
     * @param array<string, ?string> $row
     */
    private function resolveUuid(array $row): ?string
    {
        $captured = $row['row_uuid'] ?? null;
        if (is_string($captured) && $captured !== '') {
            return Uuid::fromHex($captured);
        }

        if (($row['op'] ?? null) === ChangeOperation::Delete->value) {
            return null;
        }

        $watched = $this->watchedByTable[(string) ($row['resource_table'] ?? '')] ?? null;
        if ($watched === null) {
            return null;
        }

        $hex = QueryUtils::fetchSingleValue(
            sprintf(
                'SELECT HEX(`%s`) AS u FROM `%s` WHERE `%s` = ?',
                $watched->uuidColumn,
                $watched->table,
                $watched->primaryKeyColumn
            ),
            'u',
            [$row['row_pk']]
        );

        return is_string($hex) && $hex !== '' ? Uuid::fromHex($hex) : null;
    }

    private function clampLimit(?int $limit): int
    {
        if ($limit === null || $limit <= 0) {
            return self::DEFAULT_LIMIT;
        }

        return min(self::MAX_LIMIT, $limit);
    }

    /**
     * @param list<array{resourceType: string, id: string, operation: string, cursor: int, changedAt: string}> $changes
     * @return array{since: int, cursor: int, watermark: int, count: int, changes: list<array{resourceType: string, id: string, operation: string, cursor: int, changedAt: string}>}
     */
    private function page(int $since, int $cursor, int $watermark, array $changes): array
    {
        return [
            'since' => $since,
            'cursor' => $cursor,
            'watermark' => $watermark,
            'count' => count($changes),
            'changes' => $changes,
        ];
    }
}
