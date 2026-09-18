<?php

/**
 * AdminController form_id mutation tests.
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

    use OpenEMR\Modules\LbfStatements\Controller\AdminController;
    use PHPUnit\Framework\TestCase;

    final class AdminControllerTest extends TestCase
    {
        /**
         * POST mutations keep a posted active LBF and reject empty or retired ids.
         */
        public function testMutationFormIdKeepsOnlyActivePostedLayouts(): void
        {
            $active = ['LBFecho', 'LBFecg'];
            $this->assertSame('LBFecho', AdminController::mutationFormId('LBFecho', $active));
            $this->assertSame('', AdminController::mutationFormId('LBFold', $active));
            $this->assertSame('', AdminController::mutationFormId('', $active));
        }
    }
}
