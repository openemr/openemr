<?php

/**
 * Tests for the timely filing candidate list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\TimelyFilingCandidates;
use PHPUnit\Framework\TestCase;

class TimelyFilingCandidatesTest extends TestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tf_' . getmypid() . '_' . mt_rand();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach ((array) scandir($this->dir) as $name) {
            if (is_file($this->dir . DIRECTORY_SEPARATOR . $name)) {
                unlink($this->dir . DIRECTORY_SEPARATOR . $name);
            }
        }
        rmdir($this->dir);
    }

    private static function claim(string $reference, string $reason): array
    {
        return [
            'our_claim_id' => $reference,
            'svc' => [['adj' => [['group_code' => 'CO', 'reason_code' => $reason, 'amount' => '257.00']]]],
        ];
    }

    private static function parser(array ...$claims): callable
    {
        return static function (string $path, callable $onClaim) use ($claims): void {
            foreach ($claims as $claim) {
                $onClaim($claim);
            }
        };
    }

    public function testReason29IsTimelyFiling(): void
    {
        $this->assertTrue(TimelyFilingCandidates::hasTimelyFiling(self::claim('100-200', '29')));
        $this->assertFalse(TimelyFilingCandidates::hasTimelyFiling(self::claim('100-200', '45')));
        $this->assertFalse(TimelyFilingCandidates::hasTimelyFiling([]));
        $this->assertFalse(TimelyFilingCandidates::hasTimelyFiling(['svc' => ['x', ['adj' => ['y']]]]));
    }

    public function testARemittanceDenialListsItsEncounter(): void
    {
        file_put_contents($this->dir . '/r.835', 'x');
        $out = TimelyFilingCandidates::fromRemittances(
            [['FileName' => 'r.835', 'CLM01' => '100-200', 'Payer' => 'PLAN']],
            [['FileName' => 'r.835', 'Date' => '20260710']],
            $this->dir,
            self::parser(self::claim('100-200', '29'))
        );
        $this->assertSame([
            ['pid' => 100, 'encounter' => 200, 'payer' => 'PLAN', 'date' => '2026-07-10', 'source' => 'remittance'],
        ], $out);
    }

    public function testOtherReasonsAndUnknownReferencesAreNotListed(): void
    {
        file_put_contents($this->dir . '/r.835', 'x');
        $csv = [['FileName' => 'r.835', 'CLM01' => '100-200']];
        $this->assertSame([], TimelyFilingCandidates::fromRemittances(
            $csv, [], $this->dir, self::parser(self::claim('100-200', '45'))
        ));
        $this->assertSame([], TimelyFilingCandidates::fromRemittances(
            $csv, [], $this->dir, self::parser(self::claim('999-999', '29'))
        ));
        $this->assertSame([], TimelyFilingCandidates::fromRemittances(
            [['FileName' => 'r.835', 'CLM01' => 'ABC-1']], [], $this->dir, self::parser(self::claim('ABC-1', '29'))
        ));
    }

    public function testOneEncounterIsListedOnceAcrossFiles(): void
    {
        file_put_contents($this->dir . '/a.835', 'x');
        file_put_contents($this->dir . '/b.835', 'y');
        $csv = [
            ['FileName' => 'a.835', 'CLM01' => '100-200'],
            ['FileName' => 'b.835', 'CLM01' => '100-200'],
        ];
        $out = TimelyFilingCandidates::fromRemittances($csv, [], $this->dir, self::parser(self::claim('100-200', '29')));
        $this->assertCount(1, $out);
    }

    public function testAMissingFileListsNothing(): void
    {
        $this->assertSame([], TimelyFilingCandidates::fromRemittances(
            [['FileName' => 'gone.835', 'CLM01' => '100-200']], [], $this->dir, self::parser()
        ));
    }
}