<?php

/**
 * Tests for ForbiddenSessionSuperglobalRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\ForbiddenSessionSuperglobalRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenSessionSuperglobalRule>
 */
final class ForbiddenSessionSuperglobalRuleTest extends RuleTestCase
{
    private const EXPECTED_MESSAGE = 'Direct access to $_SESSION is forbidden. Use the session wrapper instead.';

    private const EXPECTED_TIP = 'Read: SessionWrapperFactory::getInstance()->getActiveSession()->get($key). Write: SessionUtil::setSession() / SessionUtil::unsetSession(). See src/Common/Session/.';

    /** @var list<string> */
    private array $exemptPathFragments = [];

    /**
     * The fixture lives under tests/, which the shipped exemption list skips,
     * so the default instance under test carries no exemptions. Individual
     * tests opt one in via $exemptPathFragments.
     */
    protected function getRule(): Rule
    {
        return new ForbiddenSessionSuperglobalRule($this->exemptPathFragments);
    }

    public function testFlagsSessionSuperglobalReadsAndWrites(): void
    {
        $this->analyse(
            [__DIR__ . '/data/session_superglobal_usage.php'],
            [
                [self::EXPECTED_MESSAGE, 19, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 24, self::EXPECTED_TIP],
            ],
        );
    }

    public function testExemptPathIsIgnored(): void
    {
        $this->exemptPathFragments = ['/PHPStan/data/'];

        $this->analyse([__DIR__ . '/data/session_superglobal_usage.php'], []);
    }

    public function testShippedExemptionsCoverTheFixturePath(): void
    {
        $this->exemptPathFragments = ForbiddenSessionSuperglobalRule::DEFAULT_EXEMPT_PATH_FRAGMENTS;

        // The fixture lives under tests/, so the shipped '/tests/' exemption
        // must silence it — the same way it silences the real test suite.
        $this->analyse([__DIR__ . '/data/session_superglobal_usage.php'], []);
    }
}
