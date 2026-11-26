<?php

/**
 * Reads the audit chain for the tamper report.
 *
 * Queries go through the no-log path. A logged read would append a new link
 * while the chain was being checked.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

final class AuditChainReader
{
    /**
     * @return list<AuditChainFailureItem>
     */
    public static function failures(string $start, string $end): array
    {
        $found = QueryUtils::querySingleRow("SHOW TABLES LIKE 'log_chain_anchor'", [], false);
        if (!is_array($found)) {
            return [new AuditChainFailureItem(AuditChainFailure::Upgrade)];
        }

        try {
            $key = AuditChainKey::fromSiteDirectory(
                OEGlobalsBag::getInstance()->getString('OE_SITE_DIR'),
                false,
            )->value();
        } catch (\RuntimeException) {
            return [new AuditChainFailureItem(AuditChainFailure::Key)];
        }

        try {
            return self::check($start, $end, $key);
        } catch (SqlQueryException) {
            return [new AuditChainFailureItem(AuditChainFailure::Upgrade)];
        }
    }

    /**
     * @return list<AuditChainFailureItem>
     */
    private static function check(string $start, string $end, string $key): array
    {
        $bounds = [$start, $end];
        // The date window picks the first and last log id to report. The walk
        // then includes every link between those ids. A row whose date falls
        // outside the window can still sit between them, and dropping it
        // would look like a break in the chain. A chain row whose version
        // was lowered still belongs in that window. Rows from before the
        // chain have no chain value, so they stay out.
        $span = QueryUtils::querySingleRow(
            'SELECT MIN(c.log_id) AS min_id, MAX(c.log_id) AS max_id'
            . ' FROM log_comment_encrypt c'
            . ' INNER JOIN log l ON l.id = c.log_id'
            . " WHERE (c.version >= 5 OR (c.chain IS NOT NULL AND c.chain <> ''))"
            . ' AND l.date >= ? AND l.date <= ?',
            $bounds,
            false,
        );

        $previous = AuditChain::genesis();
        $minId = self::scalar(is_array($span) ? ($span['min_id'] ?? null) : null);
        $maxId = self::scalar(is_array($span) ? ($span['max_id'] ?? null) : null);
        $checker = new AuditChainChecker($previous, $key);
        if ($minId !== '' && $maxId !== '') {
            $earlier = QueryUtils::querySingleRow(
                'SELECT c.chain FROM log_comment_encrypt c'
                . ' WHERE c.chain IS NOT NULL AND c.chain != \'\''
                . ' AND c.log_id < ?'
                . ' ORDER BY c.log_id DESC LIMIT 1',
                [$minId],
                false,
            );
            $earlierChain = is_array($earlier) ? ($earlier['chain'] ?? null) : null;
            if (is_string($earlierChain) && $earlierChain !== '') {
                $checker = new AuditChainChecker($earlierChain, $key);
            }

            $rows = QueryUtils::fetchRecordsNoLog(
                'SELECT c.log_id, c.checksum, c.checksum_api, c.chain'
                . ' FROM log_comment_encrypt c'
                . ' WHERE c.chain IS NOT NULL AND c.chain != \'\''
                . ' AND c.log_id >= ? AND c.log_id <= ?'
                . ' ORDER BY c.log_id ASC',
                [$minId, $maxId],
            );
            foreach ($rows as $row) {
                self::addRow($checker, $row);
            }
            $next = QueryUtils::querySingleRow(
                'SELECT c.log_id, c.checksum, c.checksum_api, c.chain FROM log_comment_encrypt c'
                . " WHERE c.chain IS NOT NULL AND c.chain != '' AND c.log_id > ?"
                . ' ORDER BY c.log_id ASC LIMIT 1',
                [$maxId],
                false,
            );
            if (is_array($next)) {
                self::addRow($checker, $next);
            }
        }

        $failures = $checker->failures();
        $anchor = QueryUtils::querySingleRow(
            'SELECT log_id, chain FROM log_chain_anchor WHERE id = 1',
            [],
            false,
        );
        if (!is_array($anchor)) {
            if ($minId !== '') {
                $failures[] = new AuditChainFailureItem(AuditChainFailure::Anchor);
            }

            return $failures;
        }

        $anchorLogId = self::scalar($anchor['log_id'] ?? null);
        $anchorChain = $anchor['chain'] ?? null;
        if ($anchorLogId === '' || $anchorLogId === '0' || !is_string($anchorChain)) {
            return $failures;
        }

        $stored = QueryUtils::querySingleRow(
            'SELECT chain FROM log_comment_encrypt WHERE log_id = ?',
            [$anchorLogId],
            false,
        );
        $storedChain = is_array($stored) ? ($stored['chain'] ?? null) : null;
        if (AuditChainChecker::anchorDeleted($anchorLogId, $anchorChain, is_string($storedChain) ? $storedChain : null)) {
            $failures[] = new AuditChainFailureItem(AuditChainFailure::Anchor);
        }

        return $failures;
    }

    /**
     * @param array<mixed> $row
     */
    private static function addRow(AuditChainChecker $checker, array $row): void
    {
        $checksum = self::column($row, 'checksum');
        $chain = self::column($row, 'chain');
        $checksumApi = self::column($row, 'checksum_api');
        $checker->add(
            self::scalar(self::column($row, 'log_id')),
            is_string($checksum) ? $checksum : '',
            is_string($chain) ? $chain : null,
            is_string($checksumApi) ? $checksumApi : '',
        );
    }

    /**
     * @param array<mixed> $row
     */
    private static function column(array $row, string $name): mixed
    {
        foreach ($row as $key => $value) {
            if ($key === $name) {
                return $value;
            }
        }

        return null;
    }

    private static function scalar(mixed $value): string
    {
        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        return '';
    }
}
