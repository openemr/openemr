<?php

/**
 * What resend_referral.php refuses (#15137): a request without the page's token, and a referral
 * that isn't in the open chart. Setup is HTTP-only (see tests/Tests/WebUi/README.md), and these
 * cases need no referral: a refused request never reaches one. ReferralTaskFhirApiTest covers
 * what a re-send does.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\WebUi;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReferralResendEndpointTest extends TestCase
{
    private const PATH = '/interface/patient_file/transaction/resend_referral.php';

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
            $message = 'The re-send endpoint test needs a real webserver (Apache/nginx)';
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
    public function aLinkCanNotReSend(): void
    {
        $response = $this->http->get($this->baseUrl . self::PATH . '?transid=1&task=x&csrf_token_form=' . $this->token);

        $this->assertSame(403, $response->getStatusCode());
    }

    #[Test]
    public function aPostWithoutTheTokenIsRefused(): void
    {
        $response = $this->http->post($this->baseUrl . self::PATH, ['form_params' => ['csrf_token_form' => 'wrong', 'transid' => '1', 'task' => 'x']]);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Authentication Error', (string)$response->getBody());
    }

    /**
     * No chart is open in this session, so no referral is in it, whatever the id.
     */
    #[Test]
    public function aReferralOutsideTheOpenChartIsRefused(): void
    {
        $response = $this->http->post($this->baseUrl . self::PATH, ['form_params' => ['csrf_token_form' => $this->token, 'transid' => '1', 'task' => 'x']]);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringNotContainsString('Authentication Error', (string)$response->getBody());
    }
}
