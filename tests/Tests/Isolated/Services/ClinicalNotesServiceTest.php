<?php

/**
 * Clinical Notes change detection and unchanged-save regression tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\ClinicalNotesService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ClinicalNotesServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }
    }

    /** @return array<string, int|string|null> */
    private function existingNote(): array
    {
        return [
            'id' => '10',
            'form_id' => '20',
            'pid' => '30',
            'encounter' => '40',
            'user' => 'original_author',
            'groupname' => 'Original group',
            'authorized' => '0',
            'activity' => '1',
            'code' => 'LOINC:11506-3',
            'codetext' => null,
            'description' => "First line\nSecond line",
            'date' => '2026-01-01',
            'clinical_notes_type' => 'progress_note',
            'clinical_notes_category' => 'clinical_note',
            'note_related_to' => null,
            'uuid' => str_repeat('a', 16),
            'last_updated' => '2026-01-01 12:00:00',
        ];
    }

    public function testAnotherUsersUnchangedSaveReturnsOriginalNoteWithoutWriting(): void
    {
        $existing = $this->existingNote();
        $submitted = $existing;
        unset($submitted['uuid'], $submitted['last_updated']);
        $submitted['authorized'] = 1;
        $submitted['user'] = 'another_user';
        $submitted['groupname'] = 'Another group';
        $submitted['activity'] = 1;
        $submitted['codetext'] = '';
        $submitted['description'] = "First line\r\nSecond line";
        $submitted['note_related_to'] = '[]';

        $service = $this->getMockBuilder(ClinicalNotesService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClinicalRecordNoteById'])
            ->getMock();
        $service->expects($this->once())->method('getClinicalRecordNoteById')->with('10')->willReturn($existing);

        // No database is initialized. Any attempted UPDATE makes this test fail.
        $this->assertSame($existing, $service->saveArray($submitted));
    }

    #[DataProvider('changedFields')]
    public function testIndividualClinicalChangesAreDetected(string $field, mixed $value): void
    {
        $existing = $this->existingNote();
        $submitted = $existing;
        unset($submitted['id']);
        $submitted[$field] = $value;

        $service = (new ReflectionClass(ClinicalNotesService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(ClinicalNotesService::class, 'hasClinicalNoteChanges');
        $this->assertTrue($method->invoke($service, $submitted, $existing));
    }

    /** @return array<string, array{string, mixed}> */
    public static function changedFields(): array
    {
        return [
            'narrative' => ['description', 'An edited note'],
            'narrative whitespace' => ['description', "First line\nSecond line "],
            'code' => ['code', 'LOINC:34109-9'],
            'code text' => ['codetext', 'New code description'],
            'date' => ['date', '2026-01-02'],
            'type' => ['clinical_notes_type', 'consultation_note'],
            'category' => ['clinical_notes_category', 'another_category'],
            'related issues' => ['note_related_to', '["Diabetes"]'],
            'activity' => ['activity', 0],
        ];
    }

    public function testNumericLookingClinicalTextIsNotComparedLoosely(): void
    {
        $service = (new ReflectionClass(ClinicalNotesService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(ClinicalNotesService::class, 'hasClinicalNoteChanges');
        $this->assertTrue($method->invoke($service, ['description' => '01'], ['description' => '1']));
    }

    /** @param array<string, int|string|null> $existing */
    #[DataProvider('invalidContexts')]
    public function testExistingNoteMustBelongToSubmittedContext(array $existing): void
    {
        $submitted = $this->existingNote();
        $service = $this->getMockBuilder(ClinicalNotesService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClinicalRecordNoteById'])
            ->getMock();
        $service->method('getClinicalRecordNoteById')->willReturn($existing);

        $this->expectException(\InvalidArgumentException::class);
        $service->saveArray($submitted);
    }

    /** @return array<string, array{array<string, int|string|null>}> */
    public static function invalidContexts(): array
    {
        return [
            'missing note' => [[]],
            'different form' => [['form_id' => '21', 'pid' => '30', 'encounter' => '40']],
            'different patient' => [['form_id' => '20', 'pid' => '31', 'encounter' => '40']],
            'different encounter' => [['form_id' => '20', 'pid' => '30', 'encounter' => '41']],
        ];
    }
}
