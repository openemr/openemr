<?php

/**
 * XmlUtilsTest - Unit tests for OpenEMR\Common\Utils\XmlUtils.
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Utils;

use OpenEMR\Common\Utils\XmlUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

class XmlUtilsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // loadString — success paths
    // -------------------------------------------------------------------------

    #[Test]
    public function testLoadStringReturnsSimpleXmlElement(): void
    {
        $xml = '<root><child>hello</child></root>';
        $result = XmlUtils::loadString($xml);

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
    }

    #[Test]
    public function testLoadStringPreservesContent(): void
    {
        $xml = '<patient><name>John Doe</name><age>42</age></patient>';
        $result = XmlUtils::loadString($xml);

        $this->assertSame('John Doe', (string) $result->name);
        $this->assertSame('42', (string) $result->age);
    }

    #[Test]
    public function testLoadStringHandlesNamespaces(): void
    {
        $xml = '<root xmlns:ns="http://example.com"><ns:child>value</ns:child></root>';
        $result = XmlUtils::loadString($xml);

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
    }

    #[Test]
    public function testLoadStringWithLibxmlNocdata(): void
    {
        $xml = '<root><data><![CDATA[some & data]]></data></root>';
        // Without LIBXML_NOCDATA the CDATA section wraps the value; with it the text is exposed.
        $result = XmlUtils::loadString($xml, LIBXML_NOCDATA);

        $this->assertSame('some & data', (string) $result->data);
    }

    // -------------------------------------------------------------------------
    // loadString — failure paths
    // -------------------------------------------------------------------------

    #[Test]
    public function testLoadStringThrowsOnMalformedXml(): void
    {
        $this->expectException(RuntimeException::class);
        XmlUtils::loadString('<not-valid-xml');
    }

    #[Test]
    public function testLoadStringThrowsOnEmptyString(): void
    {
        $this->expectException(RuntimeException::class);
        XmlUtils::loadString('');
    }

    #[Test]
    public function testLoadStringThrowsOnNonXmlString(): void
    {
        $this->expectException(RuntimeException::class);
        XmlUtils::loadString('this is not xml at all');
    }

    #[Test]
    public function testLoadStringThrowsOnUnclosedTag(): void
    {
        $this->expectException(RuntimeException::class);
        XmlUtils::loadString('<root><child>text</root>');
    }

    // -------------------------------------------------------------------------
    // tryLoadString — success paths
    // -------------------------------------------------------------------------

    #[Test]
    public function testTryLoadStringReturnsElementOnValidXml(): void
    {
        $xml = '<root><item>1</item></root>';
        $result = XmlUtils::tryLoadString($xml);

        $this->assertNotNull($result);
        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertSame('1', (string) $result->item);
    }

    // -------------------------------------------------------------------------
    // tryLoadString — failure paths (returns null, no throw)
    // -------------------------------------------------------------------------

    #[Test]
    public function testTryLoadStringReturnsNullOnMalformedXml(): void
    {
        $result = XmlUtils::tryLoadString('<broken');

        $this->assertNull($result);
    }

    #[Test]
    public function testTryLoadStringReturnsNullOnEmptyString(): void
    {
        $result = XmlUtils::tryLoadString('');

        $this->assertNull($result);
    }

    #[Test]
    public function testTryLoadStringReturnsNullOnNonXml(): void
    {
        $result = XmlUtils::tryLoadString('{"json": "not xml"}');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Security: LIBXML_NONET prevents external entity resolution
    // -------------------------------------------------------------------------

    #[Test]
    public function testLoadStringDoesNotResolveExternalEntities(): void
    {
        // An XXE payload that attempts to read /etc/passwd via an external entity.
        // With LIBXML_NONET the external DTD fetch is blocked, so parsing fails or
        // the entity is left unresolved — either way no file disclosure occurs.
        $xxeAttempt = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE root [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<root>&xxe;</root>
XML;

        // loadString should throw (external entity resolution blocked/failed)
        // OR return an element where the entity body is not the file contents.
        try {
            $result = XmlUtils::loadString($xxeAttempt);
            // If we get here, parsing succeeded but the entity must not have resolved.
            $body = (string) $result;
            $this->assertStringNotContainsString('root:', $body, 'File contents must not appear in output');
        } catch (RuntimeException) {
            // Expected: parsing was blocked — this is the secure outcome.
            $this->assertTrue(true);
        }
    }
}
