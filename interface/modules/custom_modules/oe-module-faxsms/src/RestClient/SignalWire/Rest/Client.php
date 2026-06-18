<?php

/**
 * Minimal SignalWire REST client shim (Compatibility / LaML Fax API).
 *
 * Self-contained replacement for the legacy `signalwire/signalwire` package,
 * reproducing only the surface the faxsms module consumes:
 *
 *     $client->fax->v1->faxes->create([...])
 *     $client->fax->v1->faxes->read([...], $limit)
 *     $client->fax->v1->faxes->getContext($sid)->fetch()
 *     $client->fax->v1->faxes->getContext($sid)->delete()
 *
 * All HTTP goes through Guzzle (bundled with OpenEMR), so the transport is
 * injectable/mockable in tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Thrown when a SignalWire REST request fails (transport error or non-2xx).
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
 * Internal HTTP transport. Owns credentials, base URL, auth, and JSON decoding.
 *
 * @internal
 */
final readonly class Transport
{
    private string $spaceUrl;
    private ClientInterface $http;

    public function __construct(
        private string $projectId,
        private string $apiToken,
        string $spaceUrl,
        ?ClientInterface $http = null
    ) {
        $this->spaceUrl = self::normalizeSpace($spaceUrl);
        $this->http = $http ?? new GuzzleClient();
    }

    private static function normalizeSpace(string $space): string
    {
        $space = preg_replace('#^https?://#i', '', trim($space)) ?? $space;
        return rtrim($space, '/');
    }

    private function faxesBase(): string
    {
        return 'https://' . $this->spaceUrl
            . '/api/laml/2010-04-01/Accounts/' . rawurlencode($this->projectId) . '/Faxes';
    }

    /**
     * Issue a request against the Faxes resource and decode the JSON body.
     *
     * @param array<string, mixed> $opts
     * @return array<string, mixed>
     * @throws RestException
     */
    public function request(string $method, string $path = '', array $opts = []): array
    {
        return $this->send($method, $this->faxesBase() . $path, $opts, true) ?? [];
    }

    /**
     * Issue a request that returns no body (e.g. DELETE -> 204). True on 2xx.
     *
     * @throws RestException
     */
    public function requestNoContent(string $method, string $path = ''): bool
    {
        $this->send($method, $this->faxesBase() . $path, [], false);
        return true;
    }

    /**
     * @return array<string, mixed>
     * @throws RestException
     */
    public function requestAbsolute(string $method, string $uri): array
    {
        $url = str_starts_with($uri, 'http')
            ? $uri
            : 'https://' . $this->spaceUrl . '/' . ltrim($uri, '/');
        return $this->send($method, $url, [], true) ?? [];
    }

    /**
     * @param array<string, mixed> $opts
     * @return array<string, mixed>|null Decoded body when $expectJson, else null.
     * @throws RestException
     */
    private function send(string $method, string $url, array $opts, bool $expectJson): ?array
    {
        $options = array_merge(
            [
                'auth' => [$this->projectId, $this->apiToken],
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => true,
            ],
            $opts
        );

        try {
            $response = $this->http->request($method, $url, $options);
        } catch (GuzzleException $e) {
            $code = method_exists($e, 'getCode') ? (int) $e->getCode() : 0;
            throw new RestException('SignalWire request failed: ' . $e->getMessage(), $code, $e);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new RestException('SignalWire returned HTTP ' . $status . ': ' . self::snippet($body), $status);
        }

        if (!$expectJson) {
            return null;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RestException(
                'SignalWire returned a non-JSON body (HTTP ' . $status . '): ' . self::snippet($body),
                $status
            );
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
 * A single fax resource. camelCase properties mapped from API snake_case.
 */
final class FaxInstance
{
    public ?string $sid = null;
    public ?string $status = null;
    public ?string $direction = null;
    public ?string $from = null;
    public ?string $to = null;
    public ?int $numPages = null;
    public ?int $duration = null;
    public ?\DateTime $dateCreated = null;
    public ?string $mediaUrl = null;

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        $fax = new self();
        $fax->sid = isset($raw['sid']) ? (string) $raw['sid'] : null;
        $fax->status = isset($raw['status']) ? (string) $raw['status'] : null;
        $fax->direction = isset($raw['direction']) ? (string) $raw['direction'] : null;
        $fax->from = isset($raw['from']) ? (string) $raw['from'] : null;
        $fax->to = isset($raw['to']) ? (string) $raw['to'] : null;

        $pages = $raw['num_pages'] ?? ($raw['pages'] ?? null);
        $fax->numPages = $pages !== null ? (int) $pages : null;

        $fax->duration = isset($raw['duration']) ? (int) $raw['duration'] : null;
        $fax->mediaUrl = isset($raw['media_url']) ? (string) $raw['media_url'] : null;

        $created = $raw['date_created'] ?? null;
        if (!empty($created) && is_string($created)) {
            try {
                $fax->dateCreated = new \DateTime($created);
            } catch (\Throwable) {
                $fax->dateCreated = null;
            }
        }

        return $fax;
    }
}

/**
 * Context for a single fax (selected by SID); supports fetch() and delete().
 */
final readonly class FaxContext
{
    public function __construct(private Transport $transport, private string $sid)
    {
    }

    /**
     * GET .../Faxes/{sid}
     *
     * @throws RestException
     */
    public function fetch(): FaxInstance
    {
        $data = $this->transport->request('GET', '/' . rawurlencode($this->sid));
        return FaxInstance::fromArray($data);
    }

    /**
     * DELETE .../Faxes/{sid} - removes the fax (and its media) from SignalWire.
     *
     * @return bool True on a 2xx response.
     * @throws RestException
     */
    public function delete(): bool
    {
        return $this->transport->requestNoContent('DELETE', '/' . rawurlencode($this->sid));
    }
}

/**
 * The Faxes collection: create / read (list) / getContext.
 */
final readonly class FaxList
{
    public function __construct(private Transport $transport)
    {
    }

    /**
     * POST .../Faxes
     *
     * @param array<string, mixed> $options Accepts: to, from, mediaUrl (+ optional
     *                                       quality, statusCallback, storeMedia).
     * @throws RestException
     */
    public function create(array $options): FaxInstance
    {
        $form = [];
        if (isset($options['to'])) {
            $form['To'] = (string) $options['to'];
        }
        if (isset($options['from'])) {
            $form['From'] = (string) $options['from'];
        }
        if (isset($options['mediaUrl'])) {
            $form['MediaUrl'] = (string) $options['mediaUrl'];
        }
        $optional = [
            'quality' => 'Quality',
            'statusCallback' => 'StatusCallback',
            'storeMedia' => 'StoreMedia',
        ];
        foreach ($optional as $camel => $pascal) {
            if (isset($options[$camel])) {
                $form[$pascal] = $options[$camel];
            }
        }

        $data = $this->transport->request('POST', '', ['form_params' => $form]);
        return FaxInstance::fromArray($data);
    }

    /**
     * GET .../Faxes (auto-paginates up to $limit).
     *
     * @param array<string, mixed> $filters Accepts: from, to, dateCreatedAfter,
     *                                       dateCreatedOnOrBefore, dateCreatedBefore.
     * @return list<FaxInstance>
     * @throws RestException
     */
    public function read(array $filters = [], ?int $limit = null): array
    {
        $map = [
            'from' => 'From',
            'to' => 'To',
            'dateCreatedAfter' => 'DateCreatedAfter',
            'dateCreatedOnOrBefore' => 'DateCreatedOnOrBefore',
            'dateCreatedBefore' => 'DateCreatedBefore',
        ];

        $query = [];
        foreach ($map as $camel => $pascal) {
            if (isset($filters[$camel])) {
                $query[$pascal] = $filters[$camel];
            }
        }

        $pageSize = $limit !== null ? max(1, min($limit, 1000)) : 50;
        $query['PageSize'] = $pageSize;

        $results = [];
        $nextUri = null;

        do {
            $data = $nextUri === null
                ? $this->transport->request('GET', '', ['query' => $query])
                : $this->transport->requestAbsolute('GET', $nextUri);

            $rows = $data['faxes'] ?? [];
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (is_array($row)) {
                        $results[] = FaxInstance::fromArray($row);
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

    /**
     * Select a single fax by SID.
     */
    public function getContext(string $sid): FaxContext
    {
        return new FaxContext($this->transport, $sid);
    }
}

/**
 * Version wrapper: exposes ->faxes (mirrors the legacy ->fax->v1->faxes path).
 */
final class V1Domain
{
    public FaxList $faxes;

    public function __construct(Transport $transport)
    {
        $this->faxes = new FaxList($transport);
    }
}

/**
 * Fax domain wrapper: exposes ->v1.
 */
final class FaxDomain
{
    public V1Domain $v1;

    public function __construct(Transport $transport)
    {
        $this->v1 = new V1Domain($transport);
    }
}

/**
 * Drop-in replacement for SignalWire\Rest\Client (fax surface only).
 */
class Client
{
    /** Exposes the ->fax->v1->faxes chain used by the faxsms controller. */
    public FaxDomain $fax;

    private readonly Transport $transport;

    /**
     * @param array{signalwireSpaceUrl?: string, httpClient?: ClientInterface} $options
     */
    public function __construct(string $projectId, string $apiToken, array $options = [])
    {
        $spaceUrl = $options['signalwireSpaceUrl'] ?? '';

        $http = $options['httpClient'] ?? null;
        if ($http !== null && !$http instanceof ClientInterface) {
            throw new \InvalidArgumentException("'httpClient' must implement GuzzleHttp\\ClientInterface");
        }

        $this->transport = new Transport($projectId, $apiToken, $spaceUrl, $http);
        $this->fax = new FaxDomain($this->transport);
    }
}
