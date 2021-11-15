<?php

/*
 * This file is part of the Panther project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Panther;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

if (\class_exists(WebTestCase::class)) {
    abstract class PantherTestCase extends WebTestCase
    {
        use WebTestAssertionsTrait;

        public const CHROME = 'chrome';
        public const FIREFOX = 'firefox';

        protected function tearDown(): void
        {
            $this->doTearDown();
        }

        private function doTearDown(): void
        {
            parent::tearDown();
            self::getClient(null);
        }
    }
} else {
    // Without Symfony
    abstract class PantherTestCase extends TestCase
    {
        use PantherTestCaseTrait;

        public const CHROME = 'chrome';
        public const FIREFOX = 'firefox';
    }
}
