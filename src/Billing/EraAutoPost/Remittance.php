<?php

/**
 * The checks read from one 835 file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

final readonly class Remittance
{
    /**
     * @param list<RemittanceCheck> $checks
     * @param string                $error  the parser's complaint when the file could not be read to its end, or ''
     */
    public function __construct(
        public array $checks,
        public string $error,
    ) {
    }
}
