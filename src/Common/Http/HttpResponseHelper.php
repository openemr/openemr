<?php

/**
 * HttpResponseHelper
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Common\Http;

class HttpResponseHelper
{
    public static function send($statusCode, $payload, $serializationStrategy)
    {
        $response = null;

        switch ($serializationStrategy) {
            case 'JSON':
                $messageObject = null;
                if (is_string($payload)) {
                    $messageObject = new \stdClass();
                    $messageObject->message = $payload;
                }

                header("Content-Type: application/json; charset=utf-8");
                if (!empty($messageObject)) {
                    $response = json_encode($messageObject);
                } else {
                    $response = json_encode($payload);
                }
                break;
        }

        header("Expires: on, 01 Jan 1970 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        http_response_code($statusCode);

        echo $response;
    }
}
