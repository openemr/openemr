<?php

/**
 * Isolated tests for EraAdjustmentMemo: the memo ERA posting stores for a
 * patient-responsibility adjustment keeps the payer's reason code, and the
 * pattern Claim uses to read it back matches memos with and without it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\EraAdjustmentMemo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EraAdjustmentMemoTest extends TestCase
{
    /**
     * @return array<string, array{bool, string, float, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function memoProvider(): array
    {
        return [
            'deductible' => [true, '1', 150.0, 'Ins1 dedbl: 150.00'],
            'coinsurance' => [true, '2', 12.5, 'Ins1 coins: 12.50'],
            'copay' => [true, '3', 25.0, 'Ins1 copay: 25.00'],
            'benefit maximum keeps its code' => [true, '119', 160.0, 'Ins1 ptresp 119: 160.00'],
            'non-covered keeps its code' => [true, '96', 44.0, 'Ins1 ptresp 96: 44.00'],
            'no code' => [true, '', 160.0, 'Ins1 ptresp: 160.00'],
            'secondary note' => [false, '119', 160.0, 'Ins1 note 119: 160.00'],
            'secondary note without a code' => [false, '', 160.0, 'Ins1 note: 160.00'],
        ];
    }

    #[Test]
    #[DataProvider('memoProvider')]
    public function memoNamesOrCodesTheReason(bool $primary, string $code, float $amount, string $memo): void
    {
        $this->assertSame($memo, EraAdjustmentMemo::forAdjustment('Ins1', $primary, $code, $amount));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patientResponsibilityCodeProvider(): array
    {
        return [
            'with a code' => ['119'],
            'without a code' => [''],
        ];
    }

    #[Test]
    #[DataProvider('patientResponsibilityCodeProvider')]
    public function claimReadsEveryPtrespMemoThisBuilds(string $code): void
    {
        $memo = EraAdjustmentMemo::forAdjustment('Ins1', true, $code, 160.0);
        $this->assertSame(1, preg_match(EraAdjustmentMemo::PATIENT_RESPONSIBILITY_PATTERN, $memo, $m), $memo);
        $this->assertSame('160.00', $m[1]);
    }

    #[Test]
    public function memosFitOnStatements(): void
    {
        $this->assertLessThanOrEqual(25, strlen(EraAdjustmentMemo::forAdjustment('Ins1', true, '242', 1234.56)));
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patternProvider(): array
    {
        return [
            'with the code' => ['Ins1 ptresp 119: 160.00', '160.00'],
            'posted before the code was kept' => ['Ins1 ptresp: 160.00', '160.00'],
            'deductible is not ptresp' => ['Ins1 dedbl: 150.00', null],
            'secondary note is not ptresp' => ['Ins2 note 119: 160.00', null],
            'contractual adjustment is not ptresp' => ['Adjust code 45', null],
        ];
    }

    #[Test]
    #[DataProvider('patternProvider')]
    public function claimReadsPatientResponsibilityWithOrWithoutTheCode(string $memo, ?string $amount): void
    {
        $matched = preg_match(EraAdjustmentMemo::PATIENT_RESPONSIBILITY_PATTERN, $memo, $m);
        $this->assertSame($amount, $matched === 1 ? $m[1] : null);
    }
}
