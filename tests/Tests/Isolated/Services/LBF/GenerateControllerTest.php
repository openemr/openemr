<?php

/**
 * Generate screen form-choice filtering.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\LBF {

    use OpenEMR\Services\LBF\Controller\GenerateController;
    use OpenEMR\Services\LBF\LayoutCatalog;
    use PHPUnit\Framework\TestCase;

    final class GenerateControllerTest extends TestCase
    {
        /**
         * An instance-only request must not keep forms the patient does not have.
         */
        public function testLimitFormsToPatientDropsFormsThePatientDoesNotHave(): void
        {
            $choices = [
                ['form_id' => 'LBFecho', 'title' => 'Echo'],
                ['form_id' => 'LBFnuc', 'title' => 'Nuc'],
            ];
            [$kept, $formId, $instanceId] = GenerateController::limitFormsToPatient(
                $choices,
                ['LBFecho'],
                'LBFnuc',
                12
            );
            $this->assertSame(['LBFecho'], array_column($kept, 'form_id'));
            $this->assertSame('', $formId);
            $this->assertSame(0, $instanceId);
        }

        /**
         * Keep the selected form when the patient has that layout.
         */
        public function testLimitFormsToPatientKeepsAFormThePatientHas(): void
        {
            $choices = [
                ['form_id' => 'LBFecho', 'title' => 'Echo'],
                ['form_id' => 'LBFnuc', 'title' => 'Nuc'],
            ];
            [$kept, $formId, $instanceId] = GenerateController::limitFormsToPatient(
                $choices,
                ['LBFecho', 'LBFnuc'],
                'LBFnuc',
                12
            );
            $this->assertCount(2, $kept);
            $this->assertSame('LBFnuc', $formId);
            $this->assertSame(12, $instanceId);
        }

        /**
         * The editor keeps the note and adds a generated sentence once.
         */
        public function testEditorParagraphKeepsTheNoteAndAddsANewSentence(): void
        {
            $text = GenerateController::editorParagraph('Mild note.', 'EF is mildly reduced.');
            $this->assertSame('Mild note. EF is mildly reduced.', $text);
            $this->assertSame($text, GenerateController::editorParagraph($text, 'EF is mildly reduced.'));
            $again = GenerateController::editorParagraph("Discussed risks, etc.", 'Follow-up advised.');
            $this->assertStringContainsString('Follow-up advised.', $again);
            $this->assertSame(
                $again,
                GenerateController::editorParagraph($again, 'Follow-up advised.')
            );
            $asked = GenerateController::editorParagraph('Pulmonary embolism?', 'Pulmonary embolism.');
            $this->assertStringContainsString('Pulmonary embolism?', $asked);
            $this->assertStringContainsString('Pulmonary embolism.', $asked);
        }

        /**
         * No generated sentence leaves the note unchanged.
         */
        public function testEditorParagraphLeavesTheNoteWhenNothingWasGenerated(): void
        {
            $this->assertSame('Mild note.', GenerateController::editorParagraph('Mild note.', ''));
            $this->assertSame('EF is normal.', GenerateController::editorParagraph('', 'EF is normal.'));
        }

        /**
         * A patient with no eligible forms sees an empty list.
         */
        public function testLimitFormsToPatientClearsEverythingWhenThePatientHasNone(): void
        {
            $choices = [
                ['form_id' => 'LBFecho', 'title' => 'Echo'],
            ];
            [$kept, $formId, $instanceId] = GenerateController::limitFormsToPatient(
                $choices,
                [],
                'LBFecho',
                4
            );
            $this->assertSame([], $kept);
            $this->assertSame('', $formId);
            $this->assertSame(0, $instanceId);
        }

        /**
         * An empty layout rule does not add a write check.
         */
        public function testLayoutWriteDecisionAllowsAnOpenLayout(): void
        {
            $this->assertTrue(GenerateController::layoutWriteDecision('', false, false));
        }

        /**
         * A super user follows the layout form and is allowed.
         */
        public function testLayoutWriteDecisionAllowsASuperUser(): void
        {
            $this->assertTrue(GenerateController::layoutWriteDecision('patients|demo', true, false));
        }

        /**
         * Anyone else needs write on the layout rule.
         */
        public function testLayoutWriteDecisionRequiresWriteOnTheLayout(): void
        {
            $this->assertFalse(GenerateController::layoutWriteDecision('patients|demo', false, false));
            $this->assertTrue(GenerateController::layoutWriteDecision('patients|demo', false, true));
        }

        /**
         * Storing a paragraph needs encounter-notes write and layout write.
         */
        public function testMaySaveParagraphRequiresNotesWrite(): void
        {
            $this->assertFalse(GenerateController::maySaveParagraph(false, true));
            $this->assertFalse(GenerateController::maySaveParagraph(true, false));
            $this->assertTrue(GenerateController::maySaveParagraph(true, true));
        }

        /**
         * A missing or inactive layout is refused before the access rule.
         */
        public function testLayoutWriteDecisionRefusesAnInactiveLayout(): void
        {
            $this->assertFalse(GenerateController::layoutWriteDecision('', true, true, false));
            $this->assertFalse(GenerateController::layoutWriteDecision('patients|demo', true, true, false));
        }

        /**
         * Any-encounter notes open every encounter. Own notes do not.
         */
        public function testEncounterVisibleFollowsTheEncounterList(): void
        {
            $this->assertTrue(GenerateController::encounterVisible(true, false, 'other', 'drwho', true));
            $this->assertTrue(GenerateController::encounterVisible(false, true, 'drwho', 'drwho', true));
            $this->assertFalse(GenerateController::encounterVisible(false, true, 'other', 'drwho', true));
            $this->assertFalse(GenerateController::encounterVisible(true, true, 'drwho', 'drwho', false));
            $this->assertFalse(GenerateController::encounterVisible(false, true, '', 'drwho', true));
        }

        /**
         * A save is current only when the stored paragraph still matches the loaded hash.
         */
        public function testParagraphIsCurrentMatchesTheLoadedText(): void
        {
            $stored = "Mild note.\nEF is mildly reduced.";
            $baseline = GenerateController::paragraphBaseline(4, 'summary_a', $stored);
            $this->assertTrue(GenerateController::paragraphIsCurrent($baseline, 4, 'summary_a', $stored));
            $this->assertFalse(GenerateController::paragraphIsCurrent($baseline, 4, 'summary_a', $stored . ' more'));
            $this->assertFalse(GenerateController::paragraphIsCurrent($baseline, 4, 'summary_b', $stored));
            $this->assertFalse(GenerateController::paragraphIsCurrent($baseline, 9, 'summary_a', $stored));
            $this->assertFalse(GenerateController::paragraphIsCurrent('', 4, 'summary_a', $stored));
        }

        public function testCategorySpecAllowsAnOpenCategory(): void
        {
            $this->assertTrue(GenerateController::categorySpecAllows('', false));
            $this->assertTrue(GenerateController::categorySpecAllows('encounters|notes', true));
            $this->assertFalse(GenerateController::categorySpecAllows('encounters|notes', false));
        }

        public function testStoresInLayoutRejectsEncounterAttributes(): void
        {
            $this->assertTrue(LayoutCatalog::storesInLayout(''));
            $this->assertTrue(LayoutCatalog::storesInLayout('F'));
            $this->assertFalse(LayoutCatalog::storesInLayout('E'));
            $this->assertFalse(LayoutCatalog::storesInLayout('D'));
            $this->assertFalse(LayoutCatalog::storesInLayout('H'));
        }


        /**
         * A changed measurement does not match the posted hash.
         */
        public function testMeasurementBaselineChangesWithTheValue(): void
        {
            $first = GenerateController::measurementBaseline(['ef' => '55']);
            $this->assertTrue(GenerateController::measurementsAreCurrent($first, ['ef' => '55']));
            $this->assertFalse(GenerateController::measurementsAreCurrent($first, ['ef' => '40']));
        }

        /**
         * A busy lock keeps the submitted draft. A stale save shows the stored paragraph.
         */
        public function testTextareaKeepsARetryableDraft(): void
        {
            $this->assertSame(
                'Typed.',
                GenerateController::textareaAfterSave('Stored.', 'Proposed.', 'Typed.', false, true)
            );
            $this->assertSame(
                'Stored. Proposed.',
                GenerateController::textareaAfterSave('Stored.', 'Proposed.', 'Typed.', false, false)
            );
        }
    }
}
