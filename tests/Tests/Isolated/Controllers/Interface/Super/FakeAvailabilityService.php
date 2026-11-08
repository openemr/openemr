<?php

/**
 * In-memory stand-in for QuestionnaireAvailabilityService.
 *
 * Serves a fixed set of questionnaires and rules, and records every write instead of
 * touching the database, so the controller can be exercised and its view rendered in
 * isolated tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Super;

use InvalidArgumentException;
use OpenEMR\Services\Questionnaire\AssignmentRecord;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;

class FakeAvailabilityService extends QuestionnaireAvailabilityService
{
    /** @var list<AssignmentRecord> */
    public array $assigned = [];

    /** @var list<AssignmentRecord> */
    public array $updated = [];

    /** @var list<array{int, bool}> */
    public array $activeChanges = [];

    /** @var list<int> */
    public array $deleted = [];

    /** When set, assign() and updateAssignment() refuse with this service error code. */
    public ?int $refuseWith = null;

    /**
     * @param list<array{id: int, name: string, category: string}> $questionnaires
     * @param list<AssignmentRecord> $rules
     */
    public function __construct(
        private readonly array $questionnaires = [],
        private readonly array $rules = [],
    ) {
    }

    /**
     * The data the render fixtures and controller tests share: three questionnaires, and
     * rules covering every badge and row state the screen draws.
     */
    public static function sample(): self
    {
        return new self(
            [
                ['id' => 50, 'name' => 'PHQ-9', 'category' => 'assessment'],
                ['id' => 56, 'name' => 'AUDIT-C', 'category' => 'sdoh'],
                ['id' => 57, 'name' => 'Back Pain', 'category' => 'legacy_cat'],
            ],
            [
                new AssignmentRecord(id: 1, surface: QuestionnaireSurface::Dashboard, resourceId: 50, resourceName: 'PHQ-9'),
                new AssignmentRecord(id: 2, surface: QuestionnaireSurface::Encounter, resourceId: 50, active: false, resourceName: 'PHQ-9'),
                new AssignmentRecord(id: 3, surface: QuestionnaireSurface::Encounter, resourceId: 50, visitCategoryId: 9, resourceName: 'PHQ-9'),
                new AssignmentRecord(
                    id: 4,
                    surface: QuestionnaireSurface::Encounter,
                    resourceId: 50,
                    facility: 3,
                    provider: 77,
                    visitCategoryId: 5,
                    aclSection: 'encounters',
                    aclLevel: 'notes',
                    seq: 1,
                    resourceName: 'PHQ-9',
                ),
                new AssignmentRecord(id: 5, surface: QuestionnaireSurface::Dashboard, category: 'sdoh'),
                new AssignmentRecord(id: 6, surface: QuestionnaireSurface::Portal, resourceId: 56, aclSection: 'patients', aclLevel: 'bogus', resourceName: 'AUDIT-C'),
                new AssignmentRecord(id: 7, surface: QuestionnaireSurface::Smart, resourceId: 50, clientId: 'app-a', resourceName: 'PHQ-9'),
                new AssignmentRecord(id: 8, surface: QuestionnaireSurface::Smart, resourceId: 56, clientId: 'gone-app', resourceName: 'AUDIT-C'),
            ]
        );
    }

    public function listAssignments(?QuestionnaireSurface $surface = null): array
    {
        if ($surface === null) {
            return $this->rules;
        }

        return array_values(array_filter(
            $this->rules,
            static fn(AssignmentRecord $rule): bool => $rule->surface === $surface
        ));
    }

    public function listManageableQuestionnaires(): array
    {
        return $this->questionnaires;
    }

    public function assign(AssignmentRecord $record): int
    {
        $this->refuseIfAsked();
        $this->assigned[] = $record;

        return 1000 + count($this->assigned);
    }

    public function updateAssignment(AssignmentRecord $record): void
    {
        $this->refuseIfAsked();
        $this->updated[] = $record;
    }

    public function setAssignmentActive(int $assignmentId, bool $active): void
    {
        $this->activeChanges[] = [$assignmentId, $active];
    }

    public function deleteAssignment(int $assignmentId): void
    {
        $this->deleted[] = $assignmentId;
    }

    private function refuseIfAsked(): void
    {
        if ($this->refuseWith !== null) {
            throw new InvalidArgumentException('refused by the fake', $this->refuseWith);
        }
    }
}
