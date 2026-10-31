<?php

/**
 * A C-CDA section is routed to its importer by its templateId root, for the "entries required"
 * templates (suffix .1) and for the "entries optional" templates (no suffix) alike.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaTemplateParse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CdaSectionTemplateRootRoutingTest extends TestCase
{
    private const HANDLERS = ['VitalSign', 'allergy', 'procedure'];

    /**
     * @return array<string, array{string, string}> section templateId root => handler
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sectionRootProvider(): array
    {
        return [
            'vital signs, entries required' => ['2.16.840.1.113883.10.20.22.2.4.1', 'VitalSign'],
            'vital signs, entries optional' => ['2.16.840.1.113883.10.20.22.2.4', 'VitalSign'],
            'allergies, entries required' => ['2.16.840.1.113883.10.20.22.2.6.1', 'allergy'],
            'allergies, entries optional' => ['2.16.840.1.113883.10.20.22.2.6', 'allergy'],
            'procedures, entries required' => ['2.16.840.1.113883.10.20.22.2.7.1', 'procedure'],
            'procedures, entries optional' => ['2.16.840.1.113883.10.20.22.2.7', 'procedure'],
        ];
    }

    #[DataProvider('sectionRootProvider')]
    public function testSectionWithASingleTemplateIdIsRouted(string $root, string $handler): void
    {
        $component = ['section' => ['templateId' => ['root' => $root]]];

        $parser = $this->parserExpectingOnly($handler, $component);
        $parser->parseCDAEntryComponents([$component]);
    }

    #[DataProvider('sectionRootProvider')]
    public function testSectionWithAListOfTemplateIdsIsRouted(string $root, string $handler): void
    {
        // A C-CDA R2.1 section normally carries the template twice: without and with its version.
        $component = ['section' => ['templateId' => [
            ['root' => $root],
            ['root' => $root, 'extension' => '2014-06-09'],
        ]]];

        $parser = $this->parserExpectingOnly($handler, $component);
        $parser->parseCDAEntryComponents([$component]);
    }

    public function testSectionWithAnUnknownRootIsStillSkipped(): void
    {
        $component = ['section' => ['templateId' => ['root' => '2.16.840.1.113883.10.20.22.2.99']]];

        $parser = $this->parserExpectingOnly(null, $component);
        $parser->parseCDAEntryComponents([$component]);
    }

    /**
     * A parser without its constructor (it needs the kernel) whose three importers are mocks:
     * the real ones read the database. Only $expected may be called, once, with $component.
     *
     * @param array<string, mixed> $component
     * @return CdaTemplateParse&MockObject
     */
    private function parserExpectingOnly(?string $expected, array $component): CdaTemplateParse
    {
        $parser = $this->createPartialMock(CdaTemplateParse::class, self::HANDLERS);
        foreach (self::HANDLERS as $handler) {
            if ($handler === $expected) {
                $parser->expects($this->once())->method($handler)->with($component);
            } else {
                $parser->expects($this->never())->method($handler);
            }
        }

        (new ReflectionProperty(CdaTemplateParse::class, 'templateData'))->setValue($parser, []);
        (new ReflectionProperty(CdaTemplateParse::class, 'currentOid'))->setValue($parser, '');
        (new ReflectionProperty(CdaTemplateParse::class, 'ed'))
            ->setValue($parser, $this->createMock(EventDispatcherInterface::class));

        return $parser;
    }
}
