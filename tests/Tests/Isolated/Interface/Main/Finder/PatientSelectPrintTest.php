<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Interface\Main\Finder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientSelectPrintTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int|null, bool, bool}>
     */
    public static function printModeProvider(): iterable
    {
        yield 'ordinary CDR drilldown' => ['cdr_report', 0, true, false];
        yield 'explicit entire-list print' => ['cdr_report', 1, true, true];
        yield 'non-CDR patient selector without print variable' => ['', null, false, false];
        yield 'non-CDR patient selector with disabled print' => ['', 0, true, false];
    }

    #[DataProvider('printModeProvider')]
    public function testAutoPrintOnlyRunsForEnabledCdrPrintMode(
        string $fromPage,
        ?int $printPatients,
        bool $definePrintPatients,
        bool $expectsAutoPrint
    ): void {
        $block = $this->extractAutoPrintTemplateBlock();
        $errors = [];
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message];

            return true;
        });

        try {
            $output = $this->renderTemplateBlock($block, $fromPage, $printPatients, $definePrintPatients);
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $errors, 'Rendering the auto-print block emitted a PHP warning or notice.');
        self::assertSame($expectsAutoPrint, str_contains($output, 'printLogPrint(window)'));
    }

    private function extractAutoPrintTemplateBlock(): string
    {
        $path = realpath(__DIR__ . '/../../../../../../interface/main/finder/patient_select.php');
        self::assertIsString($path);

        $source = file_get_contents($path);
        self::assertIsString($source);

        $printCallPosition = strpos($source, 'printLogPrint(window);');
        self::assertIsInt($printCallPosition);

        $blockStart = strrpos(substr($source, 0, $printCallPosition), '<?php if ');
        self::assertIsInt($blockStart);

        $closingTag = '<?php } ?>';
        $blockEnd = strpos($source, $closingTag, $printCallPosition);
        self::assertIsInt($blockEnd);

        $block = substr($source, $blockStart, $blockEnd + strlen($closingTag) - $blockStart);
        self::assertStringContainsString('printLogPrint(window);', $block);

        return $block;
    }

    private function renderTemplateBlock(
        string $block,
        string $fromPage,
        ?int $printPatients,
        bool $definePrintPatients
    ): string {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'openemr-patient-select-');
        self::assertIsString($temporaryPath);
        self::assertIsInt(file_put_contents($temporaryPath, $block));

        $render = static function () use ($temporaryPath, $fromPage, $printPatients, $definePrintPatients): string {
            $from_page = $fromPage;
            if ($definePrintPatients) {
                $print_patients = $printPatients;
            }

            ob_start();
            try {
                include $temporaryPath;

                $output = ob_get_contents();
                self::assertIsString($output);

                return $output;
            } finally {
                ob_end_clean();
            }
        };

        try {
            return $render();
        } finally {
            unlink($temporaryPath);
        }
    }
}
