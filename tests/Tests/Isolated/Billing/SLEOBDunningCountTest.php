<?php

/**
 * Isolated tests for SLEOB::dunningCount(), which decides whether the
 * statement list treats an invoice as due from insurance or from the patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\SLEOB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SLEOBDunningCountTest extends TestCase
{
    /**
     * @return array<string, array{int, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invoiceProvider(): array
    {
        return [
            // statements sent, last level closed, payers on file => dunning count
            'new invoice, primary open' => [0, 0, 1, -1],
            'new invoice, primary and secondary open' => [0, 0, 2, -2],
            'primary closed, secondary open' => [0, 1, 2, -1],
            'all insurance closed, no statement yet' => [0, 2, 2, 0],
            'self pay' => [0, 0, 0, 0],
            'patient sent two statements' => [2, 1, 1, 2],
            'moved back to primary after a statement' => [1, 0, 1, -1],
            'moved back to secondary after two statements' => [2, 1, 2, -1],
            'closed beyond the payers on file' => [0, 3, 1, 0],
        ];
    }

    #[Test]
    #[DataProvider('invoiceProvider')]
    public function dunningCount(int $statementCount, int $lastLevelClosed, int $payerCount, int $expected): void
    {
        $this->assertSame($expected, SLEOB::dunningCount($statementCount, $lastLevelClosed, $payerCount));
    }
}
