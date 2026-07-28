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
     * ccd.xsl and qrda.xsl that match `n1:table/@*|n1:thead/@*|...`.
     * Not currently reached by production callers (parent element templates
     * copy attributes via safe-copy-narrative-attrs), but gated so any
     * future `<xsl:apply-templates select="@*"/>` respects the allowlist.
     *
     * Runs against both stylesheets since they have parallel templates —
     * a future refactor touching one but not the other is exactly the
     * kind of drift this test is meant to catch.
     *
     * @param non-empty-string $xslFile
     */
    #[DataProvider('stylesheetsWithAttributeMatchTemplateProvider')]
    public function testAttributeMatchTemplateAppliesAllowlist(string $xslFile): void
    {
        $xslPath = realpath(self::XSL_DIR . '/' . $xslFile);
        self::assertNotFalse($xslPath, $xslFile . ' must resolve');

        // Wrapper imports the target stylesheet and drives the attribute-match
        // template by explicitly applying to @* on our synthetic root. The
        // wrapper's match="/" template overrides the imported match="/" (import
        // lowers priority) so we don't get pulled into the full rendering
        // pipeline for our tiny synthetic input.
        $wrapperXsl = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:n1="urn:hl7-org:v3">
  <xsl:import href="file://$xslPath"/>
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

        $this->assertMatchesRegularExpression('/\bID=/', $out, "$xslFile: ID must survive attribute-match template");
        $this->assertMatchesRegularExpression('/\bcolspan=/', $out, "$xslFile: colspan must survive attribute-match template");
        $this->assertDoesNotMatchRegularExpression('/\bonmouseover=/', $out, "$xslFile: onmouseover must be dropped by attribute-match template");
        $this->assertDoesNotMatchRegularExpression('/\bstyle=/', $out, "$xslFile: style must be dropped by attribute-match template");
    }

    /**
     * @return array<string, array{non-empty-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stylesheetsWithAttributeMatchTemplateProvider(): array
    {
        return [
            'ccd.xsl' => ['ccd.xsl'],
            'qrda.xsl' => ['qrda.xsl'],
        ];
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

            // --- href scheme filtering ---
            'href https preserved' => [
                'href="https://example.org/x"',
                ['href'],
                [],
            ],
            'href http preserved' => [
                'href="http://example.org/x"',
                ['href'],
                [],
            ],
            'href mailto preserved' => [
                'href="mailto:a@b.example"',
                ['href'],
                [],
            ],
            'href tel preserved' => [
                'href="tel:+15555550100"',
                ['href'],
                [],
            ],
            'href fragment preserved' => [
                'href="#section"',
                ['href'],
                [],
            ],
            'href javascript: dropped' => [
                'href="javascript:alert(1)"',
                [],
                ['href'],
            ],
            'href data:text/html dropped' => [
                'href="data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;"',
                [],
                ['href'],
            ],
            'href vbscript dropped' => [
                'href="vbscript:alert(1)"',
                [],
                ['href'],
            ],
            'href file:// dropped' => [
                'href="file:///etc/passwd"',
                [],
                ['href'],
            ],
            'href empty dropped' => [
                'href=""',
                [],
                ['href'],
            ],
            'href relative path dropped' => [
                'href="/relative/path"',
                [],
                ['href'],
            ],
            'href mixed benign + malicious side-by-side' => [
                'href="https://good.example" title="tip"',
                ['href', 'title'],
                [],
            ],
        ];
    }

    /**
     * Drive cda.xsl's inline `output-attrs` template with a synthetic <n1:td>
     * element and assert that URL-scheme filtering on `href` is applied.
     * Parallel to safe-copy-narrative-attrs coverage above; cda.xsl uses
     * inline logic rather than calling the shared template, so it needs
     * its own regression coverage.
     *
     * @param non-empty-string $inputAttrs
     * @param list<string> $expectedNames
     * @param list<string> $droppedNames
     */
    #[DataProvider('cdaOutputAttrsUrlCasesProvider')]
    public function testCdaOutputAttrsUrlScheme(string $inputAttrs, array $expectedNames, array $droppedNames): void
    {
        $xslPath = realpath(self::XSL_DIR . '/cda.xsl');
        self::assertNotFalse($xslPath, 'cda.xsl must resolve');

        // Wrapper imports cda.xsl and drives output-attrs on a synthetic
        // <n1:td>. `match="/"` overrides the imported match="/" (import
        // lowers priority) so our root template runs instead of cda.xsl's
        // ClinicalDocument-only dispatcher.
        $wrapperXsl = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:n1="urn:hl7-org:v3">
  <xsl:import href="file://$xslPath"/>
  <xsl:output method="xml" indent="no" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <result>
      <xsl:for-each select="n1:td">
        <xsl:call-template name="output-attrs"/>
      </xsl:for-each>
    </result>
  </xsl:template>
</xsl:stylesheet>
XSL;

        $xsl = new DOMDocument();
        $xsl->loadXML($wrapperXsl);

        $xml = new DOMDocument();
        $xml->loadXML('<n1:td xmlns:n1="urn:hl7-org:v3" ' . $inputAttrs . '/>');

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        $result = (string) $proc->transformToXml($xml);

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
     * @return array<string, array{non-empty-string, list<string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function cdaOutputAttrsUrlCasesProvider(): array
    {
        return [
            'href https preserved' => [
                'href="https://example.org/x"',
                ['href'],
                [],
            ],
            'href http preserved' => [
                'href="http://example.org/x"',
                ['href'],
                [],
            ],
            'href mailto preserved' => [
                'href="mailto:a@b.example"',
                ['href'],
                [],
            ],
            'href tel preserved' => [
                'href="tel:+15555550100"',
                ['href'],
                [],
            ],
            'href fragment preserved' => [
                'href="#section"',
                ['href'],
                [],
            ],
            'href data:text/html dropped' => [
                'href="data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;"',
                [],
                ['href'],
            ],
            'href vbscript dropped' => [
                'href="vbscript:alert(1)"',
                [],
                ['href'],
            ],
            'href file:// dropped' => [
                'href="file:///etc/passwd"',
                [],
                ['href'],
            ],
            'href empty dropped' => [
                'href=""',
                [],
                ['href'],
            ],
            'href relative path dropped' => [
                'href="/relative/path"',
                [],
                ['href'],
            ],
            'legitimate href preserved alongside title' => [
                'href="https://good.example" title="tip"',
                ['href', 'title'],
                [],
            ],
        ];
    }
}
