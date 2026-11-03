<?php

/**
 * Clinical Notes timestamp regression tests using the real database save path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\ClinicalNotesService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ClinicalNotesServiceTest extends TestCase
{
    private ClinicalNotesService $service;

    /** @var list<int> */
    private array $noteIds = [];

    /** @var list<string> */
    private array $registeredUuids = [];

    private const ORIGINAL_TIMESTAMP = '2001-01-01 12:00:00';

    protected function setUp(): void
    {
        // Exercise the real read/write methods without running constructor-wide
        // UUID backfills for unrelated clinical notes in the test database.
        $this->service = (new ReflectionClass(ClinicalNotesService::class))->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        foreach ($this->noteIds as $id) {
            QueryUtils::sqlStatementThrowException('DELETE FROM form_clinical_notes WHERE id = ?', [$id]);
        }
        foreach ($this->registeredUuids as $uuid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid = ?', [$uuid]);
        }
        parent::tearDown();
    }

    /** @return array<string, int|string|null> */
    private function noteRecord(): array
    {
        return [
            'form_id' => 987654321,
            'pid' => 987654321,
            'encounter' => '987654321',
            'user' => 'original_author',
            'groupname' => 'Original group',
            'authorized' => 0,
            'activity' => ClinicalNotesService::ACTIVITY_ACTIVE,
            'code' => 'LOINC:11506-3',
            'codetext' => null,
            'description' => "Note A\nOriginal narrative",
            'date' => '2026-01-01',
            'clinical_notes_type' => 'progress_note',
            'clinical_notes_category' => 'clinical_note',
            'note_related_to' => '[]',
        ];
    }

    /** @return array<string, int|string|null> */
    private function insertOriginalNote(): array
    {
        $record = $this->noteRecord();
        $record['uuid'] = random_bytes(16);
        $record['last_updated'] = self::ORIGINAL_TIMESTAMP;
        $sets = array_map(fn(string $field): string => $field . ' = ?', array_keys($record));
        $id = QueryUtils::sqlInsert('INSERT INTO form_clinical_notes SET ' . implode(', ', $sets), array_values($record));
        $this->noteIds[] = $id;
        $record['id'] = $id;
        return $record;
    }

    /**
     * @param array<string, int|string|null> $record
     * @return array<string, int|string|null>
     */
    private function submitAsAnotherUser(array $record): array
    {
        unset($record['uuid'], $record['last_updated']);
        $record['user'] = 'another_user';
        $record['groupname'] = 'Another group';
        $record['authorized'] = 1;
        return $record;
    }

    public function testMetadataOnlySavePreservesOriginalTimestampAndAuthorship(): void
    {
        $original = $this->insertOriginalNote();
        $before = $this->service->getClinicalRecordNoteById($original['id']);
        $submitted = $this->submitAsAnotherUser($original);
        $submitted['description'] = "Note A\r\nOriginal narrative";
        $submitted['codetext'] = '';
        $this->service->saveArray($submitted);

        $this->assertSame($before, $this->service->getClinicalRecordNoteById($original['id']));
    }

    public function testEditingNoteBLeavesNoteAUnchanged(): void
    {
        $noteA = $this->insertOriginalNote();
        $noteB = $this->insertOriginalNote();
        $submittedB = $this->submitAsAnotherUser($noteB);
        $submittedB['description'] = 'Edited Note B';

        $this->service->saveArray($this->submitAsAnotherUser($noteA));
        $this->service->saveArray($submittedB);

        $savedA = $this->service->getClinicalRecordNoteById($noteA['id']);
        $savedB = $this->service->getClinicalRecordNoteById($noteB['id']);
        $this->assertIsArray($savedA);
        $this->assertIsArray($savedB);
        $this->assertSame(self::ORIGINAL_TIMESTAMP, $savedA['last_updated']);
        $this->assertSame($noteA['description'], $savedA['description']);
        $this->assertNotSame(self::ORIGINAL_TIMESTAMP, $savedB['last_updated']);
        $this->assertSame('Edited Note B', $savedB['description']);
        $this->assertSame('original_author', $savedB['user']);
        $this->assertSame('Original group', $savedB['groupname']);
        $this->assertContains($savedB['authorized'], [0, '0']);
        $this->assertSame($noteB['uuid'], $savedB['uuid']);
    }

    public function testAddingNoteBLeavesNoteAUnchanged(): void
    {
        $noteA = $this->insertOriginalNote();
        $newNote = $this->submitAsAnotherUser($this->noteRecord());
        $newNote['description'] = 'New Note B';

        $this->service->saveArray($this->submitAsAnotherUser($noteA));
        $saved = $this->service->saveArray($newNote);
        $this->assertIsInt($saved['id']);
        $this->assertIsString($saved['uuid']);
        $this->noteIds[] = $saved['id'];
        $this->registeredUuids[] = $saved['uuid'];
        $savedB = $this->service->getClinicalRecordNoteById($saved['id']);
        $this->assertIsArray($savedB);
        $this->assertIsString($savedB['uuid']);

        $savedA = $this->service->getClinicalRecordNoteById($noteA['id']);
        $this->assertIsArray($savedA);
        $this->assertSame(self::ORIGINAL_TIMESTAMP, $savedA['last_updated']);
        $this->assertSame('New Note B', $savedB['description']);
        $this->assertSame('another_user', $savedB['user']);
        $this->assertSame('Another group', $savedB['groupname']);
        $this->assertContains($savedB['authorized'], [1, '1']);
        $this->assertNotSame(self::ORIGINAL_TIMESTAMP, $savedB['last_updated']);
        $this->assertSame(16, strlen($savedB['uuid']));

    }

    public function testInactivationStillUpdatesTimestamp(): void
    {
        $original = $this->insertOriginalNote();
        $this->service->setActivityForClinicalRecord(
            $original['id'],
            $original['pid'],
            $original['encounter'],
            ClinicalNotesService::ACTIVITY_INACTIVE
        );

        $saved = $this->service->getClinicalRecordNoteById($original['id']);
        $this->assertIsArray($saved);
        $this->assertContains($saved['activity'], [0, '0']);
        $this->assertNotSame(self::ORIGINAL_TIMESTAMP, $saved['last_updated']);
    }
}
