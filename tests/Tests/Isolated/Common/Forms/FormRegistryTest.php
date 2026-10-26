<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms;

use OpenEMR\Common\Forms\FormRegistry;
use PHPUnit\Framework\TestCase;

final class FormRegistryTest extends TestCase
{
    public function testAFormWithAnEnabledRegistryRowIsEnabled(): void
    {
        $asked = [];
        $registry = new FormRegistry(static function (string $directory) use (&$asked): int {
            $asked[] = $directory;
            return $directory === 'vitals' ? 1 : 0;
        });

        $this->assertTrue($registry->isEnabled('vitals'));
        $this->assertFalse($registry->isEnabled('track_anything'), 'not registered, or registered and disabled');
        $this->assertSame(['vitals', 'track_anything'], $asked, 'each form is looked up by its own directory');
    }

    public function testMoreThanOneEnabledRowStillMeansEnabled(): void
    {
        $this->assertTrue((new FormRegistry(static fn(string $directory): int => 2))->isEnabled('track_anything'));
    }
}
