<?php

/**
 * PHPFHIRHelperIsolatedTest — tests for PHPFHIRHelper::recursiveXMLImport().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\FHIR\R4;

use OpenEMR\Common\Utils\XmlParseException;
use OpenEMR\FHIR\R4\PHPFHIRHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class PHPFHIRHelperIsolatedTest extends TestCase
{
    #[Test]
    public function recursiveXMLImportMergesChildElement(): void
    {
        $parent = new SimpleXMLElement('<root/>');
        $childXml = '<child attr="value">text</child>';

        PHPFHIRHelper::recursiveXMLImport($parent, $childXml);

        $this->assertCount(1, $parent->children());
        $this->assertSame('child', $parent->children()[0]->getName());
        $this->assertSame('value', (string) $parent->children()[0]['attr']);
    }

    #[Test]
    public function recursiveXMLImportMergesMultipleElements(): void
    {
        $parent = new SimpleXMLElement('<root/>');

        PHPFHIRHelper::recursiveXMLImport($parent, '<first>one</first>');
        PHPFHIRHelper::recursiveXMLImport($parent, '<second>two</second>');

        $output = $parent->asXML();
        $this->assertIsString($output);
        $this->assertStringContainsString('<first', $output);
        $this->assertStringContainsString('<second', $output);
    }

    #[Test]
    public function recursiveXMLImportThrowsOnInvalidXml(): void
    {
        $parent = new SimpleXMLElement('<root/>');

        $this->expectException(XmlParseException::class);
        PHPFHIRHelper::recursiveXMLImport($parent, '<not-valid');
    }
}
