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
        $min = QueryUtils::querySingleRow(
            'SELECT MIN(c.log_id) AS min_id'
            . ' FROM log_comment_encrypt c'
            . ' INNER JOIN log l ON l.id = c.log_id'
            . ' WHERE c.version >= 5 AND l.date >= ? AND l.date <= ?',
            $bounds,
            false,
        );

        $previous = AuditChain::genesis();
        $minId = self::scalar(is_array($min) ? ($min['min_id'] ?? null) : null);
        if ($minId !== '') {
            $earlier = QueryUtils::querySingleRow(
                'SELECT c.chain FROM log_comment_encrypt c'
                . ' WHERE c.version >= 5 AND c.chain IS NOT NULL AND c.chain != \'\''
                . ' AND c.log_id < ?'
                . ' ORDER BY c.log_id DESC LIMIT 1',
                [$minId],
                false,
            );
            $earlierChain = is_array($earlier) ? ($earlier['chain'] ?? null) : null;
            if (is_string($earlierChain) && $earlierChain !== '') {
                $previous = $earlierChain;
            }
        }

        $checker = new AuditChainChecker($previous, $key);
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT c.log_id, c.checksum, c.chain'
            . ' FROM log_comment_encrypt c'
            . ' INNER JOIN log l ON l.id = c.log_id'
            . ' WHERE c.version >= 5 AND l.date >= ? AND l.date <= ?'
            . ' ORDER BY c.log_id ASC',
            $bounds,
        );
        foreach ($rows as $row) {
            $checksum = $row['checksum'] ?? null;
            $chain = $row['chain'] ?? null;
            $checker->add(
                self::scalar($row['log_id'] ?? null),
                is_string($checksum) ? $checksum : '',
                is_string($chain) ? $chain : null,
            );
        }

        $failures = $checker->failures();
        $anchor = QueryUtils::querySingleRow(
            'SELECT log_id, chain FROM log_chain_anchor WHERE id = 1',
            [],
            false,
        );
        if (!is_array($anchor)) {
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

    private static function scalar(mixed $value): string
    {
        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        return '';
    }
}
