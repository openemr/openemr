<?php

/**
 * Isolated tests for the UDS payer classifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Payer;

use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\Payer\UdsPayerClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsPayerClassifierTest extends TestCase
{
    #[DataProvider('codeProvider')]
    public function testClassifyByInsuranceTypeCode(?int $code, ?UdsPayerCategory $expected): void
    {
        self::assertSame($expected, (new UdsPayerClassifier())->classifyByInsuranceTypeCode($code));
    }

    /**
     * @return array<string, array{?int, ?UdsPayerCategory}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function codeProvider(): array
    {
        return [
            'Medicaid' => [3, UdsPayerCategory::Medicaid],
            'Medicare Part B' => [2, UdsPayerCategory::Medicare],
            'Medicare HMO risk' => [15, UdsPayerCategory::Medicare],
            'Self pay' => [8, UdsPayerCategory::None],
            'TRICARE (other public)' => [5, UdsPayerCategory::OtherPublic],
            'VA plan (other public)' => [24, UdsPayerCategory::OtherPublic],
            'Title V (other public)' => [23, UdsPayerCategory::OtherPublic],
            'Blue Cross (private)' => [6, UdsPayerCategory::Private],
            'Commercial (private)' => [17, UdsPayerCategory::Private],
            'null code' => [null, null],
            'unknown code' => [999, null],
        ];
    }
}
