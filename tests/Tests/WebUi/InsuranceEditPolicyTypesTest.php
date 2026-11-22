<?php

/**
 * The insurance editor's Secondary Medicare Type list offers the 837P policy types. It came up empty
 * after #13837 dropped the $policy_types global the page read. Setup is HTTP-only (see
 * tests/Tests/WebUi/README.md): the test makes its own patient through the new-patient form and
 * doesn't remove it, because deleting a patient over HTTP needs the allow_pat_delete global.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\WebUi;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Billing\InsurancePolicyTypes;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

final class InsuranceEditPolicyTypesTest extends TestCase
{
    private string $baseUrl = '';
    private Client $http;
    private string $token = '';

    /**
     * Signs in as staff and keeps the session's token, as the tabbed page has it.
     */
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
            $message = 'The insurance editor test needs a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        $this->http->get($this->baseUrl . '/interface/login/login.php?site=default');
        $main = $this->http->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => [
                'authUser' => 'admin',
                'clearPass' => 'pass',
                'languageChoice' => '1',
                'new_login_session_management' => '1',
            ],
        ]);
        $this->assertSame(1, preg_match('/var csrf_token_js = "([0-9a-f]+)"/', (string)$main->getBody(), $match), 'signed in');
        $this->token = $match[1];
    }

    #[Test]
    public function theSecondaryMedicareTypeListOffersEveryPolicyType(): void
    {
        $url = $this->baseUrl . '/interface/patient_file/summary/insurance_edit.php?' . http_build_query([
            'set_pid' => $this->createPatient(),
            'csrf_token_form' => $this->token,
        ]);
        $response = $this->http->get($url);
        $this->assertSame(200, $response->getStatusCode(), 'the insurance editor opens');

        $options = (new Crawler((string)$response->getBody()))
            ->filterXPath('//select[@name="form_policy_type"]/option')
            ->each(static fn(Crawler $option): string => (string)$option->attr('value'));

        $this->assertSame(InsurancePolicyTypes::POLICY_TYPES, $options, 'the list offers N/A and every 837P Medicare secondary payer type');
    }

    private function createPatient(): int
    {
        $form = (string)$this->http->get($this->baseUrl . '/interface/new/new_comprehensive.php')->getBody();
        $this->assertSame(1, preg_match('/name="csrf_token_form" value="([^"]+)"/', $form, $token), 'the new-patient form prints a token');
        $saved = (string)$this->http->post($this->baseUrl . '/interface/new/new_comprehensive_save.php', [
            'allow_redirects' => false,
            'form_params' => [
                'csrf_token_form' => $token[1],
                'form_fname' => 'InsuranceEdit',
                'form_lname' => 'Test' . bin2hex(random_bytes(3)),
                'form_DOB' => '1950-01-02',
                'form_sex' => 'Male',
            ],
        ])->getBody();
        $this->assertSame(1, preg_match('/set_pid=(\d+)/', $saved, $pid), 'the new-patient save names the new pid');
        return (int)$pid[1];
    }
}
