<?php

/**
 * Walks chained audit checksums and reports the first break in each gap.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class AuditChainChecker
{
    private const int MESSAGE_LIMIT = 20;

    private int $omitted = 0;

    /** @var list<AuditChainFailureItem> */
    private array $failures = [];

    public function __construct(private string $previous, private readonly string $key)
    {
    }

    public function add(string $logId, string $checksum, ?string $chain): void
    {
        if ($chain === null || $chain === '') {
            $this->note(AuditChainFailure::Missing, $logId);

            return;
        }

        $expected = AuditChain::link($this->previous, $logId, $checksum, $this->key);
        if (strlen($expected) !== strlen($chain) || !hash_equals($expected, $chain)) {
            $this->note(AuditChainFailure::Break, $logId);
        }
        $this->previous = $chain;
    }

    /**
     * The anchor names the newest chained row. A missing or different row
     * means that newest record was removed or rewritten.
     */
    public static function anchorDeleted(string $anchorLogId, string $anchorChain, ?string $rowChain): bool
    {
        if ($anchorLogId === '' || $anchorLogId === '0') {
            return false;
        }
        if ($rowChain === null || $rowChain === '') {
            return true;
        }
        if (strlen($anchorChain) !== strlen($rowChain)) {
            return true;
        }

        return !hash_equals($anchorChain, $rowChain);
    }

    /**
     * @return list<AuditChainFailureItem>
     */
    public function failures(): array
    {
        $items = $this->failures;
        if ($this->omitted > 0) {
            $items[] = new AuditChainFailureItem(AuditChainFailure::Omitted);
        }

        return $items;
    }

    private function note(AuditChainFailure $kind, string $logId): void
    {
        if (count($this->failures) >= self::MESSAGE_LIMIT) {
            $this->omitted++;

            return;
        }
        $this->failures[] = new AuditChainFailureItem($kind, $logId);
    }
}
