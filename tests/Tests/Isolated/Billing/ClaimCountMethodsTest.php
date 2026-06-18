<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for Claim::procCount() and Claim::payerCount().
 */
class ClaimCountMethodsTest extends TestCase
{
    /**
     * @param list<array<string, mixed>> $procs
     * @param list<array<string, mixed>> $payers
     */
    private function makeClaim(array $procs = [], array $payers = []): Claim
    {
        // Bypass the real constructor, which hits the database. procCount()
        // and payerCount() read only the procs and payers properties, so the
        // stub seeds nothing else.
        $stub = new class extends Claim {
            public function __construct()
            {
            }
        };

        $stub->procs = $procs;
        $stub->payers = $payers;
        return $stub;
    }

    public function testProcCountIsZeroWhenNoProcs(): void
    {
        $claim = $this->makeClaim(procs: []);
        $this->assertSame(0, $claim->procCount());
    }

    public function testProcCountIsOneForSingleProc(): void
    {
        $claim = $this->makeClaim(procs: [['code' => '99213', 'fee' => '100.00']]);
        $this->assertSame(1, $claim->procCount());
    }

    public function testProcCountReflectsMultipleProcs(): void
    {
        $procs = [
            ['code' => '99213', 'fee' => '100.00'],
            ['code' => '93000', 'fee' => '50.00'],
            ['code' => '36415', 'fee' => '25.00'],
        ];
        $claim = $this->makeClaim(procs: $procs);
        $this->assertSame(3, $claim->procCount());
    }

    public function testPayerCountIsZeroWhenNoPayers(): void
    {
        $claim = $this->makeClaim(payers: []);
        $this->assertSame(0, $claim->payerCount());
    }

    public function testPayerCountIsOneForPrimaryOnly(): void
    {
        $claim = $this->makeClaim(payers: [['data' => ['type' => 'primary'], 'company' => [], 'object' => null]]);
        $this->assertSame(1, $claim->payerCount());
    }

    public function testPayerCountIsTwoForPrimaryAndSecondary(): void
    {
        $payers = [
            ['data' => ['type' => 'primary'], 'company' => [], 'object' => null],
            ['data' => ['type' => 'secondary'], 'company' => [], 'object' => null],
        ];
        $claim = $this->makeClaim(payers: $payers);
        $this->assertSame(2, $claim->payerCount());
    }

    public function testPayerCountIsThreeForAllPayerLevels(): void
    {
        $payers = [
            ['data' => ['type' => 'primary'], 'company' => [], 'object' => null],
            ['data' => ['type' => 'secondary'], 'company' => [], 'object' => null],
            ['data' => ['type' => 'tertiary'], 'company' => [], 'object' => null],
        ];
        $claim = $this->makeClaim(payers: $payers);
        $this->assertSame(3, $claim->payerCount());
    }
}
