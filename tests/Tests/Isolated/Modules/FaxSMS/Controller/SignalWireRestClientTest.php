<?php

/**
 * Isolated test for the bundled SignalWire REST client shim.
 *
 * The shim (OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\Client) replaces
 * the legacy `signalwire/signalwire` Composer package and reproduces only the
 * Compatibility/LaML Fax surface that SignalWireClient consumes:
 *
 *     $client->fax->v1->faxes->create([...])
 *     $client->fax->v1->faxes->read([...], $limit)
 *     $client->fax->v1->faxes->getContext($sid)->fetch()
 *
 * All HTTP goes through an injectable Guzzle client, so these tests drive the
 * shim entirely through a MockHandler — no network, no database. A history
 * middleware captures the outgoing requests so we can assert the endpoint,
 * Basic auth, and the camelCase -> PascalCase parameter mapping, in addition
 * to the snake_case -> camelCase response mapping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    SignalWire Integration
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\RestClient;

use Composer\Autoload\ClassLoader;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\Client;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\FaxInstance;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\RestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * The custom_modules/oe-module-faxsms module is not registered in the root
 * composer.json autoload map. At runtime the module manager registers its
 * PSR-4 prefix when the module is enabled. The isolated test suite has no
 * database, so we register the prefix ourselves before referencing any
 * module class. Loading the Client class pulls in the whole shim file (all
 * its collaborator classes live alongside it), so a single PSR-4 entry is
 * sufficient.
 */
final class SignalWireRestClientTest extends TestCase
{
    private const PROJECT = 'a1b2c3d4-0000-0000-0000-projectsid01';
    private const TOKEN   = 'PT_secret_token_value';
    private const SPACE   = 'example.signalwire.com';

    private const EXPECTED_PATH = '/api/laml/2010-04-01/Accounts/' . self::PROJECT . '/Faxes';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4(
            'OpenEMR\\Modules\\FaxSMS\\',
            dirname(__DIR__, 6) . '/interface/modules/custom_modules/oe-module-faxsms/src/'
        );
    }

    public function testCreatePostsCompatFaxAndMapsResponse(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(201, ['Content-Type' => 'application/json'], (string) json_encode([
                    'sid'          => 'FX0000000000000000000000000000aa',
                    'status'       => 'queued',
                    'direction'    => 'outbound',
                    'from'         => '+15557654321',
                    'to'           => '+15551234567',
                    'num_pages'    => '0',
                    'duration'     => '0',
                    'date_created' => '2026-06-16T13:45:07Z',
                    'media_url'    => 'https://files.signalwire.com/media/FX0000.pdf',
                ])),
            ],
            $history
        );

        $fax = $client->fax->v1->faxes->create([
            'to'       => '+15551234567',
            'from'     => '+15557654321',
            'mediaUrl' => 'https://example.org/sites/default/fax/outbound.pdf',
        ]);

        // Response mapping: snake_case -> camelCase, with numeric coercion.
        self::assertInstanceOf(FaxInstance::class, $fax);
        self::assertSame('FX0000000000000000000000000000aa', $fax->sid);
        self::assertSame('queued', $fax->status);
        self::assertSame('outbound', $fax->direction);
        self::assertSame('+15557654321', $fax->from);
        self::assertSame('+15551234567', $fax->to);
        self::assertSame(0, $fax->numPages);
        self::assertSame(0, $fax->duration);
        self::assertSame('https://files.signalwire.com/media/FX0000.pdf', $fax->mediaUrl);
        self::assertInstanceOf(\DateTimeImmutable::class, $fax->dateCreated);
        self::assertSame('2026-06-16 13:45:07', $fax->dateCreated->format('Y-m-d H:i:s'));

        // Outgoing request: method, endpoint, host, Basic auth, form mapping.
        $request = $this->lastRequest($history);
        self::assertSame('POST', $request->getMethod());
        self::assertSame(self::SPACE, $request->getUri()->getHost());
        self::assertSame(self::EXPECTED_PATH, $request->getUri()->getPath());
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame($this->expectedBasicAuthHeader(), $request->getHeaderLine('Authorization'));

        parse_str((string) $request->getBody(), $form);
        self::assertSame('+15551234567', $form['To'] ?? null);
        self::assertSame('+15557654321', $form['From'] ?? null);
        self::assertSame('https://example.org/sites/default/fax/outbound.pdf', $form['MediaUrl'] ?? null);
    }

    public function testReadAppliesDateFiltersAndPageSizeAndMapsRows(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(200, [], (string) json_encode([
                    'faxes' => [
                        [
                            'sid'       => 'FXinbound1',
                            'status'    => 'received',
                            'direction' => 'inbound',
                            'from'      => '+15550001111',
                            'to'        => '+15557654321',
                            'num_pages' => 2,
                            'media_url' => 'https://files.signalwire.com/media/FXinbound1.pdf',
                        ],
                        [
                            'sid'       => 'FXinbound2',
                            'status'    => 'received',
                            'direction' => 'inbound',
                            'from'      => '+15550002222',
                            'to'        => '+15557654321',
                            'num_pages' => 1,
                            'media_url' => 'https://files.signalwire.com/media/FXinbound2.pdf',
                        ],
                    ],
                    'next_page_uri' => null,
                ])),
            ],
            $history
        );

        $faxes = $client->fax->v1->faxes->read(
            [
                'dateCreatedAfter'      => '2026-06-01T00:00:01+00:00',
                'dateCreatedOnOrBefore' => '2026-06-16T23:59:59+00:00',
            ],
            100
        );

        self::assertCount(2, $faxes);
        self::assertContainsOnlyInstancesOf(FaxInstance::class, $faxes);
        self::assertSame('FXinbound1', $faxes[0]->sid);
        self::assertSame(2, $faxes[0]->numPages);
        self::assertSame('FXinbound2', $faxes[1]->sid);
        self::assertSame(1, $faxes[1]->numPages);

        $request = $this->lastRequest($history);
        self::assertSame('GET', $request->getMethod());
        self::assertSame(self::EXPECTED_PATH, $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame('2026-06-01T00:00:01+00:00', $query['DateCreatedAfter'] ?? null);
        self::assertSame('2026-06-16T23:59:59+00:00', $query['DateCreatedOnOrBefore'] ?? null);
        self::assertSame('100', $query['PageSize'] ?? null);
    }

    public function testReadFollowsPaginationUpToExhaustion(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(200, [], (string) json_encode([
                    'faxes' => [
                        ['sid' => 'FX1', 'direction' => 'inbound', 'status' => 'received'],
                        ['sid' => 'FX2', 'direction' => 'inbound', 'status' => 'received'],
                    ],
                    'next_page_uri' => '/api/laml/2010-04-01/Accounts/' . self::PROJECT . '/Faxes?Page=1&PageToken=abc',
                ])),
                new Response(200, [], (string) json_encode([
                    'faxes' => [
                        ['sid' => 'FX3', 'direction' => 'inbound', 'status' => 'received'],
                    ],
                    'next_page_uri' => null,
                ])),
            ],
            $history
        );

        $faxes = $client->fax->v1->faxes->read([], 100);

        self::assertCount(3, $faxes);
        self::assertSame(['FX1', 'FX2', 'FX3'], array_map(static fn(FaxInstance $f): ?string => $f->sid, $faxes));

        // Two HTTP round-trips: the base collection, then the next_page_uri.
        self::assertCount(2, $history);
        self::assertSame(self::EXPECTED_PATH, $history[1]['request']->getUri()->getPath());
        parse_str($history[1]['request']->getUri()->getQuery(), $secondQuery);
        self::assertSame('abc', $secondQuery['PageToken'] ?? null);
    }

    public function testReadStopsAtLimitWithoutOverFetching(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(200, [], (string) json_encode([
                    'faxes' => [
                        ['sid' => 'FX1', 'direction' => 'inbound', 'status' => 'received'],
                        ['sid' => 'FX2', 'direction' => 'inbound', 'status' => 'received'],
                        ['sid' => 'FX3', 'direction' => 'inbound', 'status' => 'received'],
                    ],
                    'next_page_uri' => '/api/laml/2010-04-01/Accounts/' . self::PROJECT . '/Faxes?Page=1',
                ])),
            ],
            $history
        );

        $faxes = $client->fax->v1->faxes->read([], 2);

        self::assertCount(2, $faxes);
        // Limit reached on the first page; the next_page_uri must not be fetched.
        self::assertCount(1, $history);
    }

    public function testGetContextFetchRetrievesSingleFax(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(200, [], (string) json_encode([
                    'sid'       => 'FXfetchme',
                    'status'    => 'delivered',
                    'direction' => 'outbound',
                    'num_pages' => 3,
                    'duration'  => 42,
                    'media_url' => 'https://files.signalwire.com/media/FXfetchme.pdf',
                ])),
            ],
            $history
        );

        $fax = $client->fax->v1->faxes->getContext('FXfetchme')->fetch();

        self::assertSame('FXfetchme', $fax->sid);
        self::assertSame('delivered', $fax->status);
        self::assertSame(3, $fax->numPages);
        self::assertSame(42, $fax->duration);

        $request = $this->lastRequest($history);
        self::assertSame('GET', $request->getMethod());
        self::assertSame(self::EXPECTED_PATH . '/FXfetchme', $request->getUri()->getPath());
    }

    public function testMissingOptionalFieldsMapToNullAndPagesFallback(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                // No date_created; 'pages' instead of 'num_pages'; no duration/media_url.
                new Response(200, [], (string) json_encode([
                    'sid'       => 'FXsparse',
                    'status'    => 'queued',
                    'direction' => 'outbound',
                    'pages'     => 5,
                ])),
            ],
            $history
        );

        $fax = $client->fax->v1->faxes->create(['to' => '+1', 'from' => '+1', 'mediaUrl' => 'https://x/y.pdf']);

        self::assertSame(5, $fax->numPages, "'pages' should be honored when 'num_pages' is absent");
        self::assertNull($fax->dateCreated);
        self::assertNull($fax->duration);
        self::assertNull($fax->mediaUrl);
    }

    public function testHttpErrorIsWrappedInRestException(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(400, [], (string) json_encode([
                    'code'    => 21212,
                    'message' => 'Invalid To phone number.',
                ])),
            ],
            $history
        );

        try {
            $client->fax->v1->faxes->create(['to' => 'not-a-number', 'from' => '+1', 'mediaUrl' => 'https://x/y.pdf']);
            self::fail('Expected RestException for an HTTP 400 response.');
        } catch (RestException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertStringContainsString('request failed', $e->getMessage());
        }
    }

    public function testNonJsonBodyIsWrappedInRestException(): void
    {
        $history = [];
        $client = $this->makeClient(
            [
                new Response(200, ['Content-Type' => 'text/html'], '<html>not json</html>'),
            ],
            $history
        );

        $this->expectException(RestException::class);
        $client->fax->v1->faxes->getContext('FXanything')->fetch();
    }

    public function testSpaceUrlWithSchemeAndTrailingSlashIsNormalized(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, [], (string) json_encode(['sid' => 'FXz', 'status' => 'queued'])),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $guzzle = new GuzzleClient(['handler' => $stack]);

        $client = new Client(self::PROJECT, self::TOKEN, [
            'signalwireSpaceUrl' => 'https://' . self::SPACE . '/',
            'httpClient'         => $guzzle,
        ]);

        $client->fax->v1->faxes->getContext('FXz')->fetch();

        $uri = $this->lastRequest($history)->getUri();
        self::assertSame('https', $uri->getScheme());
        self::assertSame(self::SPACE, $uri->getHost());
        self::assertSame(self::EXPECTED_PATH . '/FXz', $uri->getPath());
    }

    public function testNonGuzzleHttpClientOptionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Client(self::PROJECT, self::TOKEN, [
            'signalwireSpaceUrl' => self::SPACE,
            'httpClient'         => new \stdClass(),
        ]);
    }

    /**
     * Build a Client whose transport is a MockHandler-backed Guzzle client,
     * recording outgoing requests into $history.
     *
     * @param list<Response>                                            $responses
     * @param array<int, array{request: RequestInterface, response: mixed}> $history
     */
    private function makeClient(array $responses, array &$history): Client
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $guzzle = new GuzzleClient(['handler' => $stack]);

        return new Client(self::PROJECT, self::TOKEN, [
            'signalwireSpaceUrl' => self::SPACE,
            'httpClient'         => $guzzle,
        ]);
    }

    /**
     * @param array<int, array{request: RequestInterface, response: mixed}> $history
     */
    private function lastRequest(array $history): RequestInterface
    {
        self::assertNotEmpty($history, 'Expected at least one HTTP request to have been made.');
        return $history[array_key_last($history)]['request'];
    }

    private function expectedBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode(self::PROJECT . ':' . self::TOKEN);
    }
}
