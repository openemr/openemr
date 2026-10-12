<?php

/**
 * Tests for ForbiddenAuthorizationHeaderReadRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\ForbiddenAuthorizationHeaderReadRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenAuthorizationHeaderReadRule>
 */
final class ForbiddenAuthorizationHeaderReadRuleTest extends RuleTestCase
{
    private const EXPECTED_MESSAGE = 'Direct read of the Authorization header is forbidden. Bearer tokens must flow through BearerTokenAuthorizationStrategy so the signature, revocation, scope, and trusted-user checks run.';

    private const EXPECTED_TIP = 'If you need the authenticated user or client, read it from the request attributes set by BearerTokenAuthorizationStrategy (userId, clientId, oauth_scopes). If you are implementing a new authorization strategy, add it to the exempt list in ForbiddenAuthorizationHeaderReadRule::DEFAULT_EXEMPT_PATH_FRAGMENTS.';

    /** @var list<string> */
    private array $exemptPathFragments = [];

    /**
     * The fixture lives under tests/, which the shipped exemption list
     * skips, so the default instance under test carries no exemptions.
     * Individual tests opt one in via $exemptPathFragments.
     */
    protected function getRule(): Rule
    {
        return new ForbiddenAuthorizationHeaderReadRule($this->exemptPathFragments);
    }

    public function testFlagsAuthorizationHeaderReads(): void
    {
        $this->analyse(
            [__DIR__ . '/data/authorization_header_usage.php'],
            [
                [self::EXPECTED_MESSAGE, 21, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 26, self::EXPECTED_TIP],
            ],
        );
    }

    public function testDoesNotFlagOtherHeaderReads(): void
    {
        // readOtherHeader() + readOtherBagValue() + dummyGetterCallWithAuthString()
        // all live in the same fixture; the first assertion above exercises
        // the two Authorization-header reads only — the three sibling calls
        // must stay out of the error list.
        $this->analyse(
            [__DIR__ . '/data/authorization_header_usage.php'],
            [
                [self::EXPECTED_MESSAGE, 21, self::EXPECTED_TIP],
                [self::EXPECTED_MESSAGE, 26, self::EXPECTED_TIP],
            ],
        );
    }

    public function testExemptPathIsIgnored(): void
    {
        $this->exemptPathFragments = ['/PHPStan/data/'];

        $this->analyse([__DIR__ . '/data/authorization_header_usage.php'], []);
    }

    public function testShippedExemptionsCoverTheFixturePath(): void
    {
        $this->exemptPathFragments = ForbiddenAuthorizationHeaderReadRule::DEFAULT_EXEMPT_PATH_FRAGMENTS;

        $this->analyse([__DIR__ . '/data/authorization_header_usage.php'], []);
    }
}
