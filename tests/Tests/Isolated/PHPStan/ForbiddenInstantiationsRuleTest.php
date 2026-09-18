<?php

/**
 * Tests for ForbiddenInstantiationsRule (the PHPMailer entry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\ForbiddenInstantiationsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenInstantiationsRule>
 */
final class ForbiddenInstantiationsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenInstantiationsRule();
    }

    public function testFlagsPhpMailerAndLeavesOtherInstantiationsAlone(): void
    {
        $this->analyse(
            [__DIR__ . '/data/forbidden_instantiations_phpmailer_usage.php'],
            [
                [
                    'Direct instantiation of PHPMailer\PHPMailer\PHPMailer is discouraged. '
                        . 'Use MyMailer, which resolves the configured EMAIL_METHOD/SMTP settings instead.',
                    27,
                    'See library/classes/postmaster.php',
                ],
            ],
        );
    }
}
