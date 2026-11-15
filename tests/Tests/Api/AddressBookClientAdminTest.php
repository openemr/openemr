<?php

/**
 * The client admin screen's control for limiting an OAuth client to an address book entry (#15137),
 * through a signed-in session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AddressBookClientAdminTest extends TestCase
{
    private const SCREEN = '/interface/smart/admin-client.php';

    private string $baseUrl = '';
    private Client $http;
    private string $clientId = '';
    private string $token = '';
    private int $entryId = 0;
    private string $entryUuid = '';

    /**
     * A signed-in admin, a client with no limit, and an address book entry to limit it to.
     */
    protected function setUp(): void
    {
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client(['verify' => false, 'http_errors' => false, 'cookies' => new CookieJar(), 'allow_redirects' => true, 'timeout' => 30]);
        if ($this->http->get($this->baseUrl . '/')->getHeaderLine('Server') === '') {
            $message = 'The client admin test needs a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        $this->clientId = 'zq-limit-' . bin2hex(random_bytes(8));
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients (client_id, client_name, client_role, is_enabled, register_date, contacts, redirect_uri, scope, grant_types, is_confidential)
                VALUES (?, 'Zq Limit Client', 'users', 1, NOW(), 'zq@example.com', 'https://example.com/callback', 'openid fhirUser', 'authorization_code', 1)",
            [$this->clientId]
        );
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $this->entryUuid = UuidRegistry::uuidToString($uuid);
        $this->entryId = (int)QueryUtils::sqlInsert(
            "INSERT INTO users (uuid, username, active, authorized, abook_type, organization) VALUES (?, '', 1, 0, 'external_org', 'Zq Limit Org')",
            [$uuid]
        );

        $this->http->get($this->baseUrl . '/interface/login/login.php?site=default');
        $this->http->post($this->baseUrl . '/interface/main/main_screen.php?auth=login&site=default', [
            'form_params' => ['authUser' => 'admin', 'clearPass' => 'pass', 'languageChoice' => '1', 'new_login_session_management' => '1'],
        ]);
        $list = (string)$this->http->get($this->baseUrl . self::SCREEN)->getBody();
        $this->assertSame(1, preg_match('/csrf_token=([0-9a-f]+)/', $list, $match), 'the client list links with a token');
        $this->token = $match[1];
    }

    /**
     * Removes the client and the address book entry.
     */
    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM oauth_clients WHERE client_id = ?', [$this->clientId]);
        if ($this->entryId !== 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', ['users', UuidRegistry::uuidToBytes($this->entryUuid)]);
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->entryId]);
        }
    }

    #[Test]
    public function thePageOffersTheEntryAndTellsTheLimits(): void
    {
        $page = $this->editPage();

        $this->assertStringContainsString('value="' . $this->entryUuid . '"', $page);
        $this->assertStringContainsString('Zq Limit Org (Organization)', $page);
        $this->assertStringContainsString('It gets no other patient data.', $page);
    }

    #[Test]
    public function savingLimitsTheClientAndAnEmptyChoiceLiftsIt(): void
    {
        $this->assertSame(303, $this->save($this->entryUuid)->getStatusCode());
        $this->assertSame($this->entryUuid, $this->storedLimit());
        $this->assertStringContainsString('value="' . $this->entryUuid . '" selected', $this->editPage());

        $this->assertSame(303, $this->save('')->getStatusCode());
        $this->assertNull($this->storedLimit());
    }

    /**
     * An entry that drops off the list still shows as the client's limit, and saving it as shown keeps it.
     */
    #[Test]
    public function aLimitToAnEntryNoLongerListedStillShows(): void
    {
        $this->save($this->entryUuid);
        QueryUtils::sqlStatementThrowException('UPDATE users SET active = 0 WHERE id = ?', [$this->entryId]);

        $page = $this->editPage();
        $this->assertStringContainsString('value="' . $this->entryUuid . '" selected>', $page);
        $this->assertStringContainsString('Limited to an entry that is no longer listed', $page);

        $saved = $this->save($this->entryUuid);
        $this->assertStringContainsString(urlencode('The address book limit is unchanged for'), $saved->getHeaderLine('Location'));
        $this->assertSame($this->entryUuid, $this->storedLimit());
        $this->save(null);
        $this->assertSame($this->entryUuid, $this->storedLimit());
    }

    /**
     * Nothing but an address book entry that can own a Task: not any uuid, nor a user who logs in.
     */
    #[Test]
    public function anUnknownEntryIsNotSaved(): void
    {
        $response = $this->save('11111111-2222-4333-8444-555555555555');
        $this->assertSame(303, $response->getStatusCode(), (string)$response->getBody());
        $this->assertNull($this->storedLimit());
        $this->save(['not', 'a', 'uuid']);
        $this->assertNull($this->storedLimit());
    }

    /**
     * Only the empty choice lifts a limit: a post missing the field, or sending it malformed, doesn't.
     */
    #[Test]
    public function aMalformedPostLeavesALimitInPlace(): void
    {
        $this->save($this->entryUuid);

        $this->save(['not', 'a', 'uuid']);
        $this->assertSame($this->entryUuid, $this->storedLimit(), 'an array');

        $this->save(null);
        $this->assertSame($this->entryUuid, $this->storedLimit(), 'no field');
    }

    #[Test]
    /**
     * The limit takes a POST with the right token, so a link or a forged form can't set it.
     */
    public function aLinkOrAWrongTokenChangesNothing(): void
    {
        $url = $this->baseUrl . self::SCREEN . '?action=' . urlencode('edit/' . $this->clientId . '/address-book');

        $get = $this->http->get($url . '&csrf_token=' . $this->token . '&address_book_uuid=' . $this->entryUuid);
        $this->assertSame(405, $get->getStatusCode());
        $this->assertNull($this->storedLimit(), 'a GET');

        $this->http->post($url . '&csrf_token=wrong', ['allow_redirects' => false, 'form_params' => ['address_book_uuid' => $this->entryUuid]]);
        $this->assertNull($this->storedLimit(), 'a wrong token');
    }

    /**
     * The screen's other actions change a client from a plain link, so the link's token has to be right.
     */
    #[Test]
    public function theScreensLinksChangeNothingWithoutTheToken(): void
    {
        $url = $this->baseUrl . self::SCREEN . '?action=' . urlencode('edit/' . $this->clientId . '/disable');

        $this->assertSame(403, $this->http->get($url)->getStatusCode(), 'no token');
        $this->assertSame(403, $this->http->get($url . '&csrf_token=wrong')->getStatusCode(), 'a wrong token');
        $this->assertSame(1, $this->storedEnabled());

        $this->http->get($url . '&csrf_token=' . $this->token);
        $this->assertSame(0, $this->storedEnabled(), 'the screen\'s own token');
    }

    /**
     * Posts the screen's form, with no field at all when given null.
     *
     * @param string|list<string>|null $uuid
     */
    private function save(string|array|null $uuid): ResponseInterface
    {
        return $this->http->post(
            $this->baseUrl . self::SCREEN . '?action=' . urlencode('edit/' . $this->clientId . '/address-book') . '&csrf_token=' . $this->token,
            ['allow_redirects' => false, 'form_params' => $uuid === null ? ['other' => '1'] : ['address_book_uuid' => $uuid]]
        );
    }

    /**
     * The client's enabled flag as saved.
     */
    private function storedEnabled(): int
    {
        $enabled = QueryUtils::fetchSingleValue('SELECT is_enabled FROM oauth_clients WHERE client_id = ?', 'is_enabled', [$this->clientId]);
        return is_numeric($enabled) ? (int)$enabled : -1;
    }

    /**
     * The client's edit page, as the signed-in admin sees it.
     */
    private function editPage(): string
    {
        return (string)$this->http->get($this->baseUrl . self::SCREEN . '?action=' . urlencode('edit/' . $this->clientId) . '&csrf_token=' . $this->token)->getBody();
    }

    /**
     * The client's limit as saved, or null with none.
     */
    private function storedLimit(): ?string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT address_book_uuid FROM oauth_clients WHERE client_id = ?', 'address_book_uuid', [$this->clientId]);
        return is_string($uuid) ? UuidRegistry::uuidToString($uuid) : null;
    }
}
