<?php

/**
 * Portal custom report renders only the session patient's encounter forms.
 *
 * `portal/report/portal_custom_report.php` reads `<formdir>_<form_id>` keys
 * with an encounter id value and renders that form plus the encounter
 * header. These tests pin that a key naming another patient's form and
 * encounter renders nothing, while the logged-in patient's own form still
 * renders.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PortalCustomReportFormScopeTest extends TestCase
{
    private const OWN_DATE = '1986-04-15';
    private const OTHER_DATE = '1987-06-23';

    private string $baseUrl = '';
    private Client $http;
    private PortalPatientFixtureManager $fixtures;
    /** @var list<int> */
    private array $encounterRowIds = [];
    /** @var list<int> */
    private array $formRowIds = [];

    protected function setUp(): void
    {
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => true,
            'timeout' => 30,
        ]);
        if ($this->http->get($this->baseUrl . '/')->getHeaderLine('Server') === '') {
            $message = 'portal custom report test requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        $this->fixtures = new PortalPatientFixtureManager();
    }

    protected function tearDown(): void
    {
        foreach ($this->formRowIds as $id) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `forms` WHERE `id` = ?", [$id]);
        }
        foreach ($this->encounterRowIds as $id) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `form_encounter` WHERE `id` = ?", [$id]);
        }
        $this->formRowIds = [];
        $this->encounterRowIds = [];
        if (isset($this->fixtures)) {
            $this->fixtures->removePortalPatientFixtures();
        }
    }

    #[Test]
    public function testOtherPatientsEncounterFormIsNotRendered(): void
    {
        $own = $this->installPatient('own');
        $other = $this->installPatient('other');
        $otherForm = $this->createEncounterWithForm($other['pid'], self::OTHER_DATE);

        $this->portalLogin($own);
        $body = $this->fetchReport($otherForm);

        $this->assertStringNotContainsString(
            "class='text encounter'",
            $body,
            'another patient\'s encounter form must not render in the portal custom report'
        );
        foreach ($this->dateVariants(self::OTHER_DATE) as $variant) {
            $this->assertStringNotContainsString(
                $variant,
                $body,
                'another patient\'s encounter date must not appear in the portal custom report'
            );
        }
    }

    #[Test]
    public function testOwnEncounterFormIsRendered(): void
    {
        $own = $this->installPatient('own');
        $ownForm = $this->createEncounterWithForm($own['pid'], self::OWN_DATE);

        $this->portalLogin($own);
        $body = $this->fetchReport($ownForm);

        $this->assertStringContainsString(
            "class='text encounter'",
            $body,
            'the logged-in patient\'s own encounter form must render'
        );
        $found = false;
        foreach ($this->dateVariants(self::OWN_DATE) as $variant) {
            $found = $found || str_contains($body, $variant);
        }
        $this->assertTrue($found, 'the logged-in patient\'s own encounter date must render');
    }

    /**
     * @return array{pid: int, username: string, password: string, email: string}
     */
    private function installPatient(string $label): array
    {
        $suffix = bin2hex(random_bytes(4));
        $username = "pcr-{$label}-{$suffix}";
        $password = 'PortalReport-' . $suffix . '!';
        $email = "{$username}@test.example";
        $row = $this->fixtures->installPortalPatient($username, $password, $email);
        return ['pid' => $row['pid'], 'username' => $username, 'password' => $password, 'email' => $email];
    }

    /**
     * @return array{form_id: int, encounter: int}
     */
    private function createEncounterWithForm(int $pid, string $date): array
    {
        $encounter = random_int(900000000, 999999999);
        $encounterRowId = QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `encounter`, `pid`, `reason`) VALUES (?, ?, ?, ?)",
            [$date . ' 09:00:00', $encounter, $pid, 'portal report scope test']
        );
        $this->encounterRowIds[] = $encounterRowId;
        $formRowId = QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
                . " VALUES (?, ?, 'New Patient Encounter', ?, ?, 'admin', 'Default', 1, 0, 'newpatient')",
            [$date . ' 09:00:00', $encounter, $encounterRowId, $pid]
        );
        $this->formRowIds[] = $formRowId;
        return ['form_id' => $encounterRowId, 'encounter' => $encounter];
    }

    /**
     * @param array{pid: int, username: string, password: string, email: string} $patient
     */
    private function portalLogin(array $patient): void
    {
        $this->http->get($this->baseUrl . '/portal/index.php?site=default');
        $response = $this->http->post($this->baseUrl . '/portal/get_patient_info.php', [
            'form_params' => [
                'uname' => $patient['username'],
                'pass' => $patient['password'],
                'passaddon' => $patient['email'],
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode(), 'portal login request failed');
        $home = (string) $this->http->get($this->baseUrl . '/portal/home.php')->getBody();
        $this->assertStringNotContainsString(
            'get_patient_info.php',
            $home,
            'portal login did not establish a session'
        );
    }

    /**
     * @param array{form_id: int, encounter: int} $form
     */
    private function fetchReport(array $form): string
    {
        $url = $this->baseUrl . '/portal/report/portal_custom_report.php?newpatient_'
            . $form['form_id'] . '=' . $form['encounter'];
        $response = $this->http->get($url);
        $this->assertSame(200, $response->getStatusCode(), 'portal custom report request failed');
        return (string) $response->getBody();
    }

    /**
     * The encounter header uses the configured short date format; cover the
     * three formats OpenEMR offers.
     *
     * @return list<string>
     */
    private function dateVariants(string $ymd): array
    {
        [$y, $m, $d] = explode('-', $ymd);
        return ["{$y}-{$m}-{$d}", "{$m}/{$d}/{$y}", "{$d}/{$m}/{$y}"];
    }
}
