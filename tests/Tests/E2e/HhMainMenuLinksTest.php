<?php

/**
 * HhMainMenuLinksTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Main menu links test with shared browser session.
 *
 * This test class uses a single browser session for all test cases,
 * logging in once in setUpBeforeClass() and reusing the session for
 * all menu link tests. This significantly reduces test execution time
 * compared to creating a fresh session for each test case.
 */
class HhMainMenuLinksTest extends PantherTestCase
{
    private static ?Client $sharedClient = null;

    private $crawler;

    public static function setUpBeforeClass(): void
    {
        self::$sharedClient = self::createSharedClient();
        self::performSharedLogin();
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$sharedClient !== null) {
            self::$sharedClient->quit();
            self::$sharedClient = null;
        }
    }

    private static function createSharedClient(): Client
    {
        $useGrid = getenv("SELENIUM_USE_GRID", true) ?? "false";

        if ($useGrid === "true") {
            $seleniumHost = getenv("SELENIUM_HOST", true) ?? "selenium";
            $e2eBaseUrl = getenv("SELENIUM_BASE_URL", true) ?: "http://openemr";
            $forceHeadless = getenv("SELENIUM_FORCE_HEADLESS", true) ?? "false";
            $implicitWait = (int)(getenv("SELENIUM_IMPLICIT_WAIT") ?: 0);
            $pageLoadTimeout = (int)(getenv("SELENIUM_PAGE_LOAD_TIMEOUT") ?: 60);

            $capabilities = DesiredCapabilities::chrome();

            $chromeArgs = [
                '--window-size=1920,1080',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ];

            if ($forceHeadless === "true") {
                $chromeArgs[] = '--headless';
            }

            $capabilities->setCapability('goog:chromeOptions', [
                'args' => $chromeArgs
            ]);

            $capabilities->setCapability('unhandledPromptBehavior', 'accept');
            $capabilities->setCapability('pageLoadStrategy', 'normal');

            $seleniumUrl = "http://$seleniumHost:4444/wd/hub";
            $client = Client::createSeleniumClient($seleniumUrl, $capabilities, $e2eBaseUrl);

            $client->manage()->timeouts()->implicitlyWait($implicitWait);
            $client->manage()->timeouts()->pageLoadTimeout($pageLoadTimeout);
        } else {
            $client = static::createPantherClient(['external_base_uri' => "http://localhost"]);
            $client->manage()->window()->maximize();
        }

        return $client;
    }

    private static function performSharedLogin(): void
    {
        $crawler = self::$sharedClient->request('GET', '/interface/login/login.php?site=default&testing_mode=1');
        $form = $crawler->filter('#login_form')->form();
        $form['authUser'] = LoginTestData::username;
        $form['clearPass'] = LoginTestData::password;
        self::$sharedClient->submit($form);

        // Wait for app to be ready (Knockout.js bindings applied)
        self::$sharedClient->wait(30)->until(fn($driver) => $driver->executeScript(
            'return document.getElementById("mainMenu")?.children.length > 0'
        ));

        fwrite(STDERR, "[E2E] Shared session login complete\n");
    }

    /**
     * Initial login test - verifies shared session is working.
     * Other tests depend on this to ensure proper test ordering.
     */
    public function testLoginAuthorized(): void
    {
        // Verify the shared session is logged in
        $title = self::$sharedClient->getTitle();
        $this->assertSame('OpenEMR', $title, 'Shared session login FAILED');
    }

    #[DataProvider('menuLinkProvider')]
    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testMainMenuLink(string $menuLink, string $expectedTabTitle, ?string $loading = ''): void
    {
        if ($expectedTabTitle == "Care Coordination" && !empty(getenv('UNABLE_SUPPORT_OPENEMR_NODEJS', true) ?? '')) {
            $this->markTestSkipped('Test skipped because this environment does not support high enough nodejs version.');
        }

        if (empty($loading)) {
            $loading = "Loading";
        }

        // Use the shared client - no login needed
        $this->goToMainMenuLink($menuLink);
        $this->assertActiveTab($expectedTabTitle, $loading);
    }

    private function assertActiveTab(string $text, string $loading = "Loading", bool $looseTabTitle = false): void
    {
        $client = self::$sharedClient;

        $client->waitFor(XpathsConstants::ACTIVE_TAB);

        foreach (explode('||', $loading) as $loadingText) {
            $client->waitForElementToNotContain(XpathsConstants::ACTIVE_TAB, $loadingText);
        }

        $this->crawler = $client->refreshCrawler();
        if ($looseTabTitle) {
            $this->assertTrue(str_contains($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), $text), "[$text] tab load FAILED");
        } else {
            $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "[$text] tab load FAILED");
        }
    }

    private function goToMainMenuLink(string $menuLink): void
    {
        $client = self::$sharedClient;

        // Ensure on main page (not in an iframe)
        $client->switchTo()->defaultContent();

        $menuLinkSequenceArray = explode('||', $menuLink);
        $counter = 0;
        foreach ($menuLinkSequenceArray as $menuLinkItem) {
            if ($counter == 0) {
                if (count($menuLinkSequenceArray) > 1) {
                    $xpath = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkItem . '"]';
                } else {
                    $xpath = '//div[@id="mainMenu"]/div/div/div[text()="' . $menuLinkItem . '"]';
                }
            } elseif ($counter == 1) {
                if (count($menuLinkSequenceArray) == 2) {
                    $xpath = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div[text()="' . $menuLinkItem . '"]';
                } else {
                    $xpath = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div/div[text()="' . $menuLinkItem . '"]';
                }
            } else {
                $xpath = '//div[@id="mainMenu"]/div/div/div/div[text()="' . $menuLinkSequenceArray[0] . '"]/../ul/li/div/div[text()="' . $menuLinkSequenceArray[1] . '"]/../ul/li/div[text()="' . $menuLinkItem . '"]';
            }

            $element = $client->wait(30)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath($xpath)
                )
            );
            $element->click();
            $counter++;
        }
    }

    /** @codeCoverageIgnore Data providers run before coverage instrumentation starts. */
    public static function menuLinkProvider()
    {
        return [
            'Calendar menu link' => ['Calendar', 'Calendar'],
            'Finder menu link' => ['Finder', 'Patient Finder', 'undefined...||Loading'],
            'Flow menu link' => ['Flow', 'Flow Board', 'undefined...||Loading'],
            'Recalls menu link' => ['Recalls', 'Recall Board', 'undefined...||Loading'],
            'Messages menu link' => ['Messages', 'Message Center'],
            'Patient -> New/Search menu link' => ['Patient||New/Search', 'Search or Add Patient'],
            'Fees -> Billing Manager menu link' => ['Fees||Billing Manager', 'Billing Manager'],
            'Fees -> Batch Payments menu link' => ['Fees||Batch Payments', 'New Payment'],
            'Fees -> Posting Payments menu link' => ['Fees||Posting Payments', 'EOB Posting - Search'],
            'Fees -> EDI History menu link' => ['Fees||EDI History', 'EDI History'],
            'Modules -> Manage Modules menu link' => ['Modules||Manage Modules', 'Manage Modules'],
            'Modules -> Carecoordination menu link' => ['Modules||Carecoordination', 'Care Coordination'],
            'Procedures -> Procedures Providers menu link' => ['Procedures||Providers', 'Procedure Providers'],
            'Procedures -> Configuration menu link' => ['Procedures||Configuration', 'Configure Orders and Results'],
            'Procedures -> Load Compendium menu link' => ['Procedures||Load Compendium', 'Load Compendium'],
            'Procedures -> Batch Results menu link' => ['Procedures||Batch Results', 'Procedure Results'],
            'Procedures -> Electronic Reports menu link' => ['Procedures||Electronic Reports', 'Procedure Orders and Reports'],
            'Procedures -> Lab Documents menu link' => ['Procedures||Lab Documents', 'Lab Documents'],
            'Admin -> Config menu link' => ['Admin||Config', 'Configuration'],
            'Admin -> Clinic -> Facilities menu link' => ['Admin||Clinic||Facilities', 'Facilities'],
            'Admin -> Clinic -> Calendar menu link' => ['Admin||Clinic||Calendar', 'Calendar'],
            'Admin -> Clinic -> Import Holidays menu link' => ['Admin||Clinic||Import Holidays', 'Holidays management'],
            'Admin -> Patients -> Patient Reminders menu link' => ['Admin||Patients||Patient Reminders', 'Patient Reminders'],
            'Admin -> Patients -> Merge Patients menu link' => ['Admin||Patients||Merge Patients', 'Merge Patients'],
            'Admin -> Patients -> Manage Duplicates menu link' => ['Admin||Patients||Manage Duplicates', 'Duplicate Patient Management'],
            'Admin -> Practice -> Practice Settings menu link' => ['Admin||Practice||Practice Settings', 'Practice Settings'],
            'Admin -> Practice -> Rules menu link' => ['Admin||Practice||Rules', 'Plans Configuration Go'],
            'Admin -> Practice -> Alerts menu link' => ['Admin||Practice||Alerts', 'Clinical Decision Rules Alert Manager'],
            'Admin -> Coding -> Codes menu link' => ['Admin||Coding||Codes', 'Codes'],
            'Admin -> Coding -> Native Data Loads menu link' => ['Admin||Coding||Native Data Loads', 'Install Code Set'],
            'Admin -> Coding -> External Data Loads menu link' => ['Admin||Coding||External Data Loads', 'External Data Loads'],
            'Admin -> Forms -> Forms Administration menu link' => ['Admin||Forms||Forms Administration', 'Forms Administration'],
            'Admin -> Forms -> Layouts menu link' => ['Admin||Forms||Layouts', 'Layout Editor'],
            'Admin -> Forms -> Lists menu link' => ['Admin||Forms||Lists', 'List Editor'],
            'Admin -> Documents -> Document Templates menu link' => ['Admin||Documents||Document Templates', 'Document Template Management'],
            'Admin -> System -> Backup menu link' => ['Admin||System||Backup', 'Backup'],
            'Admin -> System -> Files menu link' => ['Admin||System||Files', 'File management'],
            'Admin -> System -> Language menu link' => ['Admin||System||Language', 'Multi Language Tool'],
            'Admin -> System -> Certificates menu link' => ['Admin||System||Certificates', 'SSL Certificate Administration'],
            'Admin -> System -> Logs menu link' => ['Admin||System||Logs', 'Logs Viewer'],
            'Admin -> System -> Audit Log Tamper menu link' => ['Admin||System||Audit Log Tamper', 'Audit Log Tamper Report'],
            'Admin -> System -> Diagnostics menu link' => ['Admin||System||Diagnostics', 'Diagnostics'],
            'Admin -> System -> API Clients menu link' => ['Admin||System||API Clients', 'Client Registrations'],
            'Admin -> Users menu link' => ['Admin||Users', 'User / Groups'],
            'Admin -> Address Book menu link' => ['Admin||Address Book', 'Address Book'],
            'Admin -> ACL menu link' => ['Admin||ACL', 'Access Control List Administration'],
            'Reports -> Clients -> List menu link' => ['Reports||Clients||List', 'Patient List'],
            'Reports -> Clients -> Rx menu link' => ['Reports||Clients||Rx', 'Prescriptions and Dispensations'],
            'Reports -> Clients -> Patient List Creation menu link' => ['Reports||Clients||Patient List Creation', 'Patient List Creation'],
            'Reports -> Clients -> Message List menu link' => ['Reports||Clients||Message List', 'Message List'],
            'Reports -> Clients -> Clinical menu link' => ['Reports||Clients||Clinical', 'Clinical Reports'],
            'Reports -> Clients -> Referrals menu link' => ['Reports||Clients||Referrals', 'Referrals'],
            'Reports -> Clients -> Immunization Registry menu link' => ['Reports||Clients||Immunization Registry', 'Immunization Registry'],
            'Reports -> Clinic -> Report Results menu link' => ['Reports||Clinic||Report Results', 'Report Results/History'],
            'Reports -> Clinic -> Standard Measures menu link' => ['Reports||Clinic||Standard Measures', 'Standard Measures'],
            'Reports -> Clinic -> Automated Measures (AMC) menu link' => ['Reports||Clinic||Automated Measures (AMC)', 'Automated Measure Calculations (AMC)'],
            'Reports -> Clinic -> 2026 Real World Testing Report menu link' => ['Reports||Clinic||2026 Real World Testing Report', '2026 Real World Testing Report'],
            'Reports -> Clinic -> Alerts Log menu link' => ['Reports||Clinic||Alerts Log', 'Alerts Log'],
            'Reports -> Visits -> Daily Report menu link' => ['Reports||Visits||Daily Report', 'Daily Summary Report'],
            'Reports -> Visits -> Patient Flow Board menu link' => ['Reports||Visits||Patient Flow Board', 'Patient Flow Board Report'],
            'Reports -> Visits -> Encounters menu link' => ['Reports||Visits||Encounters', 'Encounters Report'],
            'Reports -> Visits -> Appt-Enc menu link' => ['Reports||Visits||Appt-Enc', 'Appointments and Encounters'],
            'Reports -> Visits -> Superbill menu link' => ['Reports||Visits||Superbill', 'Reports - Superbill'],
            'Reports -> Visits -> Eligibility menu link' => ['Reports||Visits||Eligibility', 'Eligibility 270 Inquiry Batch'],
            'Reports -> Visits -> Eligibility Response menu link' => ['Reports||Visits||Eligibility Response', 'EDI-271 Response File Upload'],
            'Reports -> Visits -> Chart Activity menu link' => ['Reports||Visits||Chart Activity', 'Chart Location Activity'],
            'Reports -> Visits -> Charts Out menu link' => ['Reports||Visits||Charts Out', 'Charts Checked Out'],
            'Reports -> Visits -> Services menu link' => ['Reports||Visits||Services', 'Services by Category'],
            'Reports -> Visits -> Syndromic Surveillance menu link' => ['Reports||Visits||Syndromic Surveillance', 'Syndromic Surveillance - Non Reported Issues'],
            'Reports -> Financial -> Sales menu link' => ['Reports||Financial||Sales', 'Sales by Item'],
            'Reports -> Financial -> Cash Rec menu link' => ['Reports||Financial||Cash Rec', 'Cash Receipts by Provider'],
            'Reports -> Financial -> Front Rec menu link' => ['Reports||Financial||Front Rec', 'Front Office Receipts'],
            'Reports -> Financial -> Pmt Method menu link' => ['Reports||Financial||Pmt Method', 'Receipts Summary'],
            'Reports -> Financial -> Collections and Aging menu link' => ['Reports||Financial||Collections and Aging', 'Collections Report'],
            'Reports -> Financial -> Pat Ledger' => ['Reports||Financial||Pat Ledger', 'Patient Ledger by Date'],
            'Reports -> Financial -> Financial Summary by Service Code menu link' => ['Reports||Financial||Financial Summary by Service Code', 'Financial Summary by Service Code'],
            'Reports -> Financial -> Payment Processing menu link' => ['Reports||Financial||Payment Processing', 'Payment Processing'],
            'Reports -> Procedures -> Pending Res menu link' => ['Reports||Procedures||Pending Res', 'Pending Orders'],
            'Reports -> Procedures -> Statistics menu link' => ['Reports||Procedures||Statistics', 'Procedure Statistics Report'],
            'Reports -> Insurance -> Distribution menu link' => ['Reports||Insurance||Distribution', 'Patient Insurance Distribution'],
            'Reports -> Insurance -> Indigents menu link' => ['Reports||Insurance||Indigents', 'Indigent Patients Report'],
            'Reports -> Insurance -> Unique SP menu link' => ['Reports||Insurance||Unique SP', 'Unique Seen Patients'],
            'Reports -> Services -> Background Services menu link' => ['Reports||Services||Background Services', 'Background Services'],
            'Reports -> Services -> Direct Message Log menu link' => ['Reports||Services||Direct Message Log', 'Direct Message Log'],
            'Reports -> Services -> IP Tracker menu link' => ['Reports||Services||IP Tracker', 'IP Tracker'],
            'Miscellaneous -> Dicom Viewer menu link' => ['Miscellaneous||Dicom Viewer', 'Dicom Viewer'],
            'Miscellaneous -> Patient Education menu link' => ['Miscellaneous||Patient Education', 'Web Search - Patient Education Materials'],
            'Miscellaneous -> Authorizations menu link' => ['Miscellaneous||Authorizations', 'Authorizations (More)'],
            'Miscellaneous -> Chart Tracker menu link' => ['Miscellaneous||Chart Tracker', 'Chart Tracker'],
            'Miscellaneous -> Office Notes menu link' => ['Miscellaneous||Office Notes', 'Office Notes'],
            'Miscellaneous -> Batch Communication Tool menu link' => ['Miscellaneous||Batch Communication Tool', 'BatchCom'],
            'Miscellaneous -> New Documents menu link' => ['Miscellaneous||New Documents', 'New Documents']
        ];
    }
}
