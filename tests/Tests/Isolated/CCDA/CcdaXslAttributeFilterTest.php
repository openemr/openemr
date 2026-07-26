<?php

/**
 * Isolated tests for the CDA R2 narrative-block attribute allowlist.
 *
 * Exercises the shared `safe-copy-narrative-attrs` template in
 * `interface/modules/zend_modules/public/xsl/_narrative-block-attrs.xsl`
 * — the template that `ccd.xsl` and `qrda.xsl` call in place of a bare
 * `<xsl:copy-of select="@*"/>` on table narrative elements, and that
 * `cda.xsl`'s `output-attrs` template gates against inline.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CcdaXslAttributeFilterTest extends TestCase
{
    private const XSL_DIR = __DIR__ . '/../../../../interface/modules/zend_modules/public/xsl';

    /**
     * Wrap the shared allowlist template so we can drive it directly from
     * a test-only XSL. Uses an absolute `file://` URI for the import so
     * the resolver doesn't need documentURI to work.
     */
    private static function copyAttrsViaSharedTemplate(string $attrsFragment): string
    {
        $sharedPath = realpath(self::XSL_DIR . '/_narrative-block-attrs.xsl');
        self::assertNotFalse($sharedPath, 'shared XSL file must resolve');
        $importHref = 'file://' . $sharedPath;

        $wrapperXsl = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="$importHref"/>
  <xsl:output method="xml" indent="no" omit-xml-declaration="yes"/>
  <xsl:template match="/probe">
    <result>
      <xsl:call-template name="safe-copy-narrative-attrs"/>
    </result>
  </xsl:template>
</xsl:stylesheet>
XSL;

        $xsl = new DOMDocument();
        $xsl->loadXML($wrapperXsl);

        $xml = new DOMDocument();
        $xml->loadXML('<probe ' . $attrsFragment . ' />');

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        return (string) $proc->transformToXml($xml);
    }

    /**
     * @param list<string> $expectedNames  attribute names that must appear in output
     * @param list<string> $droppedNames   attribute names that must NOT appear in output
     */
    #[DataProvider('attributeCasesProvider')]
    public function testAllowlist(string $inputAttrs, array $expectedNames, array $droppedNames): void
    {
        $result = self::copyAttrsViaSharedTemplate($inputAttrs);
        foreach ($expectedNames as $name) {
            $this->assertMatchesRegularExpression(
                '/\b' . preg_quote($name, '/') . '=/',
                $result,
                "Expected `$name=` to be preserved in: $result"
            );
        }
        foreach ($droppedNames as $name) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b' . preg_quote($name, '/') . '=/',
                $result,
                "Expected `$name=` to be dropped from: $result"
            );
        }
    }

    /**
     * Belt-and-suspenders coverage for the attribute-match templates in
     * ccd.xsl/qrda.xsl that match `n1:table/@*|n1:thead/@*|...`. Not
     * currently reached by production callers (parent element templates
     * copy attributes via safe-copy-narrative-attrs), but gated so any
     * future `<xsl:apply-templates select="@*"/>` respects the allowlist.
     *
     * Test drives that path directly against ccd.xsl and asserts drops.
     */
    public function testCcdAttributeMatchTemplateAppliesAllowlist(): void
    {
        $ccdPath = realpath(self::XSL_DIR . '/ccd.xsl');
        self::assertNotFalse($ccdPath, 'ccd.xsl must resolve');

        // Wrapper imports ccd.xsl and drives the attribute-match template by
        // explicitly applying to @* on our synthetic root. Overrides ccd.xsl's
        // match="/" template (import lowers priority) so we don't get pulled
        // into CCD's full rendering pipeline for our tiny synthetic input.
        $wrapperXsl = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:n1="urn:hl7-org:v3">
  <xsl:import href="file://$ccdPath"/>
  <xsl:output method="xml" indent="no" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <result>
      <xsl:apply-templates select="/n1:td/@*"/>
    </result>
  </xsl:template>
</xsl:stylesheet>
XSL;

        $xsl = new DOMDocument();
        $xsl->loadXML($wrapperXsl);
        $xml = new DOMDocument();
        $xml->loadXML('<n1:td xmlns:n1="urn:hl7-org:v3" ID="keep" colspan="2" onmouseover="alert(1)" style="x"/>');

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        $out = (string) $proc->transformToXml($xml);

        $this->assertMatchesRegularExpression('/\bID=/', $out, 'ID must survive attribute-match template');
        $this->assertMatchesRegularExpression('/\bcolspan=/', $out, 'colspan must survive attribute-match template');
        $this->assertDoesNotMatchRegularExpression('/\bonmouseover=/', $out, 'onmouseover must be dropped by attribute-match template');
        $this->assertDoesNotMatchRegularExpression('/\bstyle=/', $out, 'style must be dropped by attribute-match template');
    }

    /**
     * @return array<string, array{string, list<string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function attributeCasesProvider(): array
    {
        return [
            // --- Benign spec-legal attributes preserved ---
            'ID preserved (spec case)' => [
                'ID="section1"',
                ['ID'],
                [],
            ],
            'width + border on table' => [
                'width="200" border="1"',
                ['width', 'border'],
                [],
            ],
            'colspan + rowspan on cell' => [
                'colspan="2" rowspan="3"',
                ['colspan', 'rowspan'],
                [],
            ],
            'styleCode preserved' => [
                'styleCode="Bold"',
                ['styleCode'],
                [],
            ],
            'all-narrative sample (title, href)' => [
                'title="tip" href="https://example.org"',
                ['title', 'href'],
                [],
            ],

            // --- Case sensitivity: only spec-cased names allowed ---
            'lowercase id dropped (spec uses ID)' => [
                'id="lowercase"',
                [],
                ['id'],
            ],
            'uppercase ID kept + lowercase id dropped side-by-side' => [
                'ID="keep" id="drop"',
                ['ID'],
                ['id'],
            ],

            // --- Event handlers dropped ---
            'onmouseover dropped' => [
                'onmouseover="alert(1)"',
                [],
                ['onmouseover'],
            ],
            'onclick dropped' => [
                'onclick="alert(1)"',
                [],
                ['onclick'],
            ],
            'onfocus dropped' => [
                'onfocus="alert(1)"',
                [],
                ['onfocus'],
            ],
            'onerror dropped' => [
                'onerror="alert(1)"',
                [],
                ['onerror'],
            ],

            // --- Other non-narrative dangerous attributes dropped ---
            'style dropped' => [
                'style="background:red"',
                [],
                ['style'],
            ],
            'srcdoc dropped' => [
                'srcdoc="&lt;script&gt;alert(1)&lt;/script&gt;"',
                [],
                ['srcdoc'],
            ],
            'formaction dropped' => [
                'formaction="https://evil.example"',
                [],
                ['formaction'],
            ],

            // --- Mixed: legitimate + malicious in same element ---
            'legitimate colspan preserved + onmouseover dropped' => [
                'colspan="2" onmouseover="alert(1)"',
                ['colspan'],
                ['onmouseover'],
            ],
            'legitimate ID preserved + onclick dropped' => [
                'ID="keeper" onclick="alert(1)"',
                ['ID'],
                ['onclick'],
            ],

            // --- Substring/prefix matches that must NOT bypass the allowlist ---
            'idFoo (prefix collision with ID) dropped' => [
                'idFoo="attempt"',
                [],
                ['idFoo'],
            ],
            'nameEquals (variant of name) dropped' => [
                'nameEquals="attempt"',
                [],
                ['nameEquals'],
            ],
            'widthy (prefix collision with width) dropped' => [
                'widthy="attempt"',
                [],
                ['widthy'],
            ],
        ];
    }
}
