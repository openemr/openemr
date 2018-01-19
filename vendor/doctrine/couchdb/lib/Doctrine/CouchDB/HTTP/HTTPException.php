<?php

namespace Doctrine\CouchDB\HTTP;

/**
 * Base exception class for package Doctrine\ODM\CouchDB\HTTP
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Kore Nordmann <kore@arbitracker.org>
 */
class HTTPException extends \Doctrine\CouchDB\CouchDBException
{
    /**
     * @param string $ip
     * @param integer $port
     * @param string $errstr
     * @param integer $errno
     * @return \Doctrine\CouchDB\HTTP\HTTPException
     */
    public static function connectionFailure( $ip, $port, $errstr, $errno )
    {
        return new self( sprintf(
            "Could not connect to server at %s:%d: '%d: %s'",
            $ip,
            $port,
            $errno,
            $errstr
        ), $errno );
    }

    /**
     * @param string $ip
     * @param integer $port
     * @param string $errstr
     * @param integer $errno
     * @return \Doctrine\CouchDB\HTTP\HTTPException
     */
    public static function readFailure( $ip, $port, $errstr, $errno )
    {
        return new static( sprintf(
            "Could read from server at %s:%d: '%d: %s'",
            $ip,
            $port,
            $errno,
            $errstr
        ), $errno );
    }

    /**
     * @param string $path
     * @param Response $response
     * @return \Doctrine\CouchDB\HTTP\HTTPException
     */
    public static function fromResponse( $path, Response $response )
    {
        $response = self::fixCloudantBulkCustomError($response);

        if (!isset($response->body['error'])) {
            $response->body['error'] = '';
        }

        return new self(
            "HTTP Error with status " . $response->status . " occoured while "
                . "requesting " . $path . ". Error: " . $response->body['error']
                . " " . $response->body['reason'],
            $response->status );
    }

    private static function fixCloudantBulkCustomError($response)
    {
        if (isset($response->body[0]['error']) && isset($response->body[0]['reason'])) {
            $response->body['error'] = $response->body[0]['error'];
            $response->body['reason'] = $response->body[0]['reason'];
        }

        return $response;
    }
}
