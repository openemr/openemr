<?php

/**
 * Isolated test for the default of the fee sheet's reopen setting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FeeSheetReopenDefaultTest extends TestCase
{
    /**
     * Voiding a checkout removes the visit's posted payments and adjustments, so a new install
     * must not do it until an administrator turns it on. library/globals.inc.php can't be loaded
     * in an isolated test because xl() needs the database, so this reads the entry from the source.
     */
    public function testReopeningFromTheFeeSheetDoesNotVoidByDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/library/globals.inc.php');
        $this->assertIsString($source);
        // PHP keeps the last of a repeated array key, so a second entry would be the one that applies.
        $this->assertSame(1, substr_count($source, "'void_checkout_reopen' => ["));
        $this->assertSame(
            1,
            preg_match("/^        'void_checkout_reopen' => \[\n(.*?)^        \],\n/ms", $source, $entry)
        );
        $this->assertMatchesRegularExpression("/^            'bool',.*\n            '0',/m", $entry[1]);
    }
}
