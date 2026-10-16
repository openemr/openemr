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

namespace OpenEMR\Tests\Isolated\Services\LBF {

    use OpenEMR\Services\LBF\Controller\AdminController;
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
