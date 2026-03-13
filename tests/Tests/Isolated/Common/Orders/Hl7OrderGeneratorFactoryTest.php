<?php

/**
 * Isolated tests for HL7 Order Generator factory and interface compliance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Orders;

use OpenEMR\Common\Orders\DefaultHl7OrderGenerator;
use OpenEMR\Common\Orders\Hl7OrderGeneratorFactory;
use OpenEMR\Common\Orders\Hl7OrderGeneratorInterface;
use OpenEMR\Common\Orders\Hl7OrderResult;
use OpenEMR\Common\Orders\LabCorpHl7OrderGenerator;
use OpenEMR\Common\Orders\QuestHl7OrderGenerator;
use OpenEMR\Common\Orders\UniversalHl7OrderGenerator;
use PHPUnit\Framework\TestCase;

class Hl7OrderGeneratorFactoryTest extends TestCase
{
    /**
     * @dataProvider concreteClassProvider
     */
    public function testAllConcreteClassesImplementInterface(string $className): void
    {
        $this->assertTrue(
            is_subclass_of($className, Hl7OrderGeneratorInterface::class),
            "$className must implement Hl7OrderGeneratorInterface"
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function concreteClassProvider(): array
    {
        return [
            'DefaultHl7OrderGenerator'   => [DefaultHl7OrderGenerator::class],
            'LabCorpHl7OrderGenerator'   => [LabCorpHl7OrderGenerator::class],
            'QuestHl7OrderGenerator'     => [QuestHl7OrderGenerator::class],
            'UniversalHl7OrderGenerator' => [UniversalHl7OrderGenerator::class],
        ];
    }

    public function testSupportedLabTypesReturnsExpectedKeys(): void
    {
        $supported = Hl7OrderGeneratorFactory::supportedLabTypes();

        $this->assertContains('labcorp', $supported);
        $this->assertContains('quest', $supported);
        $this->assertContains('ammon', $supported);
        $this->assertContains('clarity', $supported);
    }

    public function testHl7OrderResultConstructionWithDefaults(): void
    {
        $result = new Hl7OrderResult('MSH|^~\\&|...');

        $this->assertSame('MSH|^~\\&|...', $result->hl7);
        $this->assertSame('', $result->requisitionData);
    }

    public function testHl7OrderResultConstructionWithRequisitionData(): void
    {
        $result = new Hl7OrderResult('MSH|^~\\&|...', 'REQ-DATA-123');

        $this->assertSame('MSH|^~\\&|...', $result->hl7);
        $this->assertSame('REQ-DATA-123', $result->requisitionData);
    }
}
