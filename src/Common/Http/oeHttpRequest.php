<?php

/**
 * Http Rest Requests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use GuzzleHttp\ClientInterface;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Class oeHttpRequest
 *
 * @package OpenEMR\Common\Http
 */
class oeHttpRequest extends oeHttp
{
    private string $bodyFormat;
    private array $options;

    public function __construct(readonly private ClientInterface $client)
    {
        parent::__construct();

        $this->bodyFormat = "json";
        $httpVerifySsl = (bool) (OEGlobalsBag::getInstance()->get('http_verify_ssl') ?? true);
        $this->options = [
            'base_uri' => '',
            'http_errors' => false,
            'verify' => $httpVerifySsl,
        ];

        /* set here in class as default
        *  otherwise has to be invoked via setDebug().
        */
        if ($this->DEBUG_MODE) {
            $this->usingHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
            if ($this->useProxy) {
                $this->setOptions(['proxy' => 'localhost:' . '8888']);
            }
        }
    }

    public function usingHeaders($headers)
    {
        return $this->tap($this, fn($request): array => $this->options = array_merge_recursive($this->options, [
            'headers' => $headers
        ]));
    }

    protected function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }

    public function setOptions($options)
    {
        return $this->tap($this, fn($request): array => $this->options = array_merge_recursive($this->options, $options));
    }

    /**
     * @deprecated - use the constructor
     */
    public static function newArgs(ClientInterface $client): oeHttpRequest
    {
        return new self($client);
    }

    public function setDebug($port = '')
    {
        if ($port) {
            $this->setOptions(['proxy' => 'localhost:' . $port]);
            $this->useProxy = true;
        }
        return $this->tap($this, function ($request) {
            $this->DEBUG_MODE = true;
            return $this->usingHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
        });
    }

    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    public function bodyFormat($format)
    {
        return $this->tap($this, function ($request) use ($format): void {
            $this->bodyFormat = $format;
        });
    }

    /* Currently supporting authorization_code grant. Resource grant(password+) will come soon.*/
    public function withOAuth($credentials = [], $endpoints = [], $grant_type = 'authorization_code')
    {
        return $this->tap($this, function ($request) use ($credentials, $endpoints): void {
            $this->setAuthBase($endpoints['token_uri']); // required
            $this->setRedirect($endpoints['redirect_uri']); // required
            $this->setAuthOptions([
                'authorize_uri' => $endpoints['authorize_uri'] ?? null,
                'discovery_url' => $endpoints['discovery_url'] ?? null,
            ]);
            $this->token_config = array_merge($this->token_config, $credentials);
            $this->initOAuthClient();
        });
    }

    /* All this does is init auth to check if needs a refresh.*/
    public function reAuth()
    {
        $this->apiOAuth = true;
        return $this->tap($this, function ($request): void {
            $this->initOAuthClient();
        });
    }

    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    public function contentType(string $contentType)
    {
        return $this->usingHeaders(['Content-Type' => $contentType]);
    }

    public function accept(string $header)
    {
        return $this->usingHeaders(['Accept' => $header]);
    }

    public function setParams($params)
    {
        return $this->tap($this, fn($request): array => $this->options = array_merge_recursive($this->options, [
            'query' => $params,
        ]));
    }

    public function usingBaseUri(string $baseUri)
    {
        $baseUri = str_ends_with($baseUri, '/') ? $baseUri : $baseUri . '/';
        return $this->tap($this, fn($request): array => $this->options = array_merge($this->options, [
            'base_uri' => $baseUri,
        ]));
    }

    public function get(string $url, $queryParams = []): oeHttpResponse
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    public function send(string $method, string $url, $options = ''): oeHttpResponse
    {
        if ($this->apiOAuth) {
            $this->setOptions([
                'handler' => $this->stack,
                'auth' => 'oauth'
            ]);
        }

        return new oeHttpResponse($this->client->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url),
        ], $options)));
    }

    protected function mergeOptions(...$options): array
    {
        return array_merge_recursive($this->options, ...$options);
    }

    protected function parseQueryParams(string $url)
    {
        return $this->tap([], function (&$query) use ($url): void {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    /**
     * The getCurlOptions() function was added in PR#3172 to be able to pass a specific cipher to curl
     * in order to handle an issue in 5.0.2 (1) with OpenSSL 1.1.1c and 1.1.1d where attempting to import
     * the pharmacies from https://npiregistry.cms.hhs.gov/api/ results in the error:
     *   PHP Fatal error: Uncaught GuzzleHttp\Exception\ConnectException: cURL error 35:
     *   error:141A318A:SSL routines:tls_process_ske_dhe:dh key too small
     *   (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
     * The latest versions of OpenSSL have deprecated the use of the 512-bit Diffie–Hellman key that is
     * apparently still used by the CMS server.  Once CMS updates their encryption it may be possible to
     * remove this additional function.
     */
    public function getCurlOptions(string $url, $queryParams = [], $curlOptions = []): oeHttpResponse
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
            'curl' => $curlOptions
        ]);
    }

    public function post(string $url, $params = []): oeHttpResponse
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function patch(string $url, $params = []): oeHttpResponse
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function put(string $url, $params = []): oeHttpResponse
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function delete(string $url, $params = []): oeHttpResponse
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }
}
