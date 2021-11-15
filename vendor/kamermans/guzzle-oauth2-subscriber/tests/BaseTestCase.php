<?php

namespace kamermans\OAuth2\Tests;

use kamermans\OAuth2\Utils\Helper;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Psr7\Request as Psr7Request;

if (!class_exists('\PHPUnit\Framework\TestCase')) {
    require_once __DIR__.'/PHPUniteNamespaceShim.php';
}

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    protected function createRequest($method, $uri, $options=[])
    {
        return Helper::guzzleIs('>=', 6)?
            new Psr7Request($method, $uri, $options):
            new Request($method, $uri, ['headers' => $options]);
    }

    protected function getHeader($request, $header)
    {
        return Helper::guzzleIs('>=', 6)?
            $request->getHeaderLine($header):
            $request->getHeader($header);
    }

    protected function getQueryStringValue($request, $field)
    {
        if (Helper::guzzleIs('<', 6)) {
            return $request->getQuery()->get($field);
        }

        $query_string = $request->getUri()->getQuery();

        $values = $this->parseQueryString($query_string);

        return array_key_exists($field, $values)? $values[$field]: null;
    }

    protected function getFormPostBodyValue($request, $field)
    {
        if (Helper::guzzleIs('<', 6)) {
            return $request->getBody()->getField($field);
        }

        $query_string = (string)$request->getBody();

        $values = $this->parseQueryString($query_string);

        return array_key_exists($field, $values)? $values[$field]: null;
    }

    protected function parseQueryString($query_string)
    {
        $values = [];
        foreach (explode('&', $query_string) as $component) {
            list($key, $value) = explode('=', $component);
            $values[rawurldecode($key)] = rawurldecode($value);
        }

        return $values;
    }

    protected function setPostBody($request, array $data=[])
    {
        if (Helper::guzzleIs('>=', 6)) {
            return $request->withBody(\GuzzleHttp\Psr7\stream_for(http_build_query($data, '', '&')));
        }

        $request->setBody(new PostBody($data));

        return $request;
    }

}
