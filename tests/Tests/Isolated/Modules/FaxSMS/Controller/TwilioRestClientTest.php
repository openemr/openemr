<?php

/**
 * Isolated unit tests for the vendored Twilio Messages REST shim.
 *
 * Network-free: a Guzzle MockHandler is injected into the shim's transport, and
 * the history middleware captures outgoing requests so we can assert URL, verb,
 * auth, body, and query construction, plus response mapping and error handling.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\RestClient;

use Composer\Autoload\ClassLoader;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Modules\FaxSMS\RestClient\Twilio\Rest\Client;
use OpenEMR\Modules\FaxSMS\RestClient\Twilio\Rest\RestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class TwilioRestClientTest extends TestCase
{
    private const ACCOUNT_SID = 'AC00000000000000000000000000000001';
    private const AUTH_USER = 'SK00000000000000000000000000000002';
    private const AUTH_PASS = 'secret-token';

    /** @var list<RequestInterface> */
    private array $history = [];

    public static function setUpBeforeClass(): void
    {
        // Make the module's shim autoloadable in the isolated test context.
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if ($loader instanceof ClassLoader) {
            $loader->addPsr4(
                'OpenEMR\\Modules\\FaxSMS\\',
                dirname(__DIR__, 6) . '/interface/modules/custom_modules/oe-module-faxsms/src/',
                true
            );
        }
    }

    /**
     * Build a shim Client whose transport uses a mocked Guzzle handler.
     *
     * @param list<Response> $responses
     */
    private function clientWith(array $responses): Client
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(function (callable $handler): callable {
            return function (RequestInterface $request, array $options) use ($handler): mixed {
                $this->history[] = $request;
                return $handler($request, $options);
            };
        });
        $guzzle = new GuzzleClient(['handler' => $stack]);

        return new Client(self::AUTH_USER, self::AUTH_PASS, self::ACCOUNT_SID, $guzzle);
    }

    private function lastRequest(): RequestInterface
    {
        self::assertNotEmpty($this->history, 'Expected at least one HTTP request to have been recorded.');
        $last = end($this->history);
        self::assertInstanceOf(RequestInterface::class, $last);
        return $last;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function messageJson(array $overrides = []): array
    {
        return array_merge([
            'sid' => 'SM0000000000000000000000000000000a',
            'uri' => '/2010-04-01/Accounts/' . self::ACCOUNT_SID . '/Messages/SM0000000000000000000000000000000a.json',
            'to' => '+15551230000',
            'from' => '+15559990000',
            'body' => 'Hello there',
            'status' => 'delivered',
            'direction' => 'inbound',
            'num_segments' => '1',
            'date_created' => 'Fri, 13 Jun 2025 12:34:56 +0000',
            'date_updated' => 'Fri, 13 Jun 2025 12:35:10 +0000',
            'date_sent' => 'Fri, 13 Jun 2025 12:34:58 +0000',
        ], $overrides);
    }

    public function testCreateSendsExpectedRequestAndMapsResponse(): void
    {
        $client = $this->clientWith([
            new Response(201, [], (string)json_encode($this->messageJson(['status' => 'queued']))),
        ]);

        $message = $client->messages->create('+15551230000', [
            'from' => '+15559990000',
            'body' => 'Hello there',
        ]);

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString(
            '/2010-04-01/Accounts/' . self::ACCOUNT_SID . '/Messages.json',
            (string)$request->getUri()
        );

        parse_str((string)$request->getBody(), $form);
        $this->assertSame('+15551230000', $form['To']);
        $this->assertSame('+15559990000', $form['From']);
        $this->assertSame('Hello there', $form['Body']);

        $this->assertSame('SM0000000000000000000000000000000a', $message->sid);
        $this->assertSame('queued', $message->status);
    }

    public function testBasicAuthHeaderIsSet(): void
    {
        $client = $this->clientWith([
            new Response(201, [], (string)json_encode($this->messageJson())),
        ]);
        $client->messages->create('+15551230000', ['from' => '+15559990000', 'body' => 'x']);

        $expected = 'Basic ' . base64_encode(self::AUTH_USER . ':' . self::AUTH_PASS);
        $this->assertSame($expected, $this->lastRequest()->getHeaderLine('Authorization'));
    }

    public function testReadMapsFieldsAndDates(): void
    {
        $client = $this->clientWith([
            new Response(200, [], (string)json_encode([
                'messages' => [$this->messageJson()],
                'next_page_uri' => null,
            ])),
        ]);

        $messages = $client->messages->read([], 100);
        $this->assertCount(1, $messages);
        $m = $messages[0];

        $this->assertSame('SM0000000000000000000000000000000a', $m->sid);
        $this->assertSame('+15551230000', $m->to);
        $this->assertSame('+15559990000', $m->from);
        $this->assertSame('Hello there', $m->body);
        $this->assertSame('delivered', $m->status);
        $this->assertSame('inbound', $m->direction);
        $this->assertSame(1, $m->numSegments);

        $this->assertInstanceOf(\DateTimeImmutable::class, $m->dateCreated);
        $this->assertInstanceOf(\DateTimeImmutable::class, $m->dateUpdated);
        $this->assertSame('2025-06-13 12:34:56', $m->dateCreated->format('Y-m-d H:i:s'));
        $this->assertSame('2025-06-13 12:35:10', $m->dateUpdated->format('Y-m-d H:i:s'));
    }

    public function testReadSendsDateRangeFilters(): void
    {
        $client = $this->clientWith([
            new Response(200, [], (string)json_encode(['messages' => [], 'next_page_uri' => null])),
        ]);

        $client->messages->read([
            'dateSentAfter' => '2025-06-01T00:00:01Z',
            'dateSentBefore' => '2025-06-30T23:59:59Z',
        ], 100);

        $query = $this->lastRequest()->getUri()->getQuery();
        // '>' and '<' are percent-encoded by the query builder.
        $this->assertStringContainsString('DateSent%3E=', $query);
        $this->assertStringContainsString('DateSent%3C=', $query);
        $this->assertStringContainsString('PageSize=100', $query);
    }

    public function testReadAutoPaginates(): void
    {
        $nextUri = '/2010-04-01/Accounts/' . self::ACCOUNT_SID . '/Messages.json?Page=1&PageToken=abc';
        $client = $this->clientWith([
            new Response(200, [], (string)json_encode([
                'messages' => [$this->messageJson(['sid' => 'SMpage1'])],
                'next_page_uri' => $nextUri,
            ])),
            new Response(200, [], (string)json_encode([
                'messages' => [$this->messageJson(['sid' => 'SMpage2'])],
                'next_page_uri' => null,
            ])),
        ]);

        $messages = $client->messages->read([], 500);
        $this->assertCount(2, $messages);
        $this->assertSame('SMpage1', $messages[0]->sid);
        $this->assertSame('SMpage2', $messages[1]->sid);

        // Second call followed the absolute next_page_uri.
        $this->assertCount(2, $this->history);
        $this->assertArrayHasKey(1, $this->history);
        $secondUrl = (string)$this->history[1]->getUri();
        $this->assertStringContainsString('api.twilio.com', $secondUrl);
        $this->assertStringContainsString('Page=1', $secondUrl);
    }

    public function testReadRespectsLimit(): void
    {
        $client = $this->clientWith([
            new Response(200, [], (string)json_encode([
                'messages' => [
                    $this->messageJson(['sid' => 'SMa']),
                    $this->messageJson(['sid' => 'SMb']),
                    $this->messageJson(['sid' => 'SMc']),
                ],
                'next_page_uri' => null,
            ])),
        ]);

        $messages = $client->messages->read([], 2);
        $this->assertCount(2, $messages);
    }

    public function testHttpErrorThrowsRestException(): void
    {
        $client = $this->clientWith([
            new Response(401, [], (string)json_encode(['code' => 20003, 'message' => 'Authenticate'])),
        ]);

        $this->expectException(RestException::class);
        $client->messages->read([], 10);
    }

    public function testNonJsonBodyThrowsRestException(): void
    {
        $client = $this->clientWith([
            new Response(200, [], '<html>not json</html>'),
        ]);

        $this->expectException(RestException::class);
        $client->messages->read([], 10);
    }

    public function testInvalidOrEmptyDatesBecomeNull(): void
    {
        $client = $this->clientWith([
            new Response(200, [], (string)json_encode([
                'messages' => [$this->messageJson(['date_created' => '', 'date_sent' => 'not-a-date'])],
                'next_page_uri' => null,
            ])),
        ]);

        $m = $client->messages->read([], 10)[0];
        $this->assertNull($m->dateCreated);
        $this->assertNull($m->dateSent);
    }
}
