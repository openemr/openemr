<?php

/**
 * StatusCode  Way to enumerate common HTTP Status Codes
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

abstract class StatusCode
{
    // 2XX status codes go here
    const OK = 200;
    const ACCEPTED = 202;

    // 3XX status codes go here
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;

    // 4XX status codes go here
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const NOT_FOUND = 404;

    // used to tell the client to stop polling so frequently
    const TOO_MANY_REQUESTS = 429;

    // 5XX status codes go here
    const INTERNAL_SERVER_ERROR = 500;

    const NOT_IMPLEMENTED = 501;
}
