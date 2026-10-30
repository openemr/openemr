<?php

/**
 * Generate screen form-choice filtering.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\LBF {

    use OpenEMR\Services\LBF\Controller\GenerateController;
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

    }
}
