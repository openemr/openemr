<?php

/**
 * HscPrivateXlOrWarnTest
 *
 * Tests the hsc_private_xl_or_warn() helper used by xlt()/xla()/xlj() to
 * translate a string while accepting nullable input.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class HscPrivateXlOrWarnTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['disable_translation'] = true;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['disable_translation']);
    }

    public function testNullKeyReturnsEmptyString(): void
    {
        $this->assertSame('', hsc_private_xl_or_warn(null));
    }

    public function testNonNullKeyIsPassedToXl(): void
    {
        // With disable_translation set, xl() is a pass-through that returns
        // its argument verbatim.
        $this->assertSame('Hello', hsc_private_xl_or_warn('Hello'));
    }
}
