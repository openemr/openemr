<?php

/**
 * Isolated tests for CdaTextParser XML parsing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaTextParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(CdaTextParser::class)]
#[Group('isolated')]
class CdaTextParserTest extends TestCase
{
    private const SECTION_CODE = '18776-5';

    private const XML_WITH_NAMESPACE = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
  <component>
    <structuredBody>
      <component>
        <section>
          <code code="18776-5" codeSystem="2.16.840.1.113883.6.1"/>
          <list>
            <item ID="goal1">
              <caption>Goal A</caption>
              Some text content
            </item>
            <item ID="goal2">
              <caption>Goal B</caption>
              Another item
            </item>
          </list>
        </section>
      </component>
    </structuredBody>
  </component>
</ClinicalDocument>
XML;

    private const XML_NESTED_LISTS = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
  <component>
    <structuredBody>
      <component>
        <section>
          <code code="18776-5" codeSystem="2.16.840.1.113883.6.1"/>
          <list>
            <item ID="parent1">
              <caption>Parent</caption>
              Top level text
              <list>
                <item ID="child1">
                  <caption>Child</caption>
                  Nested text
                </item>
              </list>
            </item>
          </list>
        </section>
      </component>
    </structuredBody>
  </component>
</ClinicalDocument>
XML;

    public function testParseSectionByCodeExtractsItems(): void
    {
        $parser = new CdaTextParser(self::XML_WITH_NAMESPACE);
        /** @var list<array{id: string, caption: string, content: string}> $notes */
        $notes = $parser->parseSectionByCode(self::SECTION_CODE);

        $this->assertCount(2, $notes);
        $this->assertSame('goal1', $notes[0]['id']);
        $this->assertSame('Goal A', $notes[0]['caption']);
        $this->assertStringContainsString('Some text content', $notes[0]['content']);
        $this->assertSame('goal2', $notes[1]['id']);
        $this->assertSame('Goal B', $notes[1]['caption']);
    }

    public function testParseSectionByCodeWithNestedLists(): void
    {
        $parser = new CdaTextParser(self::XML_NESTED_LISTS);
        /** @var list<array{id: string, caption: string, content: string}> $notes */
        $notes = $parser->parseSectionByCode(self::SECTION_CODE);

        // getElementsByTagName finds all descendant <item> elements, so both
        // the parent and nested child appear as top-level parsed items.
        $this->assertCount(2, $notes);
        $this->assertSame('parent1', $notes[0]['id']);
        $this->assertStringContainsString('Top level text', $notes[0]['content']);
        $this->assertSame('child1', $notes[1]['id']);
        $this->assertStringContainsString('Nested text', $notes[1]['content']);
    }

    public function testParseSectionByCodeWithUnknownCodeReturnsEmpty(): void
    {
        $parser = new CdaTextParser(self::XML_WITH_NAMESPACE);
        $notes = $parser->parseSectionByCode('99999-9');

        $this->assertSame([], $notes);
    }

    public function testGenerateConsolidatedTextNoteFormatsOutput(): void
    {
        $parser = new CdaTextParser(self::XML_WITH_NAMESPACE, 'Test Title.');
        $notes = $parser->parseSectionByCode(self::SECTION_CODE);
        $result = $parser->generateConsolidatedTextNote($notes);

        $this->assertStringContainsString('Test Title.', $result);
        $this->assertStringContainsString('Item 1 goal1 Goal A', $result);
        $this->assertStringContainsString('Item 2 goal2 Goal B', $result);
    }

    public function testGenerateConsolidatedTextNoteEmptyNotesReturnsEmpty(): void
    {
        $parser = new CdaTextParser(self::XML_WITH_NAMESPACE);
        $result = $parser->generateConsolidatedTextNote([]);

        $this->assertSame('', $result);
    }

    public function testDefaultTitle(): void
    {
        $parser = new CdaTextParser(self::XML_WITH_NAMESPACE);
        $notes = $parser->parseSectionByCode(self::SECTION_CODE);
        $result = $parser->generateConsolidatedTextNote($notes);

        $this->assertStringContainsString('Imported CarePlan Notes.', $result);
    }
}
