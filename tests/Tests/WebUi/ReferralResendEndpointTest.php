<?php

/**
 * What the referral page's own endpoints refuse (#15137), resend_referral.php and
 * confirm_referral_reply.php alike: a request without the page's token, and a referral that isn't in
 * the open chart. Setup is HTTP-only (see tests/Tests/WebUi/README.md): the test makes its own
 * patients and referral through the forms, and doesn't remove them, because deleting a patient over
 * HTTP needs the allow_pat_delete global. ReferralTaskFhirApiTest covers what a re-send does, and
 * ReferralReplyFhirApiTest what confirming a Direct reply does.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class ReferralResendEndpointTest extends TestCase
{
    private const RESEND = '/interface/patient_file/transaction/resend_referral.php';
    private const CONFIRM_REPLY = '/interface/patient_file/transaction/confirm_referral_reply.php';

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

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function endpointProvider(): array
    {
        return ['re-send' => [self::RESEND], 'confirm a Direct reply' => [self::CONFIRM_REPLY]];
    }

    /**
     * A link changes nothing.
     */
    #[Test]
    #[DataProvider('endpointProvider')]
    public function aLinkChangesNothing(string $path): void
    {
        $response = $this->http->get($this->baseUrl . $path . '?transid=1&task=x&reply=1&csrf_token_form=' . $this->token);

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * A post without the token is refused.
     */
    #[Test]
    #[DataProvider('endpointProvider')]
    public function aPostWithoutTheTokenIsRefused(string $path): void
    {
        $response = $this->http->post($this->baseUrl . $path, ['form_params' => ['csrf_token_form' => 'wrong', 'transid' => '1', 'task' => 'x']]);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Authentication Error', (string)$response->getBody());
    }

    /**
     * No chart is open in this session, so no referral is in it, whatever the id.
     */
    #[Test]
    #[DataProvider('endpointProvider')]
    public function aReferralOutsideTheOpenChartIsRefused(string $path): void
    {
        $response = $this->http->post($this->baseUrl . $path, ['form_params' => ['csrf_token_form' => $this->token, 'transid' => '1', 'task' => 'x']]);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringNotContainsString('Authentication Error', (string)$response->getBody());
    }

    /**
     * One patient's referral can't be re-sent from another patient's chart, and the same request
     * from its own chart gets through, so it's the chart that was refused.
     */
    #[Test]
    #[DataProvider('endpointProvider')]
    public function anotherPatientsReferralIsRefusedAndItsOwnChartIsNot(string $path): void
    {
        $owner = $this->createPatient();
        $other = $this->createPatient();
        $this->openChart($owner);
        $referral = $this->saveReferral();
        $this->openChart($other);
        $request = [
            'allow_redirects' => false,
            'form_params' => ['csrf_token_form' => $this->token, 'transid' => (string)$referral, 'task' => 'not-a-task', 'reply' => '1'],
        ];

        $this->assertSame(403, $this->http->post($this->baseUrl . $path, $request)->getStatusCode(), "another patient's chart");

        $this->openChart($owner);
        $response = $this->http->post($this->baseUrl . $path, $request);
        $this->assertSame(302, $response->getStatusCode(), 'its own chart');
        $this->assertStringContainsString('add_transaction.php?transid=' . $referral . '&', $response->getHeaderLine('Location'));
    }

    /**
     * A field sent as an array is no referral, or no Task to follow on from: the first is refused
     * like any other id, the second makes nothing, and neither is a server error.
     */
    #[Test]
    #[DataProvider('endpointProvider')]
    public function aFieldSentAsAnArrayIsNotAServerError(string $path): void
    {
        $patient = $this->createPatient();
        $this->openChart($patient);
        $referral = $this->saveReferral();
        $post = fn(array $fields): ResponseInterface => $this->http->post($this->baseUrl . $path, [
            'allow_redirects' => false,
            'form_params' => ['csrf_token_form' => $this->token] + $fields,
        ]);

        $this->assertSame(403, $post(['transid' => [(string)$referral], 'task' => 'not-a-task'])->getStatusCode(), 'an array for the referral');
        $this->assertSame(302, $post(['transid' => (string)$referral, 'task' => ['not-a-task']])->getStatusCode(), 'an array for the Task');
        $this->assertSame(302, $post(['transid' => (string)$referral, 'task' => 'not-a-task', 'reply' => ['1']])->getStatusCode(), 'an array for the reply');
    }

    private function createPatient(): int
    {
        $form = (string)$this->http->get($this->baseUrl . '/interface/new/new_comprehensive.php')->getBody();
        $this->assertSame(1, preg_match('/name="csrf_token_form" value="([^"]+)"/', $form, $token), 'the new-patient form prints a token');
        $saved = (string)$this->http->post($this->baseUrl . '/interface/new/new_comprehensive_save.php', [
            'allow_redirects' => false,
            'form_params' => [
                'csrf_token_form' => $token[1],
                'form_fname' => 'ReferralResend',
                'form_lname' => 'Test' . bin2hex(random_bytes(3)),
                'form_DOB' => '1980-01-02',
                'form_sex' => 'Female',
            ],
        ])->getBody();
        $this->assertSame(1, preg_match('/set_pid=(\d+)/', $saved, $pid), 'the new-patient save names the new pid');
        return (int)$pid[1];
    }

    private function openChart(int $pid): void
    {
        $url = $this->baseUrl . '/interface/patient_file/summary/demographics.php?set_pid=' . $pid . '&csrf_token_form=' . $this->token;
        $this->assertSame(200, $this->http->get($url)->getStatusCode(), 'the chart opens');
    }

    /**
     * Saves a referral in the open chart through the Transactions form.
     *
     * @return int its transaction id, as the patient's Transactions list links to it
     */
    private function saveReferral(): int
    {
        $this->http->post($this->baseUrl . '/interface/patient_file/transaction/add_transaction.php', [
            'form_params' => ['csrf_token_form' => $this->token, 'mode' => 'add', 'title' => 'LBTref', 'form_body' => 'A referral'],
        ]);
        $list = (string)$this->http->get($this->baseUrl . '/interface/patient_file/transaction/transactions.php')->getBody();
        $this->assertSame(1, preg_match('/add_transaction\.php\?transid=(\d+)/', $list, $id), 'the list links to the referral');
        return (int)$id[1];
    }
}
