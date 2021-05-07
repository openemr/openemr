<?php

/**
 * Psr17Factory is a Decorator around an external PSR17 factory.  It allows us to easily swap the library out for another
 * if we need to and insulates OpenEMR from external changes.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use Nyholm\Psr7\Factory\Psr17Factory as NyholmPsr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class Psr17Factory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UploadedFileFactoryInterface, UriFactoryInterface
{
    /**
     * Create a new response.
     *
     * @param int $code The HTTP status code. Defaults to 200.
     * @param string $reasonPhrase The reason phrase to associate with the status code
     *     in the generated response. If none is provided, implementations MAY use
     *     the defaults as suggested in the HTTP specification.
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new NyholmPsr17Factory())->createResponse($code, $reasonPhrase);
    }

    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return (new NyholmPsr17Factory())->createRequest($method, $uri);
    }

    /**
     * Create a new Stream from a string
     * @param string $content
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return (new NyholmPsr17Factory())->createStream($content);
    }

    /**
     * Create a new Stream from a file
     * @param string $filename
     * @param string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return (new NyholmPsr17Factory())->createStreamFromFile($filename, $mode);
    }

    /**
     * Create a stream from a resource
     * @param resource $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return (new NyholmPsr17Factory())->createStreamFromResource($resource);
    }

    /**
     * Create an uploaded file for the given stream
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFileInterface
     */
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        return (new NyholmPsr17Factory())->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
        if (null === $size) {
            $size = $stream->getSize();
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * Create an URI from the uri string
     * @param string $uri
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return (new NyholmPsr17Factory())->createUri($uri);
    }

    /**
     * Create a Server Request object
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new NyholmPsr17Factory())->createServerRequest($method, $uri, $serverParams);
    }
}
