<?php

/**
 * Tests for ForbiddenSessionAllowWriteRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\ForbiddenSessionAllowWriteRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenSessionAllowWriteRule>
 */
final class ForbiddenSessionAllowWriteRuleTest extends RuleTestCase
{
    private const EXPECTED_MESSAGE = 'Setting $sessionAllowWrite holds the session lock for the whole request.';

    private const EXPECTED_TIP = 'Leave the session read-only and write through SessionUtil::setSession(), which reopens it only for the write. A script that must write after output has started keeps the flag with // @phpstan-ignore openemr.forbiddenSessionAllowWrite (reason).';

    /** @var list<string> */
    private array $exemptPathFragments = [];

    /**
     * The fixture lives under tests/, which the shipped exemption list skips,
     * so the default instance under test carries no exemptions. Individual
     * tests opt one in via $exemptPathFragments.
     */
    protected function getRule(): Rule
    {
        return new ForbiddenSessionAllowWriteRule($this->exemptPathFragments);
    }

    public function testFlagsAssignmentsThatCanBeTruthy(): void
    {
        $this->analyse(
            [__DIR__ . '/data/session_allow_write_usage.php'],
            [
                [self::EXPECTED_MESSAGE, 19, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 25, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 31, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 53, self::EXPECTED_TIP],
            ],
        );
    }

    public function testExemptPathIsIgnored(): void
    {
        $this->exemptPathFragments = ['/PHPStan/data/'];

        $this->analyse([__DIR__ . '/data/session_allow_write_usage.php'], []);
    }

    public function testShippedExemptionsCoverTheFixturePath(): void
    {
        $this->exemptPathFragments = ForbiddenSessionAllowWriteRule::DEFAULT_EXEMPT_PATH_FRAGMENTS;

        // The fixture lives under tests/, so the shipped '/tests/' exemption
        // must silence it — the same way it silences the real test suite.
        $this->analyse([__DIR__ . '/data/session_allow_write_usage.php'], []);
    }
}
