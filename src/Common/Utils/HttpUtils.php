<?php

/**
 * HttpUtils utility class for functions dealing with urls and http.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class HttpUtils
{
    public static function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
