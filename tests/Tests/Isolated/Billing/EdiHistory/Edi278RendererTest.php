<?php

/**
 * Isolated regression tests for the legacy EDI 278 HTML renderer.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/edi_278_renderer_runtime_stubs.php';
}

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory {
    use PHPUnit\Framework\TestCase;

    final class Edi278RendererTest extends TestCase
    {
        public static function setUpBeforeClass(): void
        {
            require_once __DIR__ . '/../../../../../library/edihistory/codes/edih_271_code_class.php';
            require_once __DIR__ . '/../../../../../library/edihistory/edih_278_html.php';
        }

        public function testCompositeServiceDoesNotSkipDuplicateReferenceTransaction(): void
        {
            $x12 = new Edi278RendererX12([
                [
                    'BHT*0007*13*DUPLICATE*20260101',
                    'HL*1**SS*0',
                    'SV1*HC:CODE:MOD1:MOD2:MOD3*10',
                ],
                [
                    'BHT*0007*13*DUPLICATE*20260102',
                    'HL*2**SS*0',
                    'MSG*SECOND-TRANSACTION-RENDERED',
                ],
            ]);

            $html = \edih_278_transaction_html($x12, 'DUPLICATE');

            self::assertIsString($html);
            self::assertStringContainsString('SECOND-TRANSACTION-RENDERED', $html);
        }

        public function testSv2UsesSegmentValuesForUnitAndLevelOfCareLookups(): void
        {
            $sv2 = array_fill(0, 21, '');
            $sv2[0] = 'SV2';
            $sv2[1] = '0300:CODE:MOD1:MOD2';
            $sv2[2] = '125';
            $sv2[3] = 'MJ';
            $sv2[20] = '7';

            $x12 = new Edi278RendererX12([
                [
                    'BHT*0007*13*SV2-LOOKUP*20260101',
                    'HL*1**SS*0',
                    implode('*', $sv2),
                ],
                [
                    'BHT*0007*13*SV2-LOOKUP*20260102',
                    'HL*2**SS*0',
                    'MSG*SECOND-SV2-TRANSACTION-RENDERED',
                ],
            ]);

            $html = \edih_278_transaction_html($x12, 'SV2-LOOKUP');

            self::assertIsString($html);
            self::assertStringContainsString('Minutes', $html);
            self::assertStringContainsString('Nursing Facility (NF)', $html);
            self::assertStringContainsString('SECOND-SV2-TRANSACTION-RENDERED', $html);
        }
    }

    final readonly class Edi278RendererX12
    {
        /**
         * @param list<list<string>> $transactions
         */
        public function __construct(private array $transactions)
        {
        }

        /**
         * @return list<list<string>>
         */
        public function edih_x12_transaction(string $reference): array
        {
            return $this->transactions;
        }

        /**
         * @return array{e: string, s: string, r: string}
         */
        public function edih_delimiters(): array
        {
            return ['e' => '*', 's' => ':', 'r' => '^'];
        }

        public function edih_filename(): string
        {
            return 'fixture.278';
        }
    }
}
