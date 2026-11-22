<?php

/**
 * E2e test for the Add/Edit Transactions help page.
 *
 * The help page is the one staff open from a referral on the Transactions
 * screen. This loads it as a signed-in user and checks that it renders the
 * referral tracking guidance, so a broken page does not ship unnoticed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class TransactionsHelpPageTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const HELP_PAGE = '/Documentation/help_files/add_edit_transactions_dashboard_help.php';

    /**
     * The page renders each of its sections, the referral tracking ones included.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testHelpPageShowsReferralTrackingGuidance(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->crawler = $this->client->request('GET', self::HELP_PAGE);
            $pageText = $this->crawler->filterXPath('//body')->text();

            $this->assertStringContainsString('Add Edit Transactions Help', $pageText);
            $this->assertStringContainsString('CREATE A REFERRAL', $pageText);
            $this->assertStringContainsString('TRACK A REFERRAL', $pageText);
            $this->assertStringContainsString('Referral Tracking With Tasks', $pageText);
            $this->assertStringContainsString('Re-send appears when the recipient rejected, failed or cancelled the referral', $pageText);
            $this->assertStringContainsString('CREATE A COUNTER-REFERRAL', $pageText);
            $this->assertStringContainsString('Do not confirm a question or a refusal', $pageText);
            $this->assertStringContainsString('CREATE A SIMPLE TRANSACTION TYPE', $pageText);
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }
}
