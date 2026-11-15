<?php

/**
 * Isolated tests for BaseController::getControllerName()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use PHPUnit\Framework\TestCase;

class BaseControllerNameTest extends TestCase
{
    public function testAnonymousControllerNameHasNoNullByte(): void
    {
        $controller = new class extends BaseController {
        };

        // The raw anonymous class name carries a null byte; the controller
        // name feeds view paths, where PHP 8.6 rejects null bytes.
        $this->assertStringContainsString("\0", $controller::class);
        $this->assertSame('Base@anonymous', $controller->getControllerName());
    }
}
