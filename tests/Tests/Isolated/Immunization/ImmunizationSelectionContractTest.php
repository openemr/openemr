<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Immunization;

use Immunization\Model\PatientSelectionMarker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/PatientSelectionMarker.php';

class ImmunizationSelectionContractTest extends TestCase
{
    private string $template;
    private string $sendToScript;

    protected function setUp(): void
    {
        $projectRoot = realpath(__DIR__ . '/../../../..');
        self::assertIsString($projectRoot);

        $template = file_get_contents(
            $projectRoot . '/interface/modules/zend_modules/module/Immunization/view/immunization/immunization/index.phtml'
        );
        self::assertIsString($template);
        $this->template = $template;

        $sendToScript = file_get_contents(
            $projectRoot . '/interface/modules/zend_modules/public/js/application/sendTo.js'
        );
        self::assertIsString($sendToScript);
        $this->sendToScript = $sendToScript;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<bool> $expectedMarkers
     */
    #[DataProvider('patientRowsProvider')]
    public function testOnlyFirstRowForEachPatientIsSelectable(array $rows, array $expectedMarkers): void
    {
        $markedRows = PatientSelectionMarker::markFirstRowForEachPatient($rows);

        self::assertSame(
            array_column($rows, 'immunizationid'),
            array_column($markedRows, 'immunizationid'),
            'All immunization rows must be preserved in their original order'
        );
        self::assertSame($expectedMarkers, array_column($markedRows, 'isPatientSelectable'));
        self::assertCount(count(array_unique(array_map(
            static fn(array $row): string => is_scalar($row['patientid']) ? (string) $row['patientid'] : '',
            $rows
        ))), array_filter(array_column($markedRows, 'isPatientSelectable')));
    }

    /**
     * @return array<string, array{list<array<string, mixed>>, list<bool>}>
     */
    public static function patientRowsProvider(): array
    {
        return [
            'empty rows' => [[], []],
            'one patient' => [[
                ['patientid' => 1, 'immunizationid' => 10],
            ], [true]],
            'repeated rows for one patient' => [[
                ['patientid' => 1, 'immunizationid' => 10],
                ['patientid' => '1', 'immunizationid' => 11],
                ['patientid' => 1, 'immunizationid' => 12],
            ], [true, false, false]],
            'multiple distinct patients' => [[
                ['patientid' => 'patient-2', 'immunizationid' => 20],
                ['patientid' => '01', 'immunizationid' => 21],
                ['patientid' => 'patient-2', 'immunizationid' => 22],
                ['patientid' => 1, 'immunizationid' => 23],
            ], [true, true, false, true]],
        ];
    }

    public function testTemplateIntegratesUniqueSelectionWithTranslatedAccessibleName(): void
    {
        self::assertStringContainsString("if (\$value['isPatientSelectable'])", $this->template);
        self::assertMatchesRegularExpression('/class="check_pid check_pid_<\?php.+?name="ccda_pid\[\]"/s', $this->template);
        self::assertStringContainsString("escapeHtml(\$value['patientid'])", $this->template);
        self::assertStringContainsString("aria-label=\"<?php echo \$this->escapeHtmlAttr(", $this->template);
        self::assertStringContainsString("z_xlt('Select patient')", $this->template);
        self::assertStringContainsString("\$value['patientname']", $this->template);
        self::assertStringContainsString("document.getElementsByName('ccda_pid[]')", $this->sendToScript);
    }

    public function testSelectionColumnUsesTranslatedLabelAndMatchingFooterSpan(): void
    {
        self::assertStringContainsString("z_xlt('Select')", $this->template);
        self::assertStringContainsString('<th colspan="7">', $this->template);
    }
}
