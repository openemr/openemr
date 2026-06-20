<?php

declare(strict_types=1);

/**
 * Minimal Twilio REST client shim (Messages / SMS only).
 *
 * Self-contained replacement for the `twilio/sdk` package, reproducing only the
 * surface the faxsms SMS controller uses:
 *
 *     $client = new Client($username, $password, $accountSid);
 *     $client->messages->create($to, ['from' => ..., 'body' => ...]);
 *     $client->messages->read(['dateSentAfter' => ..., 'dateSentBefore' => ...], $limit);
 *
 * Twilio's Messages API is plain Basic-auth REST (the same family as the
 * SignalWire Compatibility shim), so this rides on the Guzzle client already in
 * core and adds no new dependency. Twilio Programmable Fax was sunset in 2021,
 * so only the SMS surface is reproduced.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\RestClient\Twilio\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Thrown when a Twilio REST request fails (transport error or non-2xx).
 */
class RestException extends \Exception
{
    public function __construct(string $message, private readonly int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

/**
 * Internal HTTP transport. Owns credentials (HTTP Basic), the Messages base URL,
 * and JSON decoding.
 *
 * @internal
 */
final readonly class Transport
{
    private string $accountSid;
    private ClientInterface $http;

    public function __construct(
        private string $username,
        private string $password,
        ?string $accountSid = null,
        ?ClientInterface $http = null
    ) {
        // Twilio defaults the account SID to the auth username when not given.
        $this->accountSid = ($accountSid !== null && $accountSid !== '') ? $accountSid : $this->username;
        $this->http = $http ?? new GuzzleClient();
    }

    private function messagesBase(): string
    {
        return 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($this->accountSid) . '/Messages.json';
    }

    /**
     * @param array<string, mixed> $opts
     * @return array<mixed, mixed>
     * @throws RestException
     */
    public function request(string $method, array $opts = []): array
    {
        return $this->send($method, $this->messagesBase(), $opts);
    }

    /**
     * Follow a Twilio next_page_uri (relative to api.twilio.com).
     *
     * @return array<mixed, mixed>
     * @throws RestException
     */
    public function requestAbsolute(string $method, string $uri): array
    {
        $url = str_starts_with($uri, 'http')
            ? $uri
            : 'https://api.twilio.com/' . ltrim($uri, '/');
        return $this->send($method, $url, []);
    }

    /**
     * @param array<string, mixed> $opts
     * @return array<mixed, mixed>
     * @throws RestException
     */
    private function send(string $method, string $url, array $opts): array
    {
        $options = array_merge(
            [
                'auth' => [$this->username, $this->password],
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => true,
            ],
            $opts
        );

        try {
            $response = $this->http->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new RestException('Twilio request failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        $status = $response->getStatusCode();
        $body = (string)$response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new RestException('Twilio returned HTTP ' . $status . ': ' . self::snippet($body), $status);
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RestException('Twilio returned a non-JSON body (HTTP ' . $status . '): ' . self::snippet($body), $status);
        }

        return $decoded;
    }

    private static function snippet(string $body): string
    {
        $body = trim($body);
        return strlen($body) > 300 ? substr($body, 0, 300) . '…' : $body;
    }
}

/**
 * A single Message resource. camelCase properties mapped from API snake_case;
 * date fields are \DateTime to match the SDK the controller was written against.
 */
final class MessageInstance
{
    public ?string $sid = null;
    public ?string $uri = null;
    public ?string $to = null;
    public ?string $from = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $direction = null;
    public ?string $errorCode = null;
    public ?string $errorMessage = null;
    public ?int $numSegments = null;
    public ?\DateTimeImmutable $dateCreated = null;
    public ?\DateTimeImmutable $dateUpdated = null;
    public ?\DateTimeImmutable $dateSent = null;

    /**
     * @param array<mixed, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        $m = new self();
        $m->sid = self::str($raw['sid'] ?? null);
        $m->uri = self::str($raw['uri'] ?? null);
        $m->to = self::str($raw['to'] ?? null);
        $m->from = self::str($raw['from'] ?? null);
        $m->body = self::str($raw['body'] ?? null);
        $m->status = self::str($raw['status'] ?? null);
        $m->direction = self::str($raw['direction'] ?? null);
        $m->errorCode = self::str($raw['error_code'] ?? null);
        $m->errorMessage = self::str($raw['error_message'] ?? null);
        $m->numSegments = self::intOrNull($raw['num_segments'] ?? null);
        $m->dateCreated = self::toDate($raw['date_created'] ?? null);
        $m->dateUpdated = self::toDate($raw['date_updated'] ?? null);
        $m->dateSent = self::toDate($raw['date_sent'] ?? null);
        return $m;
    }

    private static function str(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_scalar($value) ? (int) $value : null;
    }

    private static function toDate(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        // Twilio returns RFC 2822 dates (e.g. "Mon, 01 Jan 2024 12:00:00 +0000").
        // date_create() returns false on a bad string instead of throwing, which
        // avoids catching \Exception (and thus \ErrorException).
        $date = date_create_immutable($value);
        return $date instanceof \DateTimeImmutable ? $date : null;
    }
}

/**
 * The Messages collection: create (send) and read (list).
 */
final readonly class MessageList
{
    public function __construct(private Transport $transport)
    {
    }

    /**
     * POST .../Messages.json
     *
     * @param array<string, mixed> $options Accepts: from, body, messagingServiceSid.
     * @throws RestException
     */
    public function create(string $to, array $options = []): MessageInstance
    {
        $form = ['To' => $to];
        if (isset($options['from']) && is_scalar($options['from'])) {
            $form['From'] = (string) $options['from'];
        }
        if (isset($options['body']) && is_scalar($options['body'])) {
            $form['Body'] = (string) $options['body'];
        }
        if (isset($options['messagingServiceSid']) && is_scalar($options['messagingServiceSid'])) {
            $form['MessagingServiceSid'] = (string) $options['messagingServiceSid'];
        }

        $data = $this->transport->request('POST', ['form_params' => $form]);
        return MessageInstance::fromArray($data);
    }

    /**
     * GET .../Messages.json (auto-paginates up to $limit).
     *
     * @param array<string, mixed> $filters  Accepts: to, from, dateSent,
     *                                       dateSentAfter, dateSentBefore.
     * @return list<MessageInstance>
     * @throws RestException
     */
    public function read(array $filters = [], ?int $limit = null): array
    {
        $query = [];
        if (isset($filters['to'])) {
            $query['To'] = $filters['to'];
        }
        if (isset($filters['from'])) {
            $query['From'] = $filters['from'];
        }
        if (isset($filters['dateSent'])) {
            $query['DateSent'] = $filters['dateSent'];
        }
        // Twilio range filters use inequality-suffixed parameter names.
        if (isset($filters['dateSentAfter'])) {
            $query['DateSent>'] = $filters['dateSentAfter'];
        }
        if (isset($filters['dateSentBefore'])) {
            $query['DateSent<'] = $filters['dateSentBefore'];
        }

        $query['PageSize'] = $limit !== null ? max(1, min($limit, 1000)) : 50;

        $results = [];
        $nextUri = null;

        do {
            $data = $nextUri === null
                ? $this->transport->request('GET', ['query' => $query])
                : $this->transport->requestAbsolute('GET', $nextUri);

            $rows = $data['messages'] ?? [];
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (is_array($row)) {
                        $results[] = MessageInstance::fromArray($row);
                        if ($limit !== null && count($results) >= $limit) {
                            return $results;
                        }
                    }
                }
            }

            $next = $data['next_page_uri'] ?? null;
            $nextUri = (is_string($next) && $next !== '') ? $next : null;
        } while ($nextUri !== null);

        return $results;
    }
}

/**
 * Drop-in replacement for Twilio\Rest\Client (Messages/SMS surface only).
 *
 * Constructor mirrors the SDK: new Client($username, $password, $accountSid).
 * With API-key auth, $username is the API Key SID and $password the secret;
 * with auth-token auth, $username is the Account SID and $password the token.
 */
class Client
{
    /** Mirrors $client->messages used by the SMS controller. */
    public MessageList $messages;

    private readonly Transport $transport;

    public function __construct(
        string $username,
        string $password,
        ?string $accountSid = null,
        ?ClientInterface $httpClient = null
    ) {
        $this->transport = new Transport($username, $password, $accountSid, $httpClient);
        $this->messages = new MessageList($this->transport);
    }
}
