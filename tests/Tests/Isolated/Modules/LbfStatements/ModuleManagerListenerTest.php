<?php

/**
 * Module Manager dispatch allowlist.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use PHPUnit\Framework\TestCase;

    final class ModuleManagerListenerTest extends TestCase
    {
        protected function setUp(): void
        {
            $file = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/ModuleManagerListener.php';
            if (!class_exists('ModuleManagerListener', false)) {
                require_once $file;
            }
        }

        public function testAllowlistAndNamespace(): void
        {
            if (!class_exists('ModuleManagerListener')) {
                $this->markTestSkipped('ModuleManagerListener did not load.');
            }
            $listener = \ModuleManagerListener::initListenerSelf();
            $this->assertSame('Success', $listener->moduleManagerAction('install', '1', 'Success'));
            $this->assertSame('Success', $listener->moduleManagerAction('enable', '1', 'Success'));
            $this->assertSame('Success', $listener->moduleManagerAction('disable', '1', 'Success'));
            $this->assertSame('Success', $listener->moduleManagerAction('unregister', '1', 'Success'));
            $this->assertSame('Success', $listener->moduleManagerAction('getModuleNamespace', '1', 'Success'));
            $this->assertSame('Success', $listener->moduleManagerAction(['not-a-string'], '1', 'Success'));
            $this->assertSame('OpenEMR\\Modules\\LbfStatements\\', \ModuleManagerListener::getModuleNamespace());
        }
    }
}
