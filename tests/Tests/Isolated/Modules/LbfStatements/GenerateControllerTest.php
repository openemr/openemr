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

namespace {
    $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/src/';
    if (!is_dir($moduleSrc)) {
        throw new RuntimeException('LBF statements module source not found at ' . $moduleSrc);
    }
    spl_autoload_register(static function (string $class) use ($moduleSrc): void {
        $prefix = 'OpenEMR\\Modules\\LbfStatements\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use OpenEMR\Modules\LbfStatements\Controller\GenerateController;
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
    }
}
