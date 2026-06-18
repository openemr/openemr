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
        $stub = self::newStub();
        $stub->procs = $procs;
        $stub->payers = $payers;
        return $stub;
    }

    /**
     * A Claim whose real constructor (which hits the database) is bypassed.
     * procCount() and payerCount() read only the procs and payers properties,
     * so the stub seeds nothing else.
     */
    private static function newStub(): Claim
    {
        return new class extends Claim {
            public function __construct()
            {
            }
        };
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

    /**
     * Regression guard for #12331: before #12525, procCount() called
     * count($this->procs) unconditionally. The procs property has no default,
     * so it stays null until getProcsAndDiags() populates it, and count(null)
     * throws a TypeError on PHP 8. The is_array() guard must return 0 instead.
     */
    public function testProcCountIsZeroWhenProcsIsNull(): void
    {
        $claim = self::newStub();
        $claim->procs = null;
        $this->assertSame(0, $claim->procCount());
    }

    /**
     * Regression guard for #12331: payerCount() must return 0 rather than throw
     * a TypeError when payers is null (the failure mode #12525 fixed).
     */
    public function testPayerCountIsZeroWhenPayersIsNull(): void
    {
        $claim = self::newStub();
        $claim->payers = null;
        $this->assertSame(0, $claim->payerCount());
    }
}
