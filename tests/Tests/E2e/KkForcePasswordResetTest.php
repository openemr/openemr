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
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class KkForcePasswordResetTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private Crawler $crawler;

    private const USERNAME = 'forcepwtest';
    private const PASSWORD = 'Test12te$t';
    private const NEW_PASSWORD = 'N3wTest12te$t';
    private const FIRSTNAME = 'Force';
    private const LASTNAME = 'Reset';

    protected function setUp(): void
    {
        parent::setUp();
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
        QueryUtils::sqlStatementThrowException('DELETE FROM `users_secure` WHERE `username` = ?', [self::USERNAME]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `groups` WHERE `user` = ?', [self::USERNAME]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `username` = ?', [self::USERNAME]);
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
            'INSERT INTO `users` (`username`, `fname`, `lname`, `active`, `authorized`) VALUES (?, ?, ?, 1, 1)',
            [self::USERNAME, self::FIRSTNAME, self::LASTNAME]
        );

        $hash = password_hash(self::PASSWORD, PASSWORD_DEFAULT);
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO `users_secure` (`id`, `username`, `password`, `force_new_password`, `last_update_password`)
            VALUES (?, ?, ?, 1, NOW())
            SQL,
            [$userId, self::USERNAME, $hash]
        );

        // User must belong to a group for login to succeed
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO `groups` (`name`, `user`) VALUES ('Default', ?)
            SQL,
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
            $this->assertActiveTab('Change Password');

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
            $this->client->wait(15)->until(static function (WebDriver $driver): bool {
                $msg = $driver->findElement(WebDriverBy::id('display_msg'));
                return str_contains($msg->getText(), 'Password change successful');
            });

            // Verify the force flag is cleared in the database. Both checks are
            // phrased as row-existence questions so the driver's column type
            // (int vs numeric string) never enters the assertion.
            $secureRow = QueryUtils::querySingleRow(
                'SELECT `id` FROM `users_secure` WHERE `username` = ?',
                [self::USERNAME]
            );
            $this->assertIsArray($secureRow, 'the users_secure row for the test user disappeared');

            $stillForced = QueryUtils::querySingleRow(
                'SELECT `id` FROM `users_secure` WHERE `username` = ? AND `force_new_password` <> 0',
                [self::USERNAME]
            );
            $this->assertFalse($stillForced, 'force_new_password was not cleared by the password change');

            // Log out and log back in with the new password
            $this->logOut();
            $this->login(self::USERNAME, self::NEW_PASSWORD);

            // Verify normal login — the forced password change tab is gone.
            // Read the tab URLs from the view model rather than the rendered
            // tab text: the text is asynchronously replaced by the iframe's
            // own title, so asserting on it right after login races the load
            // and would pass even if the tab were still there.
            $this->client->switchTo()->defaultContent();
            $tabUrls = $this->client->executeScript(
                'return app_view_model.application_data.tabs.tabsList().map(function (t) { return t.url(); });'
            );
            $this->assertIsArray($tabUrls, 'unable to read the tab list from the view model');
            $this->assertNotSame([], $tabUrls, 'the tab list is empty, so the check below would be vacuous');
            $forcedTabs = array_filter(
                $tabUrls,
                static fn(mixed $url): bool => is_string($url) && str_contains($url, 'user_info.php')
            );
            $this->assertSame(
                [],
                array_values($forcedTabs),
                'the forced password change tab is still present after the password was changed'
            );

            $this->logOut();
        } finally {
            // finally rather than the catch/rethrow/quit shape used elsewhere
            // in this suite: the browser is a resource acquired by base(), and
            // catching \Throwable purely to re-raise it trips the project's
            // ForbiddenCatchType rule for no benefit.
            $this->client->quit();
        }
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
            <<<'JS'
            var f = document.getElementsByName(arguments[0])[0];
            f.value = "";
            f.value = arguments[1];
            f.dispatchEvent(new Event("input", {bubbles: true}));
            f.dispatchEvent(new Event("change", {bubbles: true}));
            JS,
            [$fieldName, $value]
        );
    }
}
