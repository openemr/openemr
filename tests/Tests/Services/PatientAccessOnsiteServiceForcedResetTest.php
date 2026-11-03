<?php

/**
 * PatientAccessOnsiteServiceForcedResetTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientAccessOnsiteService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PatientAccessOnsiteServiceForcedResetTest extends TestCase
{
    // No real user has this id; its user_settings rows are the test's own.
    private const FIXTURE_USER_ID = 2000000001;

    private const SETTING_LABEL = 'portal_login.credential_reset_disable';

    /**
     * Starts every test with no dialog choice stored for the fixture user.
     */
    protected function setUp(): void
    {
        $this->deleteFixtureSetting();
    }

    /**
     * Removes the dialog choice a test stored for the fixture user.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtureSetting();
    }

    /**
     * Policies that decide on their own, without the user's dialog choice.
     *
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fixedPolicyProvider(): array
    {
        return [
            'force the password change (default)' => ['0', 0],
            'skip the password change' => ['1', 1],
            'unknown value forces the password change' => ['', 0],
        ];
    }

    /**
     * '0' and '1' ignore any stored dialog choice, so one is stored to prove it.
     */
    #[Test]
    #[DataProvider('fixedPolicyProvider')]
    public function fixedPolicyIgnoresTheDialogChoice(string $policy, int $expected): void
    {
        $this->storeDialogChoice($expected === 1 ? '0' : '1');

        $this->assertSame(
            $expected,
            PatientAccessOnsiteService::forcedResetDisableForPolicy($policy, self::FIXTURE_USER_ID)
        );
    }

    /**
     * Under '2' a checked "disable forced reset" box in the dialog skips the password change.
     */
    #[Test]
    public function userOptionalPolicyFollowsACheckedDialogChoice(): void
    {
        $this->storeDialogChoice('1');

        $this->assertSame(1, PatientAccessOnsiteService::forcedResetDisableForPolicy('2', self::FIXTURE_USER_ID));
    }

    /**
     * Under '2' an unchecked box forces the password change.
     */
    #[Test]
    public function userOptionalPolicyFollowsAnUncheckedDialogChoice(): void
    {
        $this->storeDialogChoice('0');

        $this->assertSame(0, PatientAccessOnsiteService::forcedResetDisableForPolicy('2', self::FIXTURE_USER_ID));
    }

    /**
     * Under '2' a user who never opened the dialog gets the forced password change.
     */
    #[Test]
    public function userOptionalPolicyWithoutADialogChoiceForcesTheChange(): void
    {
        $this->assertSame(0, PatientAccessOnsiteService::forcedResetDisableForPolicy('2', self::FIXTURE_USER_ID));
    }

    /**
     * Stores the fixture user's "disable forced reset" checkbox state the way the dialog persists it.
     */
    private function storeDialogChoice(string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `user_settings` (`setting_user`, `setting_label`, `setting_value`) VALUES (?, ?, ?)",
            [self::FIXTURE_USER_ID, self::SETTING_LABEL, $value]
        );
    }

    /**
     * Deletes the fixture user's stored checkbox state.
     */
    private function deleteFixtureSetting(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `user_settings` WHERE `setting_user` = ? AND `setting_label` = ?",
            [self::FIXTURE_USER_ID, self::SETTING_LABEL]
        );
    }
}
