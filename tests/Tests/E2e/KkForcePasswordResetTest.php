<?php

/**
 * KkForcePasswordResetTest class
 *
 * Tests the force password reset feature: when an admin sets
 * force_new_password on a user, that user sees a password change
 * form on next login, and after changing their password the flag
 * is cleared and subsequent logins are normal.
 *
 * Self-contained: creates its own test user via direct DB insertion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class KkForcePasswordResetTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $client;
    private $crawler;

    private const USERNAME = 'forcepwtest';
    private const PASSWORD = 'Test12te$t';
    private const NEW_PASSWORD = 'N3wTest12te$t';
    private const FIRSTNAME = 'Force';
    private const LASTNAME = 'Reset';

    protected function setUp(): void
    {
        $this->cleanDatabase();
        $this->createTestUser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `users_secure` WHERE `username` = ?", [self::USERNAME]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `groups` WHERE `user` = ?", [self::USERNAME]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `users` WHERE `username` = ?", [self::USERNAME]);
    }

    /**
     * Create a minimal test user with force_new_password enabled.
     *
     * Populates users, users_secure, groups, and phpGACL tables —
     * all required for a successful login.
     */
    private function createTestUser(): void
    {
        $userId = QueryUtils::sqlInsert(
            "INSERT INTO `users` (`username`, `fname`, `lname`, `active`, `authorized`) VALUES (?, ?, ?, 1, 1)",
            [self::USERNAME, self::FIRSTNAME, self::LASTNAME]
        );

        $hash = password_hash(self::PASSWORD, PASSWORD_DEFAULT);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `users_secure` (`id`, `username`, `password`, `force_new_password`, `last_update_password`) "
            . "VALUES (?, ?, ?, 1, NOW())",
            [$userId, self::USERNAME, $hash]
        );

        // User must belong to a group for login to succeed
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `groups` (`name`, `user`) VALUES ('Default', ?)",
            [self::USERNAME]
        );

        // User must have a phpGACL ARO entry for login to succeed
        AclExtended::setUserAro(
            ['Administrators'],
            self::USERNAME,
            self::FIRSTNAME,
            '',
            self::LASTNAME
        );
    }

    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testForcePasswordReset(): void
    {
        $this->base();
        try {
            // Log in as the test user — should see forced password change tab
            $this->login(self::USERNAME, self::PASSWORD);
            // Tab title comes from user_info.php's <title> tag
            $this->assertActiveTab("Change Password");

            // Switch to the admin iframe containing the password change form
            $this->switchToIFrame(XpathsConstants::ADMIN_IFRAME);
            $this->client->waitFor('//input[@name="curPass"]');

            // Fill in the password change form
            $this->clearAndType('curPass', self::PASSWORD);
            $this->clearAndType('newPass', self::NEW_PASSWORD);
            $this->clearAndType('newPass2', self::NEW_PASSWORD);

            // Submit the form (AJAX via update_password())
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath('//button[contains(@class, "btn-save")]')
                )
            )->click();

            // Wait for the AJAX success response
            $this->client->wait(15)->until(function ($driver) {
                $msg = $driver->findElement(WebDriverBy::id('display_msg'));
                return str_contains((string) $msg->getText(), 'Password change successful');
            });

            // Verify the force flag is cleared in the database
            $result = QueryUtils::querySingleRow(
                "SELECT `force_new_password` FROM `users_secure` WHERE `username` = ?",
                [self::USERNAME]
            );
            $this->assertEquals(0, (int)$result['force_new_password']);

            // Log out and log back in with the new password
            $this->logOut();
            $this->login(self::USERNAME, self::NEW_PASSWORD);

            // Verify normal login — the forced password change tab should not appear
            $this->client->switchTo()->defaultContent();
            $this->crawler = $this->client->refreshCrawler();
            $activeTabText = $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text();
            $this->assertNotSame('Change Password', $activeTabText);

            $this->logOut();
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Set a form field value atomically via JavaScript.
     *
     * WebDriver sendKeys() dispatches key events one-by-one and Chrome
     * can drop keystrokes under CI resource pressure. JavaScript value
     * assignment is atomic and reliable.
     */
    private function clearAndType(string $fieldName, string $value): void
    {
        $this->client->executeScript(
            'var f = document.getElementsByName(arguments[0])[0];'
            . 'f.value = "";'
            . 'f.value = arguments[1];'
            . 'f.dispatchEvent(new Event("input", {bubbles: true}));'
            . 'f.dispatchEvent(new Event("change", {bubbles: true}));',
            [$fieldName, $value]
        );
    }
}
