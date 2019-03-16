<?php
/**
 * Http Rest Requests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

/**
 * Class oeHttpRequest
 * @package OpenEMR\Common\Http
 */
class oeHttpRequest extends oeOAuth
{
    public function __construct($client)
    {
        parent::__construct();

        $this->client = $client;
        $this->bodyFormat = "json";
        $this->options = [
            'base_uri' => '',
            'http_errors' => false,
            'verify' => false];

        // set here in class as default
        // otherwise has to be invoked via setDebug.
        if ($this->DEBUG_MODE) {
            $this->usingHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
            if ($this->useProxy) {
                $this->setOptions(['proxy' => 'localhost:' . $this->useProxy]);
            }
        }
    }

    public static function newArgs(...$args)
    {
        return new self(...$args);
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

    public function setApiContext($isExternal = false)
    {
        return $this->tap($this, function ($request) use ($isExternal) {
            $this->apiNative = !$isExternal;
        });
    }

    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    public function withOAuth($credentials = [], $endpoint = '', $grant_type = 'password')
    {
        if (empty($credentials['scope']) && $credentials['grant_type'] == 'password') {
            $credentials['scope'] = $_SESSION['site_id'];
        }

        return $this->tap($this, function ($request) use ($credentials, $endpoint) {
            if ($endpoint) {
                $this->usingAuthEndpoint($endpoint);
            }
            $this->auth_config = array_merge($this->auth_config, $credentials);
            return $this->initOAuthClient();
        });
    }

    public function reAuth()
    {
        $this->apiNative = false;
        $this->apiOAuth = true;
        return $this->tap($this, function ($request) {
            return $this->initOAuthClient();
        });
    }

    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    public function bodyFormat($format)
    {
        return $this->tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    public function contentType($contentType)
    {
        return $this->usingHeaders(['Content-Type' => $contentType]);
    }

    public function accept($header)
    {
        return $this->usingHeaders(['Accept' => $header]);
    }

    public function usingHeaders($headers)
    {
        return $this->tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers
            ]);
        });
    }

    public function setParams($params)
    {
        return $this->tap($this, function ($request) use ($params) {
            return $this->options = array_merge_recursive($this->options, [
                'query' => $params,
            ]);
        });
    }

    public function usingBaseUri($baseUri)
    {
        $baseUri = substr($baseUri, -1) == '/' ? $baseUri : $baseUri . '/';
        return $this->tap($this, function ($request) use ($baseUri) {
            return $this->options = array_merge($this->options, [
                'base_uri' => $baseUri,
            ]);
        });
    }

    public function setOptions($options)
    {
        return $this->tap($this, function ($request) use ($options) {
            return $this->options = array_merge_recursive($this->options, $options);
        });
    }

    public function get($url, $queryParams = [])
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    public function post($url, $params = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function patch($url, $params = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function put($url, $params = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function delete($url, $params = [])
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function send($method, $url, $options = '')
    {
        if ($this->apiNative && !$this->apiOAuth) {
            $this->usingHeaders([
                'appsecret' => session_id(),
                'apptoken' => $_SESSION['csrf_token']
            ]);
            // important so we may retrieve session in api.
            session_write_close();
        }

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

    protected function mergeOptions(...$options)
    {
        return array_merge_recursive($this->options, ...$options);
    }

    protected function parseQueryParams($url)
    {
        return $this->tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    protected function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }
}
