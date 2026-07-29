<?php

/**
 * FhirQuestionnaireFormServiceTest unit tests the normalization of stored
 * questionnaire items whose array-typed fields were double-encoded as JSON
 * strings (an artifact of some LForms conversions), which otherwise makes the
 * strict generated FHIR model constructors throw and fail the whole
 * Questionnaire collection response.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services\FHIR\Questionnaire;

use OpenEMR\Services\FHIR\Questionnaire\FhirQuestionnaireFormService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirQuestionnaireFormServiceTest extends TestCase
{
    /**
     * @param array<mixed> $item
     * @return array<mixed>
     */
    private function normalize(array $item): array
    {
        $reflection = new \ReflectionMethod(FhirQuestionnaireFormService::class, 'normalizeQuestionnaireItem');
        $result = $reflection->invoke(null, $item);
        $this->assertIsArray($result);
        return $result;
    }

    public function testWellFormedItemPassesThroughUnchanged(): void
    {
        $item = [
            'linkId' => '1',
            'text' => 'Do you smoke?',
            'type' => 'boolean',
            'enableWhen' => [['question' => '0', 'operator' => '=', 'answerBoolean' => true]],
            'code' => [['system' => 'http://loinc.org', 'code' => '72166-2']],
        ];
        $this->assertSame($item, $this->normalize($item));
    }

    public function testDoubleEncodedEnableWhenIsRepaired(): void
    {
        $enableWhen = [['question' => '0', 'operator' => '=', 'answerBoolean' => true]];
        $item = [
            'linkId' => '1',
            'enableWhen' => json_encode($enableWhen),
        ];
        $normalized = $this->normalize($item);
        $this->assertSame($enableWhen, $normalized['enableWhen'], "Double-encoded enableWhen should decode back to its array form");
    }

    #[DataProvider('doubleEncodedFieldProvider')]
    public function testDoubleEncodedArrayFieldsAreRepaired(string $field): void
    {
        $value = [['some' => 'object']];
        $normalized = $this->normalize(['linkId' => '1', $field => json_encode($value)]);
        $this->assertSame($value, $normalized[$field]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function doubleEncodedFieldProvider(): array
    {
        return [
            'enableWhen' => ['enableWhen'],
            'code' => ['code'],
            'answerOption' => ['answerOption'],
            'initial' => ['initial'],
            'extension' => ['extension'],
            'modifierExtension' => ['modifierExtension'],
            'item' => ['item'],
        ];
    }

    #[DataProvider('unrepairableFieldValueProvider')]
    public function testUnrepairableFieldIsDropped(mixed $badValue): void
    {
        $normalized = $this->normalize(['linkId' => '1', 'text' => 'Q', 'enableWhen' => $badValue]);
        $this->assertArrayNotHasKey('enableWhen', $normalized, "Unrepairable field should be dropped, not passed to the model");
        $this->assertSame('1', $normalized['linkId'], "Other fields should be untouched");
        $this->assertSame('Q', $normalized['text'], "Other fields should be untouched");
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unrepairableFieldValueProvider(): array
    {
        return [
            'non-json string' => ['always'],
            'json scalar string' => ['42'],
            'integer' => [7],
            'boolean' => [true],
            'object' => [new \stdClass()],
        ];
    }

    public function testScalarTypedFieldsAreNotTouched(): void
    {
        $item = ['linkId' => '1', 'text' => 'Plain question', 'type' => 'string', 'required' => true];
        $this->assertSame($item, $this->normalize($item));
    }
}
