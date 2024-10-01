<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Pages\LoginPage;
use OpenEMR\Tests\E2e\Pages\MainPage;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class CheckMainMenuLinksTest extends PantherTestCase
{
    /**
     * The base url used for e2e (end to end) browser testing.
     */
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    /**
     * @dataProvider menuLinkProvider
     */
    public function testCheckMenuLink(string $menuLink, string $expectedTabTitle): void
    {
        $openEmrPage = $this->e2eBaseUrl;
        $client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $client->manage()->window()->maximize();
        try {
            // login
            $crawler = $client->request('GET', '/interface/login/login.php?site=default');
            $form = $crawler->filter('#login_form')->form();
            $form['authUser'] = 'admin';
            $form['clearPass'] = 'pass';
            $crawler = $client->submit($form);
            // check if the menu cog is showing. if so, then click it.
            if ($crawler->filterXPath('//div[@id="mainBox"]/nav/button[@data-target="#mainMenu"]')->isDisplayed()) {
                $crawler->filterXPath('//div[@id="mainBox"]/nav/button[@data-target="#mainMenu"]')->click();
            }
            // got to and click the menu link
            $menuLinkSequenceArray = explode('||', $menuLink);
            foreach ($menuLinkSequenceArray as $menuLinkItem) {
                $client->waitFor('//div[@id="mainMenu"]//div[text()="' . $menuLinkItem . '"]');
                $crawler = $client->refreshCrawler();
                $crawler->filterXPath('//div[@id="mainMenu"]//div[text()="' . $menuLinkItem . '"]')->click();
            }
            // wait for the tab title to be shown
            $client->waitForElementToContain("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]", $expectedTabTitle);
            // Perform the final assertion
            $this->assertSame($expectedTabTitle, $crawler->filterXPath("//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]")->text());
        } catch (Exception $e) {
            // Close client
            $client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $client->quit();
    }

    public static function menuLinkProvider()
    {
        return [
            'Calendar menu link' => ['Calendar', 'Calendar'],
            'Finder menu link' => ['Finder', 'Patient Finder'],
            'Flow menu link' => ['Flow', 'Flow Board'],
            'Recalls menu link' => ['Recalls', 'Recall Board'],
            'Messages menu link' => ['Messages', 'Message Center'],
            'New/Search menu link' => ['Patient||New/Search', 'Search or Add Patient'],
            'Billing Manager menu link' => ['Fees||Billing Manager', 'Billing Manager'],
            'Batch Payments menu link' => ['Fees||Batch Payments', 'New Payment'],
            'Posting Payments menu link' => ['Fees||Posting Payments', 'EOB Posting - Search'],
            'EDI History menu link' => ['Fees||EDI History', 'EDI History'],
            'Manage Modules menu link' => ['Modules||Manage Modules', 'Manage Modules'],
            'Carecoordination menu link' => ['Modules||Carecoordination', 'Care Coordination'],
            'Procedures Providers menu link' => ['Procedures||Providers', 'Procedure Providers'],
            'Configuration menu link' => ['Procedures||Configuration', 'Configure Orders and Results'],
            'Load Compendium menu link' => ['Procedures||Load Compendium', 'Load Compendium'],
            'Batch Results menu link' => ['Procedures||Batch Results', 'Procedure Results'],
            'Electronic Reports menu link' => ['Procedures||Electronic Reports', 'Procedure Orders and Reports'],
            'Lab Documents menu link' => ['Procedures||Lab Documents', 'Lab Documents'],
            'Config menu link' => ['Admin||Config', 'Configuration'],
            'Facilities menu link' => ['Admin||Clinic||Facilities', 'Facilities'],
            'Admin Calendar menu link' => ['Admin||Clinic||Calendar', 'Calendar'],
            'Import Holidays menu link' => ['Admin||Clinic||Import Holidays', 'Holidays management'],
            'Patient Reminders menu link' => ['Admin||Patients||Patient Reminders', 'Patient Reminders'],
            'Merge Patients menu link' => ['Admin||Patients||Merge Patients', 'Merge Patients'],
            'Manage Duplicates menu link' => ['Admin||Patients||Manage Duplicates', 'Duplicate Patient Management'],
            'Practice Settings menu link' => ['Admin||Practice||Practice Settings', 'Practice Settings'],
            'Rules menu link' => ['Admin||Practice||Rules', 'Plans Configuration God'],
            'Alerts menu link' => ['Admin||Practice||Alerts', 'Clinical Decision Rules Alert Manager'],
            'Codes menu link' => ['Admin||Coding||Codes', 'Codes'],
            'Native Data Loads menu link' => ['Admin||Coding||Native Data Loads', 'Install Code Set'],
            'External Data Loads menu link' => ['Admin||Coding||External Data Loads', 'External Data Loads'],
            'Forms Administration menu link' => ['Admin||Forms||Forms Administration', 'Forms Administration'],
            'Layouts menu link' => ['Admin||Forms||Layouts', 'Layout Editor'],
            'Lists menu link' => ['Admin||Forms||Lists', 'List Editor'],
            'Document Templates menu link' => ['Admin||Documents||Document Templates', 'Document Template Management'],
            'Backup menu link' => ['Admin||System||Backup', 'Backup'],
            'Files menu link' => ['Admin||System||Files', 'File management'],
            'Language menu link' => ['Admin||System||Language', 'Multi Language Tool'],
            'Certificates menu link' => ['Admin||System||Certificates', 'SSL Certificate Administration'],
            'Logs menu link' => ['Admin||System||Logs', 'Logs Viewer'],
            'Audit Log Tamper menu link' => ['Admin||System||Audit Log Tamper', 'Audit Log Tamper Report'],
            'Diagnostics menu link' => ['Admin||System||Diagnostics', 'Diagnostics'],
            'API Clients menu link' => ['Admin||System||API Clients', 'Client Registrations'],
            'Users menu link' => ['Admin||Users', 'User / Groups'],
            'Address Book menu link' => ['Admin||Address Book', 'Address Book'],
            'ACL menu link' => ['Admin||ACL', 'Access Control List Administration'],
        ];
    }
}
