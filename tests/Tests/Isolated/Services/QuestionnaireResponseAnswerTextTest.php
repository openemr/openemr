<?php

/**
 * The encounter view's text for each FHIR R4 answer type (QuestionnaireResponseService::answerText).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\QuestionnaireResponseService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QuestionnaireResponseAnswerTextTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function answers(): array
    {
        return [
            'dateTime, shown as saved' => [['valueDateTime' => '2026-10-25T09:30:00-05:00'], '2026-10-25T09:30:00-05:00'],
            'time, shown as saved' => [['valueTime' => '09:30:00'], '09:30:00'],
            'date' => [['valueDate' => '2026-10-25'], '2026-10-25'],
            'string' => [['valueString' => 'Some text'], 'Some text'],
            'uri' => [['valueUri' => 'https://example.org'], 'https://example.org'],
            'boolean' => [['valueBoolean' => false], 'No'],
            'integer zero' => [['valueInteger' => 0], '0'],
            'decimal' => [['valueDecimal' => 2.5], '2.5'],
            'coding display' => [['valueCoding' => ['code' => 'LA6568-5', 'display' => 'Not at all']], 'Not at all'],
            'coding with no display shows its code' => [['valueCoding' => ['code' => 'LA6568-5']], 'LA6568-5'],
            'quantity with its unit' => [['valueQuantity' => ['value' => 70, 'unit' => 'kg']], '70 kg'],
            'reference' => [['valueReference' => ['reference' => 'Patient/1', 'display' => 'Jane Example']], 'Jane Example'],
            'attachment' => [['valueAttachment' => ['title' => 'scan.pdf', 'contentType' => 'application/pdf']], 'scan.pdf'],
            'nothing known' => [['valueSomethingElse' => 'x'], 'N/A'],
        ];
    }

    /** @param array<string, mixed> $answer */
    #[DataProvider('answers')]
    public function testEachAnswerTypeHasItsText(array $answer, string $expected): void
    {
        $this->assertSame($expected, QuestionnaireResponseService::answerText($answer));
    }
}
