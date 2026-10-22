<?php

/**
 * Links audit checksums so a missing row breaks the next one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class AuditChain
{
    /**
     * The link value that comes before the first chained audit row.
     * Rows written before chaining are not part of this value.
     */
    public static function genesis(): string
    {
        return hash('sha3-512', 'openemr-audit-chain-v1');
    }

    /**
     * The next link. The key is not stored in the database, so repairing a
     * gap requires the site key as well as the row contents.
     */
    public static function link(string $previous, string $logId, string $checksum, string $key): string
    {
        return hash_hmac('sha3-512', $previous . '|' . $logId . '|' . $checksum, $key);
    }
}
