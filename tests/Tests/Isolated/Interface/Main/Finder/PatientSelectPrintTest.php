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
     * @return iterable<string, array{string, int|null, bool}>
     */
    public static function printModeProvider(): iterable
    {
        yield 'ordinary CDR drilldown' => ['cdr_report', 0, false];
        yield 'explicit entire-list print' => ['cdr_report', 1, true];
        yield 'non-CDR patient selector' => ['', null, false];
    }

    #[DataProvider('printModeProvider')]
    public function testAutoPrintOnlyRunsForEnabledCdrPrintMode(
        string $fromPage,
        ?int $printPatients,
        bool $expectsAutoPrint
    ): void {
        [$initialization, $cdrDispatch, $cdrAssignment, $templateBlock] = $this->extractProductionPrintFlow();
        $errors = [];
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message];

            return true;
        });

        try {
            $output = $this->renderProductionPrintFlow(
                $initialization,
                $cdrDispatch,
                $cdrAssignment,
                $templateBlock,
                $fromPage,
                $printPatients
            );
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $errors, 'Rendering the auto-print block emitted a PHP warning or notice.');
        self::assertSame($expectsAutoPrint, str_contains($output, 'printLogPrint(window)'));
    }

    /** @return array{string, string, string, string} */
    private function extractProductionPrintFlow(): array
    {
        $path = realpath(__DIR__ . '/../../../../../../interface/main/finder/patient_select.php');
        self::assertIsString($path);

        $source = file_get_contents($path);
        self::assertIsString($source);

        preg_match('/^\$print_patients = false;$/m', $source, $initializationMatches);
        self::assertCount(1, $initializationMatches);

        $cdrBranchPosition = strpos($source, '} elseif ($from_page == "cdr_report") {');
        self::assertIsInt($cdrBranchPosition);
        $cdrDispatch = 'if' . substr($source, $cdrBranchPosition + strlen('} elseif'), strlen(' ($from_page == "cdr_report") {'));
        preg_match(
            '/^    \$print_patients = \(\$_REQUEST\[\'print_patients\'\] \?\? 0\) == 1;$/m',
            substr($source, $cdrBranchPosition),
            $assignmentMatches
        );
        self::assertCount(1, $assignmentMatches);

        $printCallPosition = strpos($source, 'printLogPrint(window);', $cdrBranchPosition);
        self::assertIsInt($printCallPosition);

        $blockStart = strrpos(substr($source, 0, $printCallPosition), '<?php if ');
        self::assertIsInt($blockStart);

        $closingTag = '<?php } ?>';
        $blockEnd = strpos($source, $closingTag, $printCallPosition);
        self::assertIsInt($blockEnd);

        $block = substr($source, $blockStart, $blockEnd + strlen($closingTag) - $blockStart);
        self::assertStringContainsString('printLogPrint(window);', $block);

        return [$initializationMatches[0], $cdrDispatch, $assignmentMatches[0], $block];
    }

    private function renderProductionPrintFlow(
        string $initialization,
        string $cdrDispatch,
        string $cdrAssignment,
        string $templateBlock,
        string $fromPage,
        ?int $printPatients
    ): string {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'openemr-patient-select-');
        self::assertIsString($temporaryPath);
        self::assertIsInt(file_put_contents($temporaryPath, "<?php\n" . $initialization));
        self::assertIsInt(file_put_contents($temporaryPath, "\n" . $cdrDispatch, FILE_APPEND));
        self::assertIsInt(file_put_contents($temporaryPath, "\n" . $cdrAssignment . "\n}", FILE_APPEND));
        self::assertIsInt(file_put_contents($temporaryPath, "\n?>\n" . $templateBlock, FILE_APPEND));

        $render = static function () use ($temporaryPath, $fromPage, $printPatients): string {
            $from_page = $fromPage;
            $_REQUEST = [];
            if ($printPatients !== null) {
                $_REQUEST['print_patients'] = $printPatients;
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
