<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Immunization;

use PHPUnit\Framework\TestCase;

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

    public function testImmunizationRowsExposeEscapedPatientSelectionToSendTo(): void
    {
        $checkboxContract = <<<'HTML'
<input class="check_pid check_pid_<?php echo $this->escapeHtml($value['patientid']); ?>" type="checkbox" name="ccda_pid[]" value="<?php echo $this->escapeHtml($value['patientid']); ?>">
HTML;

        self::assertStringContainsString($checkboxContract, $this->template);
        self::assertStringContainsString(
            "document.getElementsByName('ccda_pid[]')",
            $this->sendToScript
        );
    }

    public function testSelectionColumnUsesTranslatedLabelAndMatchingFooterSpan(): void
    {
        self::assertStringContainsString("z_xlt('Select')", $this->template);
        self::assertStringContainsString('<th colspan="7">', $this->template);
    }
}
