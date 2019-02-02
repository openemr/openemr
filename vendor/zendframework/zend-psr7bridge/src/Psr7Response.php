<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Psr7Bridge;

use Psr\Http\Message\ResponseInterface;
use Zend\Http\Response as ZendResponse;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

final class Psr7Response
{
    /**
     * Convert a PSR-7 response in a Zend\Http\Response
     *
     * @param  ResponseInterface $psr7Response
     * @return ZendResponse
     */
    public static function toZend(ResponseInterface $psr7Response)
    {
        $response = sprintf(
            "HTTP/%s %d %s\r\n%s\r\n%s",
            $psr7Response->getProtocolVersion(),
            $psr7Response->getStatusCode(),
            $psr7Response->getReasonPhrase(),
            self::psr7HeadersToString($psr7Response),
            (string) $psr7Response->getBody()
        );
        return ZendResponse::fromString($response);
    }

    /**
     * Convert a Zend\Http\Response in a PSR-7 response, using zend-diactoros
     *
     * @param  ZendResponse $zendResponse
     * @return Response
     */
    public static function fromZend(ZendResponse $zendResponse)
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($zendResponse->getBody());

        return new Response(
            $body,
            $zendResponse->getStatusCode(),
            $zendResponse->getHeaders()->toArray()
        );
    }

    /**
     * Convert the PSR-7 headers to string
     *
     * @param ResponseInterface $psr7Response
     * @return string
     */
    private static function psr7HeadersToString(ResponseInterface $psr7Response)
    {
        $headers = '';
        foreach ($psr7Response->getHeaders() as $name => $value) {
            $headers .= $name . ": " . implode(", ", $value) . "\r\n";
        }
        return $headers;
    }

    /**
     * Do not allow instantiation.
     */
    private function __construct()
    {
    }
}
