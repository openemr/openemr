<?php

/**
 * OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    OpenEMR Project
 * @copyright Copyright (c) 2026 OpenEMR Project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PaymentProcessing;

use OpenEMR\PaymentProcessing\PortalPaymentInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PortalPaymentInputTest extends TestCase
{
    #[Test]
    #[DataProvider('paymentTypeProvider')]
    public function itNormalizesPaymentTypes(string $input, string $expected): void
    {
        self::assertSame($expected, PortalPaymentInput::normalizePaymentType($input));
    }

    /**
     * @return array<string, array{string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function paymentTypeProvider(): array
    {
        return [
            'pre-payment' => ['pre_payment', 'pre_payment'],
            'copay' => ['copay', 'copay'],
            'invoice balance' => ['invoice_balance', 'invoice_balance'],
            'cash' => ['cash', 'cash'],
            'unsupported type' => ['credit', ''],
            'empty type' => ['', ''],
        ];
    }

    #[Test]
    public function itNormalizesPositivePayments(): void
    {
        self::assertSame(
            [42 => 15.5, 'encounter-7' => 2.0],
            PortalPaymentInput::normalizePositivePayments([
                42 => '15.50',
                'zero' => 0,
                'negative' => -3,
                'invalid' => 'ten',
                'encounter-7' => 2,
            ])
        );
    }

    #[Test]
    public function itNormalizesBillingCodeRows(): void
    {
        self::assertSame(
            ['CPT4', '99213', '25'],
            PortalPaymentInput::normalizeBillingCodeRow([
                'code_type' => 'CPT4',
                'code' => '99213',
                'modifier' => '25',
            ])
        );
        self::assertSame(
            ['', '', ''],
            PortalPaymentInput::normalizeBillingCodeRow([
                'code_type' => null,
                'code' => 99213,
                'modifier' => [],
            ])
        );
    }

    #[Test]
    #[DataProvider('receiptTimeProvider')]
    public function itNormalizesReceiptTimes(string $input, string $expected): void
    {
        self::assertSame($expected, PortalPaymentInput::normalizeReceiptTime($input));
    }

    /**
     * @return array<string, array{string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function receiptTimeProvider(): array
    {
        return [
            'valid leap day' => ['2024-02-29 23:59:59', '2024-02-29 23:59:59'],
            'invalid leap day' => ['2023-02-29 12:00:00', ''],
            'impossible date' => ['2026-04-31 12:00:00', ''],
            'invalid hour' => ['2026-04-14 24:00:00', ''],
            'invalid minute' => ['2026-04-14 12:60:00', ''],
            'wrong shape' => ['2026-4-14 12:00:00', ''],
            'empty timestamp' => ['', ''],
        ];
    }

    #[Test]
    public function itNormalizesReceiptRequests(): void
    {
        self::assertSame(
            [true, '2024-02-29 23:59:59'],
            PortalPaymentInput::normalizeReceiptRequest(true, '2024-02-29 23:59:59')
        );
        self::assertSame([false, ''], PortalPaymentInput::normalizeReceiptRequest(true, '2023-02-29 12:00:00'));
        self::assertSame([false, ''], PortalPaymentInput::normalizeReceiptRequest(false, '2024-02-29 23:59:59'));
    }
}
