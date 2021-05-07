<?php

/**
 * Http Rest and OAuth 2 Clients
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use GuzzleHttp\Client;

//use kamermans\OAuth2\GrantType\RefreshToken;

/**
 * Class oeHttp
 * extends oeOAuth
 *
 * @package OpenEMR\Common\Http
 */
class oeHttp extends oeOAuth
{
    public static $client;

    public static function __callStatic($method, $args)
    {
        return oeHttpRequest::newArgs(static::client())->{$method}(...$args);
    }

    public static function client()
    {
        return static::$client ?: static::$client = new Client();
    }
}
