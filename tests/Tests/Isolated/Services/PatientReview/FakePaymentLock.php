<?php

/**
 * A patient payment lock for tests: records what was asked of it and can be made busy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\Portal\PatientPaymentLock;

final class FakePaymentLock implements PatientPaymentLock
{
    /** @var list<string> */
    public array $calls = [];

    public function __construct(private readonly bool $busy = false)
    {
    }

    /**
     * Records the call, or throws as the real lock does when it is busy.
     */
    public function acquire(int $pid): void
    {
        $this->calls[] = 'acquire ' . $pid;
        if ($this->busy) {
            throw new \RuntimeException('busy');
        }
    }

    public function release(): void
    {
        $this->calls[] = 'release';
    }
}
