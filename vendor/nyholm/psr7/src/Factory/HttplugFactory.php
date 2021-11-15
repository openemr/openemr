<?php

declare(strict_types=1);

namespace Nyholm\Psr7\Factory;

use Http\Message\{MessageFactory, StreamFactory, UriFactory};
use Nyholm\Psr7\{Request, Response, Stream, Uri};
use Psr\Http\Message\UriInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Martijn van der Ven <martijn@vanderven.se>
 *
 * @final This class should never be extended. See https://github.com/Nyholm/psr7/blob/master/doc/final.md
 */
class HttplugFactory implements MessageFactory, StreamFactory, UriFactory
{
    public function createRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1')
    {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function createResponse($statusCode = 200, $reasonPhrase = null, array $headers = [], $body = null, $version = '1.1')
    {
        return new Response((int) $statusCode, $headers, $body, $version, $reasonPhrase);
    }

    public function createStream($body = null)
    {
        return Stream::create($body ?? '');
    }

    public function createUri($uri = ''): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        return new Uri($uri);
    }
}
