<?php

/**
 * KkEncounterFormNavbarUrlTest — E2E test for #10844 fix.
 *
 * Verifies that the encounter form navbar renders form links with pid and
 * encounter URL parameters. Before the fix, these URLs omitted the params,
 * causing load_form.php to rely on the session, which could be stale.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Encounter\EncounterOpenTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Patient\PatientTestData;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class KkEncounterFormNavbarUrlTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use EncounterOpenTrait;

    /** @phpstan-ignore property.unused (used by BaseTrait::switchToIFrame) */
    private mixed $crawler;

    /**
     * Verify that form navbar dropdown links include pid and encounter URL params.
     *
     * Regression test for #10844: without these params, forms relied on the
     * session encounter which could be 0, causing new.php to fall back to
     * date("Ymd") as a bogus encounter ID and triggering a 404 on redirect.
     */
    #[Depends('testEncounterOpen')]
    #[Depends('testLoginAuthorized')]
    #[Depends('testPatientOpen')]
    #[Test]
    public function testFormNavbarUrlsContainEncounterAndPid(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->patientOpenIfExist(
                PatientTestData::FNAME,
                PatientTestData::LNAME,
                PatientTestData::DOB,
                PatientTestData::SEX,
                false
            );
            $this->encounterOpenIfExist(
                PatientTestData::FNAME,
                PatientTestData::LNAME,
                PatientTestData::DOB,
                PatientTestData::SEX,
                false,
                false
            );

            // Navigate into the encounter forms iframe where the navbar lives
            $this->client->switchTo()->defaultContent();
            $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_IFRAME);
            $this->client->waitFor(XpathsConstants::ENCOUNTER_FORMS_IFRAME);
            $this->switchToIFrame(XpathsConstants::ENCOUNTER_FORMS_IFRAME);

            // Wait for the navbar to render
            $this->client->waitFor('//span[@id="navbarEncounterTitle"]');

            // Use JavaScript to extract all onclick attributes from navbar dropdown items.
            // Each form link has onclick="openNewForm('...load_form.php?formname=X&pid=Y&encounter=Z', ...)"
            /** @var list<string> $onclickValues */
            $onclickValues = (array) $this->client->executeScript(<<<'JS'
                var items = document.querySelectorAll('.dropdown-menu .dropdown-item');
                var results = [];
                items.forEach(function(item) {
                    var onclick = item.getAttribute('onclick');
                    if (onclick && onclick.indexOf('load_form.php') !== -1) {
                        results.push(onclick);
                    }
                });
                return results;
            JS);

            // There should be at least one form link in the navbar
            $this->assertNotEmpty(
                $onclickValues,
                'Expected at least one form link with load_form.php in the navbar'
            );

            // Every load_form.php URL must include pid= and encounter= params
            foreach ($onclickValues as $onclick) {
                $onclickStr = (string) $onclick;
                $this->assertStringContainsString(
                    'pid=',
                    $onclickStr,
                    "Form link missing pid param: {$onclickStr}"
                );
                $this->assertStringContainsString(
                    'encounter=',
                    $onclickStr,
                    "Form link missing encounter param: {$onclickStr}"
                );
                // The encounter value must not be 0 (the stale-session scenario)
                $this->assertDoesNotMatchRegularExpression(
                    '/encounter=0[^0-9]/',
                    $onclickStr,
                    "Form link has encounter=0 (stale session bug): {$onclickStr}"
                );
            }
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }
}
