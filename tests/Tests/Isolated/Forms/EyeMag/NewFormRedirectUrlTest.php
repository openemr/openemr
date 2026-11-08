<?php

/**
 * Verify that eye_mag/new.php redirect URLs include the encounter parameter.
 *
 * Regression test for issue #10844. After the navbar fix ensured
 * load_form.php receives pid and encounter, a second failure point
 * remained: new.php's formJump() redirects to view_form.php omitted
 * the encounter parameter. This caused view_form.php to clobber
 * $encounter with an undefined $_GET value, leading to a 404 when
 * view.php's IDOR guard compared the stored encounter against the
 * now-null session encounter.
 *
 * Since new.php is procedural PHP that requires the full OpenEMR
 * bootstrap and database, this test reads the source file and verifies
 * that every formJump() call targeting view_form.php includes the
 * encounter parameter. This catches regressions where the parameter
 * is accidentally removed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class NewFormRedirectUrlTest extends TestCase
{
    private string $newPhpSource;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 5) . '/interface/forms/eye_mag/new.php';
        $source = file_get_contents($path);
        $this->assertNotFalse($source, "Could not read {$path}");
        $this->newPhpSource = $source;
    }

    /**
     * Every formJump() redirect to view_form.php must include encounter=.
     *
     * Before the fix, the redirects were:
     *   formJump('./view_form.php?formname=eye_mag&id=5&pid=1')
     *
     * Without encounter=, view_form.php set $encounter from $_GET['encounter']
     * which was undefined, clobbering the session value and causing a 404.
     */
    #[Test]
    public function allViewFormRedirectsIncludeEncounter(): void
    {
        // Extract full lines containing formJump calls that target view_form.php.
        // We match the whole line because the call spans nested function calls
        // like attr($erow['form_id']) whose parens break simple [^)]* patterns.
        preg_match_all(
            '/^.*formJump\s*\(.*view_form\.php.*$/m',
            $this->newPhpSource,
            $matches
        );

        $this->assertNotEmpty(
            $matches[0],
            'Expected at least one formJump() call targeting view_form.php in new.php'
        );

        foreach ($matches[0] as $formJumpCall) {
            $this->assertStringContainsString(
                'encounter',
                $formJumpCall,
                "formJump() redirect to view_form.php is missing encounter parameter: {$formJumpCall}"
            );
        }
    }

    /**
     * Every formJump() redirect to view_form.php must include pid=.
     *
     * The pid parameter was already present before the #10844 fix, but
     * this test ensures it is not accidentally removed in future changes.
     */
    #[Test]
    public function allViewFormRedirectsIncludePid(): void
    {
        preg_match_all(
            '/^.*formJump\s*\(.*view_form\.php.*$/m',
            $this->newPhpSource,
            $matches
        );

        $this->assertNotEmpty(
            $matches[0],
            'Expected at least one formJump() call targeting view_form.php in new.php'
        );

        foreach ($matches[0] as $formJumpCall) {
            $this->assertStringContainsString(
                'pid',
                $formJumpCall,
                "formJump() redirect to view_form.php is missing pid parameter: {$formJumpCall}"
            );
        }
    }

    /**
     * new.php must guard against a missing or zero encounter.
     *
     * Before the fix, new.php used `date("Ymd")` as a fallback when
     * the session encounter was empty, creating forms with a bogus
     * encounter ID. The fix replaces this with an early-exit guard.
     * This test verifies the guard exists and the date fallback is gone.
     */
    #[Test]
    public function encounterGuardExistsAndDateFallbackRemoved(): void
    {
        // The date("Ymd") fallback must NOT be present
        $this->assertStringNotContainsString(
            'date("Ymd")',
            $this->newPhpSource,
            'new.php must not use date("Ymd") as encounter fallback (the root cause of #10844)'
        );

        // An encounter guard (checking for 0 or empty) must exist before DB queries
        $this->assertMatchesRegularExpression(
            '/if\s*\(.*\$encounter.*===\s*0/',
            $this->newPhpSource,
            'new.php must guard against encounter === 0'
        );
    }
}
