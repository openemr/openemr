<?php

/**
 * Talks to the Grapheus service with the clinician's (or practice's) own key.
 * Everything the browser does goes through here, so the key never reaches the
 * browser.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Exetazo\Grapheus;

use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\BC\ServiceContainer;

final readonly class Client
{
    private string $server;

    public function __construct(string $server, private string $key)
    {
        $this->server = rtrim($server, '/');
    }

    /**
     * @param array<string, mixed>|string|null $body JSON body (array), raw body (string with $contentType), or none
     * @return array{status: int, body: array<string, mixed>}
     */
    public function call(string $method, string $path, array|string|null $body = null, ?string $contentType = null, int $timeout = 60): array
    {
        $options = [
            'headers' => ['Authorization' => 'Bearer ' . $this->key, 'Accept' => 'application/json'],
            'timeout' => $timeout,
            'connect_timeout' => 10,
            'http_errors' => false,
            'allow_redirects' => false,   // the service never redirects; never follow one (could downgrade to http)
        ];
        if (is_array($body)) {
            $options['json'] = $body === [] ? new \stdClass() : $body;
        } elseif (is_string($body)) {
            $options['body'] = $body;
            $options['headers']['Content-Type'] = $contentType ?? 'application/octet-stream';
        }
        try {
            $res = (new Http())->request($method, $this->server . $path, $options);
        } catch (GuzzleException $e) {
            ServiceContainer::getLogger()->error('Grapheus request failed', ['path' => $path, 'error' => $e->getMessage()]);
            return ['status' => 0, 'body' => ['ok' => false, 'error' => 'Could not reach Grapheus. Try again in a moment.']];
        }
        $status = $res->getStatusCode();
        $json = json_decode((string) $res->getBody(), true);
        return ['status' => $status, 'body' => is_array($json) ? Val::map($json) : ['ok' => false, 'error' => 'Unexpected answer from Grapheus (' . $status . ').']];
    }
}
