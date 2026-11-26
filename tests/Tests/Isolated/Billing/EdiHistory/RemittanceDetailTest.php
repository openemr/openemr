<?php

/**
 * Tests for the remittance file detail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\RemittanceDetail;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RemittanceDetailTest extends TestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'remit_' . getmypid() . '_' . mt_rand();
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

    private function put(string $name, string $content): void
    {
        file_put_contents($this->dir . DIRECTORY_SEPARATOR . $name, $content);
    }

    private static function parser(array $claim): callable
    {
        return static function (string $path, callable $onClaim) use ($claim): void {
            $onClaim($claim);
        };
    }

    public function testSameContentUnderTwoNamesHashesAlike(): void
    {
        $this->put('a.835', 'same');
        $this->put('b.835', 'same');
        $out = RemittanceDetail::forFiles($this->dir, ['a.835', 'b.835'], self::parser([]));
        $this->assertSame(hash('sha256', 'same'), $out['sha']['a.835']);
        $this->assertSame($out['sha']['a.835'], $out['sha']['b.835']);
    }

    public function testAdjustmentsAreKeyedByFileAndOurClaimId(): void
    {
        $this->put('a.835', 'x');
        $claim = [
            'our_claim_id' => '100-200',
            'svc' => [['adj' => [['group_code' => 'CO', 'reason_code' => '29', 'amount' => '257.00']], 'remark' => 'N130']],
        ];
        $out = RemittanceDetail::forFiles($this->dir, ['a.835'], self::parser($claim));
        $this->assertSame('adjustments CO-29 257.00; remarks N130', $out['adjustments']["a.835\0" . '100-200']);
    }

    public function testAClaimWithNoReferenceOrNoAdjustmentsAddsNothing(): void
    {
        $this->put('a.835', 'x');
        $out = RemittanceDetail::forFiles($this->dir, ['a.835'], self::parser(['our_claim_id' => '']));
        $this->assertSame([], $out['adjustments']);
        $out = RemittanceDetail::forFiles($this->dir, ['a.835'], self::parser(['our_claim_id' => '100-200']));
        $this->assertSame([], $out['adjustments']);
    }

    public function testAMissingFileIsSkipped(): void
    {
        $out = RemittanceDetail::forFiles($this->dir, ['gone.835', ''], self::parser([]));
        $this->assertSame([], $out['sha']);
    }

    public function testAParserThatFailsStillLeavesTheHash(): void
    {
        $this->put('a.835', 'x');
        $parser = static function (string $path, callable $onClaim): void {
            throw new RuntimeException('cannot parse');
        };
        $out = RemittanceDetail::forFiles($this->dir, ['a.835'], $parser);
        $this->assertSame(hash('sha256', 'x'), $out['sha']['a.835']);
        $this->assertSame([], $out['adjustments']);
    }

    public function testANameRepeatedIsReadOnlyOnce(): void
    {
        $this->put('a.835', 'x');
        $calls = 0;
        $parser = static function (string $path, callable $onClaim) use (&$calls): void {
            $calls++;
        };
        RemittanceDetail::forFiles($this->dir, ['a.835', 'a.835'], $parser);
        $this->assertSame(1, $calls);
    }
}